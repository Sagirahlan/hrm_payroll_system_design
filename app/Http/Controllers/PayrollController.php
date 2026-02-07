<?php

namespace App\Http\Controllers;

use App\Models\PayrollRecord;
use App\Models\Employee;
use App\Models\PaymentTransaction;
use App\Models\Deduction;
use App\Models\Addition;
use App\Services\PayrollCalculationService;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollRecordsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\GradeLevel;
use App\Models\DeductionType;
use App\Models\AdditionType;
use App\Models\AppointmentType;
use App\Models\Department;
use App\Models\AuditTrail;

class PayrollController extends Controller
{
    protected $payrollCalculationService;

    public function __construct(PayrollCalculationService $payrollCalculationService)
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_payroll'], ['only' => ['index', 'show', 'getStatistics', 'search', 'getDetailedPayroll', 'generatePaySlip', 'exportPayroll', 'showDeductions', 'showAdditions', 'manageAllAdjustments']]);
        $this->middleware(['permission:create_payroll'], ['only' => ['generatePayroll', 'storeDeduction', 'storeAddition', 'storeBulkDeductions', 'storeBulkAdditions', 'bulkDeductions', 'additions', 'deductions', 'manageAllAdjustments', 'importEmployees']]);
        $this->middleware(['permission:edit_payroll'], ['only' => ['edit', 'update', 'recalculate', 'approve', 'reject', 'bulkUpdateStatus', 'bulkSendForReview', 'bulkMarkAsReviewed', 'bulkSendForApproval', 'bulkFinalApprove', 'sendForReview', 'markAsReviewed', 'sendForApproval', 'finalApprove']]);
        $this->middleware(['permission:delete_payroll'], ['only' => ['destroy']]);
        $this->middleware(['permission:generate_payroll'], ['only' => ['generatePayroll', 'recalculate']]);
        $this->middleware(['permission:approve_payroll'], ['only' => ['approve', 'reject', 'bulkSendForReview', 'bulkMarkAsReviewed', 'bulkSendForApproval', 'bulkFinalApprove', 'sendForReview', 'markAsReviewed', 'sendForApproval', 'finalApprove']]);
        $this->middleware(['permission:view_payslips'], ['only' => ['generatePaySlip']]);
        $this->middleware(['permission:manage_payroll_adjustments'], ['only' => ['showDeductions', 'showAdditions', 'storeDeduction', 'storeAddition', 'manageAllAdjustments', 'storeBulkDeductions', 'storeBulkAdditions', 'additions', 'deductions', 'bulkDeductions']]);
        $this->middleware(['permission:bulk_send_payroll_for_review'], ['only' => ['bulkSendForReview', 'sendForReview']]);
        $this->middleware(['permission:bulk_mark_payroll_as_reviewed'], ['only' => ['bulkMarkAsReviewed', 'markAsReviewed']]);
        $this->middleware(['permission:bulk_send_payroll_for_approval'], ['only' => ['bulkSendForApproval', 'sendForApproval']]);
        $this->middleware(['permission:bulk_final_approve_payroll'], ['only' => ['bulkFinalApprove', 'finalApprove']]);
        $this->middleware(['permission:bulk_update_payroll_status'], ['only' => ['bulkUpdateStatus']]);
        $this->payrollCalculationService = $payrollCalculationService;
    }

    // Generate payroll for a specific month
    public function generatePayroll(Request $request)
    {
        set_time_limit(0); // Set no time limit for this script
        $month = $request->input('month', now()->format('Y-m'));
        $category = $request->input('payroll_category', 'staff');
        $appointmentTypeId = $request->input('appointment_type_id');

        if ($category === 'pensioners') {
            // --- Pensioner Payroll Generation ---
            // Exclude Deceased pensioners and those retired by 'Death in Service'
            // Also exclude 'Not Eligible'
            $pensioners = \App\Models\Pensioner::where('status', '!=', 'Deceased')
                ->where('retirement_reason', '!=', 'Death in Service')
                ->where('status', '!=', 'Not Eligible') 
                ->where('status', 'Active') // Explicitly ensure they are Active (double check)
                ->get();
            $count = 0;

            foreach ($pensioners as $pensioner) {
                // Ensure we have an employee record link if needed, or create payroll record directly
                // Assuming PayrollRecord relies on employee_id.
                // Checks if the pensioner has a valid employee_id
                if (!$pensioner->employee_id) continue;

                $monthlyPension = $pensioner->pension_amount;
                $remarks = 'Pension for ' . $month;

                // --- Pro-rata Logic for Transition Month ---
                $startOfMonth = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
                $endOfMonth = \Carbon\Carbon::parse($month . '-01')->endOfMonth();
                
                if ($pensioner->date_of_retirement) {
                    $retirementDate = \Carbon\Carbon::parse($pensioner->date_of_retirement);
                    
                    if ($retirementDate->between($startOfMonth, $endOfMonth)) {
                        // This implies the pensioner retired THIS month.
                        // They were active staff part of the month, and pensioner the rest.
                        // Active Payroll pays 1st -> Retirement Date (Salary).
                        // Pension Payroll pays (Retirement Date + 1) -> End of Month (Pension).
                        
                        $daysInMonth = $startOfMonth->daysInMonth;
                        
                        // Active Days (Paid as Salary) = Retirement Date Day (e.g., 18th = 18 days)
                        $activeDays = $retirementDate->day;
                        
                        // Pension Days = Remaining Days
                        $pensionDays = $daysInMonth - $activeDays;
                        
                        // If pension days > 0, calculate pro-rata
                        if ($pensionDays > 0) {
                            $dailyPensionRate = $monthlyPension / $daysInMonth;
                            $proratedPension = round($dailyPensionRate * $pensionDays, 2);
                            
                            $monthlyPension = $proratedPension;
                            $remarks = 'Pro-rata Pension (Retired ' . $retirementDate->format('d M') . ')';
                        }
                    }
                }
                // -------------------------------------------

                // Calculate additions for this pensioner
                $totalAdditions = 0;
                $additions = \App\Models\Addition::where('employee_id', $pensioner->employee_id)
                    ->where(function($query) use ($month) {
                        $query->where(function($q) use ($month) {
                            // Monthly or Perpetual additions that are active in this month
                            $q->whereIn('addition_period', ['Monthly', 'Perpetual'])
                              ->where('start_date', '<=', $month . '-01')
                              ->where(function($dateQuery) use ($month) {
                                  $dateQuery->whereNull('end_date')
                                           ->orWhere('end_date', '>=', $month . '-01');
                              });
                        })->orWhere(function($q) use ($month) {
                            // OneTime additions for this specific month
                            $q->where('addition_period', 'OneTime')
                              ->whereYear('start_date', '=', explode('-', $month)[0])
                              ->whereMonth('start_date', '=', explode('-', $month)[1]);
                        });
                    })
                    ->get();
                
                foreach ($additions as $addition) {
                    $totalAdditions += $addition->amount;
                }
                
                // Calculate deductions for this pensioner
                $totalDeductions = 0;
                $deductions = \App\Models\Deduction::where('employee_id', $pensioner->employee_id)
                    ->where(function($query) use ($month) {
                        $query->where(function($q) use ($month) {
                            // Monthly or Perpetual deductions that are active in this month
                            $q->whereIn('deduction_period', ['Monthly', 'Perpetual'])
                              ->where('start_date', '<=', $month . '-01')
                              ->where(function($dateQuery) use ($month) {
                                  $dateQuery->whereNull('end_date')
                                           ->orWhere('end_date', '>=', $month . '-01');
                              });
                        })->orWhere(function($q) use ($month) {
                            // OneTime deductions for this specific month
                            $q->where('deduction_period', 'OneTime')
                              ->whereYear('start_date', '=', explode('-', $month)[0])
                              ->whereMonth('start_date', '=', explode('-', $month)[1]);
                        });
                    })
                    ->get();
                
                foreach ($deductions as $deduction) {
                    $deductionAmount = $deduction->amount;
                    
                    // Check if this is a loan deduction
                    if ($deduction->loan_id) {
                        $loan = \App\Models\Loan::find($deduction->loan_id);
                        if ($loan && $loan->status === 'active') {
                            // Check if loan deduction has already been processed for this payroll month
                            $existingLoanDeduction = \App\Models\LoanDeduction::where('loan_id', $loan->loan_id)
                                ->where('payroll_month', $month)
                                ->first();
                            
                            if ($existingLoanDeduction) {
                                // Already processed for this month, skip
                                continue;
                            }
                            
                            // Verify that the loan start date is on or before the end of the current payroll month
                            $payrollMonthEnd = \Carbon\Carbon::parse($month . '-01')->endOfMonth();
                            $loanStartDate = \Carbon\Carbon::parse($loan->start_date);
                            
                            if ($loanStartDate->gt($payrollMonthEnd)) {
                                // Loan starts after this payroll month ends, skip
                                continue;
                            }
                            
                            // Check if loan is fully repaid or completed
                            if ($loan->remaining_balance <= 0 || $loan->status === 'completed') {
                                continue;
                            }
                            
                            // Use the loan's monthly deduction amount
                            $deductionAmount = $loan->monthly_deduction ?: $deduction->amount;
                            
                            // Ensure deduction doesn't exceed remaining balance
                            $deductionAmount = min($deductionAmount, $loan->remaining_balance);
                            
                            // Update loan details
                            $loan->total_repaid += $deductionAmount;
                            $loan->remaining_balance -= $deductionAmount;
                            
                            // Check if loan is now fully repaid
                            if ($loan->remaining_balance <= 0.01) {
                                $loan->remaining_balance = 0;
                                $loan->status = 'completed';
                                $loan->end_date = \Carbon\Carbon::now();
                            }
                            
                            // Calculate months completed
                            $loanStart = \Carbon\Carbon::parse($loan->start_date);
                            $currentMonth = \Carbon\Carbon::parse($month . '-01');
                            $monthsSinceStart = $loanStart->diffInMonths($currentMonth, false) + 1;
                            
                            // Update remaining months
                            $existingDeductionsCount = \App\Models\LoanDeduction::where('loan_id', $loan->loan_id)->count();
                            $loan->remaining_months = max(0, $loan->total_months - ($existingDeductionsCount + 1));
                            
                            // Check if loan term has been reached
                            if ($monthsSinceStart >= $loan->total_months) {
                                $loan->status = 'completed';
                                $loan->end_date = \Carbon\Carbon::now();
                            }
                            
                            $loan->save();
                            
                            // Create loan deduction record for tracking
                            \App\Models\LoanDeduction::create([
                                'loan_id' => $loan->loan_id,
                                'employee_id' => $pensioner->employee_id,
                                'amount_deducted' => $deductionAmount,
                                'remaining_balance' => $loan->remaining_balance,
                                'month_number' => min($monthsSinceStart, $loan->total_months),
                                'payroll_month' => $month,
                                'deduction_date' => \Carbon\Carbon::now(),
                                'status' => 'completed',
                            ]);
                        }
                    }
                    
                    $totalDeductions += $deductionAmount;
                }
                
                // Calculate net salary
                $netSalary = $monthlyPension + $totalAdditions - $totalDeductions;

                // Create or update payroll record
                PayrollRecord::updateOrCreate(
                    [
                        'employee_id' => $pensioner->employee_id,
                        'payroll_month' => $month . '-01',
                        'payment_type' => 'Pension',
                    ],
                    [
                        'grade_level_id' => $pensioner->grade_level_id,
                        'basic_salary' => $monthlyPension,
                        'payment_type' => 'Pension', // Explicitly set type to Pension
                        'total_additions' => $totalAdditions,
                        'total_deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        'status' => 'Pending Review',
                        'payment_date' => null,
                        'remarks' => $remarks,
                    ]
                );

                // Create Payment Transaction
                // Check if transaction exists to avoid duplicates
                $payrollRecord = PayrollRecord::where('employee_id', $pensioner->employee_id)
                                ->where('payroll_month', $month . '-01')
                                ->first();

                if ($payrollRecord) {
                    \App\Models\PaymentTransaction::firstOrCreate(
                        ['payroll_id' => $payrollRecord->payroll_id],
                        [
                            'employee_id' => $pensioner->employee_id,
                            'amount' => $netSalary,
                            'payment_date' => null,
                            'bank_code' => $pensioner->bank->bank_code ?? null,
                            'account_name' => $pensioner->account_name ?? ($pensioner->first_name . ' ' . $pensioner->surname),
                            'account_number' => $pensioner->account_number ?? '0000000000',
                        ]
                    );
                }
                $count++;
            }

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'generated_pension_payroll',
                'description' => "Generated pension payroll for month: {$month} for {$count} pensioners.",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Payroll', 'month' => $month, 'pensioner_count' => $count]),
            ]);

            return redirect()->route('payroll.index')
                ->with('success', 'Pension Payroll generated successfully for ' . $month . ' (' . $count . ' pensioners processed)');
        } elseif ($category === 'gratuity') {
            // --- Gratuity Payroll Generation ---
            // Include Deceased pensioners for Gratuity as they are entitled to it (via Next of Kin usually)
            // But exclude 'Not Eligible' (those with 0 amounts)
            $pensioners = \App\Models\Pensioner::with('beneficiaryComputation')
                ->where('is_gratuity_paid', false)
                ->where('gratuity_amount', '>', 0)
                ->where('status', '!=', 'Not Eligible') // Explicit exclusion
                ->get();

            $count = 0;
            foreach ($pensioners as $pensioner) {
                // Check if gratuity record already exists for this pensioner (one-off)
                $existingRecord = PayrollRecord::where('employee_id', $pensioner->employee_id)
                    ->where('payment_type', 'Gratuity')
                    ->exists();

                if ($existingRecord) continue; 
                
                $deductionAmount = 0;
                $remarks = 'Gratuity Payment';

                // Check for Overstay
                if ($pensioner->beneficiaryComputation && !empty($pensioner->beneficiaryComputation->overstay_remark)) {
                     // Calculate Overstay Deduction
                     // Logic: Check Expected Retirement Date vs Actual.
                     // Expected = Earlier of (DOB + 60) or (First Appt + 35)
                     
                     $dob = \Carbon\Carbon::parse($pensioner->date_of_birth);
                     $dofa = \Carbon\Carbon::parse($pensioner->date_of_first_appointment);
                     $actualRetirement = \Carbon\Carbon::parse($pensioner->date_of_retirement);
                     
                     // Get Annual Emolument and convert to Monthly
                     $annualEmolument = (float)($pensioner->beneficiaryComputation->total_emolument ?? 0);
                     $monthlySalary = $annualEmolument / 12;

                     // Use the service to calculate, ensuring consistency
                     $calculationService = app(\App\Services\PensionCalculationService::class);
                     $overstayData = $calculationService->calculateOverstayAmount(
                         $dob, 
                         $dofa, 
                         $actualRetirement, 
                         $monthlySalary
                     );

                     if ($overstayData['amount'] > 0) {
                         $deductionAmount = $overstayData['amount'];
                         $remarks .= " - Overstay Deduction: " . number_format($deductionAmount, 2);
                     }
                }

                $netSalary = $pensioner->gratuity_amount - $deductionAmount;
                // Ensure net salary is not negative?
                if ($netSalary < 0) $netSalary = 0;
                
                $payrollRecord = PayrollRecord::create([
                    'employee_id' => $pensioner->employee_id,
                    'payroll_month' => $month . '-01', // Record it in the current processing month
                    'grade_level_id' => $pensioner->grade_level_id,
                    'basic_salary' => $pensioner->gratuity_amount,
                    'payment_type' => 'Gratuity',
                    'status' => 'Pending Review',
                    'total_additions' => 0,
                    'total_deductions' => $deductionAmount,
                    'net_salary' => $netSalary,
                    'payment_date' => null,
                    'remarks' => $remarks,
                ]);
                
                 \App\Models\PaymentTransaction::create([
                        'payroll_id' => $payrollRecord->payroll_id,
                        'employee_id' => $payrollRecord->employee_id,
                        'amount' => $payrollRecord->net_salary,
                        'transaction_type' => 'Bank Transfer',
                        'transaction_date' => null,
                        'status' => 'Pending',
                        'bank_code' => $pensioner->bank->bank_code ?? null,
                        'account_name' => $pensioner->account_name ?? ($pensioner->first_name . ' ' . $pensioner->surname),
                        'account_number' => $pensioner->account_number ?? '0000000000',
                        'reference_id' => 'GRAT-' . strtoupper(uniqid()),
                ]);

                $count++;
            }

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'generated_gratuity_payroll',
                'description' => "Generated gratuity payroll for month: {$month} for {$count} pensioners.",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Payroll', 'month' => $month, 'pensioner_count' => $count, 'type' => 'Gratuity']),
            ]);

            return redirect()->route('payroll.index')
                ->with('success', 'Gratuity Payroll generated successfully for ' . $month . ' (' . $count . ' records processed)');
        }

        // --- Standard Staff Payroll Generation ---
        // Fetch employees (Active and Suspended) and those Retiring this month
        $startOfMonth = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = \Carbon\Carbon::parse($month . '-01')->endOfMonth();

        $employeesQuery = Employee::where(function($query) use ($startOfMonth, $endOfMonth, $appointmentTypeId) {
                // Include Active, Suspended, and employees who Retired THIS month
                $query->whereIn('status', ['Active', 'Suspended'])
                      ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                          $q->where('status', 'Retired')
                            ->where(function($sq) use ($startOfMonth, $endOfMonth) {
                                // Check both Employee table AND Pensioner table for the date
                                $sq->whereBetween('date_of_retirement', [$startOfMonth, $endOfMonth])
                                   ->orWhereHas('pensioner', function($pq) use ($startOfMonth, $endOfMonth) {
                                       $pq->whereBetween('date_of_retirement', [$startOfMonth, $endOfMonth]);
                                   });
                            });
                      });
            })
            ->where(function($query) {
                // Exclude employees who are on probation
                $query->whereNull('on_probation')
                      ->orWhere('on_probation', false)
                      ->orWhere('probation_status', '!=', 'pending');
            })
            ->where('status', '!=', 'Hold')  // Exclude employees with hold status
            ->with(['gradeLevel', 'step']);      // Load related grade level and step

        if ($appointmentTypeId) {
            $employeesQuery->where('appointment_type_id', $appointmentTypeId);

            // For Casual employees (appointment_type_id = 2), don't require grade_level_id
            // since Casual employees may not have grade levels assigned
            if ($appointmentTypeId == 2) { // Casual employees
                // No need to filter by grade_level_id for Casual employees
            } else {
                // For permanent/temporary employees, require grade level
                $employeesQuery->whereNotNull('grade_level_id');
            }
        } else {
            // If no specific appointment type selected, only include non-Casual employees with grade levels
            // and Casual employees with amounts
            $employeesQuery->where(function($query) {
                $query->where(function($q) {
                    $q->whereNotNull('grade_level_id') // Non-Casual employees with grade levels
                      ->where('appointment_type_id', '!=', 2); // Not Casual employees
                })->orWhere(function($q) {
                    $q->where('appointment_type_id', 2) // Casual employees
                      ->whereNotNull('amount'); // With contract amount
                });
            });
        }

        $employees = $employeesQuery->get();

        foreach ($employees as $employee) {
            // Check if the employee is a Casual employee
            $isCasualEmployee = $employee->isCasualEmployee();

            // For Casual employees, we don't require a grade level
            // For non-Casual employees, check if they have a grade level
            if (!$isCasualEmployee && (!$employee->gradeLevel || !$employee->gradeLevel->id)) {
                continue;
            }

            // For suspended employees, calculate payroll with special logic
            if ($employee->status === 'Suspended') {
                // Use the same employee model but indicate that it's suspended for calculation
                $calculation = $this->payrollCalculationService->calculatePayroll($employee, $month, true);

                // Create the payroll record for suspended employee with 'Pending Review' status
                $payroll = PayrollRecord::updateOrCreate(
                    [
                        'employee_id' => $employee->employee_id,
                        'payroll_month' => $month . '-01',
                        'payment_type' => $isCasualEmployee ? 'Casual' : 'Permanent',
                    ],
                    [
                        'grade_level_id' => $isCasualEmployee ? null : $employee->gradeLevel->id,
                        'basic_salary' => $calculation['basic_salary'], // Will be half for suspended employees
                        'payment_type' => $isCasualEmployee ? 'Casual' : 'Permanent',
                        'total_additions' => $calculation['total_additions'], // Additions still apply
                        'total_deductions' => $calculation['total_deductions'], // Deductions still apply
                        'net_salary' => $calculation['net_salary'], // Net salary with special calculation for suspended
                        'status' => 'Pending Review',
                        'payment_date' => null,
                        'remarks' => 'Generated for ' . $month . ' (Suspended - Special Calculation Applied)',
                    ]
                );

                // ✅ Create a PaymentTransaction for this payroll record
                \App\Models\PaymentTransaction::firstOrCreate(
                    ['payroll_id' =>  $payroll->payroll_id],
                    [
                        'employee_id' => $employee->employee_id,
                        'amount' => $calculation['net_salary'],
                        'payment_date' => null,
                        'bank_code' => $employee->bank->bank_code ?? null, // safe fallback
                        'account_name' => $employee->bank->account_name ?? ($employee->first_name . ' ' . $employee->surname),
                        'account_number' => $employee->bank->account_no ?? '0000000000',
                    ]
                );
            } else {
                // For active employees, use normal calculation
                $calculation = $this->payrollCalculationService->calculatePayroll($employee, $month, false);

                // Create the payroll record for active employee with 'Pending Review' status
                $payroll = PayrollRecord::updateOrCreate(
                    [
                        'employee_id' => $employee->employee_id,
                        'payroll_month' => $month . '-01',
                        'payment_type' => $isCasualEmployee ? 'Casual' : 'Permanent',
                    ],
                    [
                        'grade_level_id' => $isCasualEmployee ? null : $employee->gradeLevel->id,
                        'basic_salary' => $calculation['basic_salary'],
                        'payment_type' => $isCasualEmployee ? 'Casual' : 'Permanent',
                        'total_additions' => $calculation['total_additions'],
                        'total_deductions' => $calculation['total_deductions'],
                        'net_salary' => $calculation['net_salary'],
                        'status' => 'Pending Review',
                        'payment_date' => null,
                        'remarks' => 'Generated for ' . $month,
                    ]
                );

                // ✅ Create a PaymentTransaction for this payroll record
                \App\Models\PaymentTransaction::firstOrCreate(
                    ['payroll_id' =>  $payroll->payroll_id],
                    [
                        'employee_id' => $employee->employee_id,
                        'amount' => $calculation['net_salary'],
                        'payment_date' => null,
                        'bank_code' => $employee->bank->bank_code ?? null, // safe fallback
                        'account_name' => $employee->bank->account_name ?? ($employee->first_name . ' ' . $employee->surname),
                        'account_number' => $employee->bank->account_no ?? '0000000000',
                    ]
                );
            }
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_payroll',
            'description' => "Generated payroll for month: {$month} for " . count($employees) . " employees (Active and Suspended).",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Payroll', 'entity_id' => null, 'month' => $month, 'employee_count' => count($employees)]),
        ]);

        return redirect()->route('payroll.index')
            ->with('success', 'Payroll generated successfully for ' . $month . ' (Includes both Active and Suspended employees)');
    }

    // Display payroll records with search and filter functionality
    public function index(Request $request)
    {
        $query = PayrollRecord::with(['employee.gradeLevel.steps', 'employee.step', 'gradeLevel', 'transaction']);

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('payroll_id', 'like', "%{$searchTerm}%")
                  ->orWhere('remarks', 'like', "%{$searchTerm}%")
                  ->orWhereHas('employee', function($employeeQuery) use ($searchTerm) {
                      $employeeQuery->where('first_name', 'like', "%{$searchTerm}%")
                                   ->orWhere('surname', 'like', "%{$searchTerm}%")
                                   ->orWhere('employee_id', 'like', "%{$searchTerm}%")
                                   ->orWhere('staff_no', 'like', "%{$searchTerm}%")
                                   ->orWhereRaw("CONCAT_WS(' ', first_name, surname) LIKE ?", ["%{$searchTerm}%"])
                                   ->orWhereRaw("CONCAT_WS(' ', surname, first_name) LIKE ?", ["%{$searchTerm}%"])
                                   ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, surname) LIKE ?", ["%{$searchTerm}%"]);
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Employee status filter (Active/Suspended - from employee table)
        if ($request->filled('employee_status')) {
            $query->whereHas('employee', function($employeeQuery) use ($request) {
                $employeeQuery->where('status', $request->employee_status);
            });
        }

        // Appointment type filter (Contract/Permanent - from employee table)
        if ($request->filled('appointment_type')) {
            $query->whereHas('employee', function($employeeQuery) use ($request) {
                $employeeQuery->where('appointment_type_id', $request->appointment_type);
            });
        }

        // Month filter
        if ($request->filled('month_filter')) {
            $monthFilter = $request->month_filter . '-01';
            $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                  ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
        }

        // Salary range filter
        if ($request->filled('salary_range')) {
            $salaryRange = $request->salary_range;
            switch ($salaryRange) {
                case '0-50000':
                    $query->whereBetween('net_salary', [0, 50000]);
                    break;
                case '50001-100000':
                    $query->whereBetween('net_salary', [50001, 100000]);
                    break;
                case '100001-200000':
                    $query->whereBetween('net_salary', [100001, 200000]);
                    break;
                case '200001-500000':
                    $query->whereBetween('net_salary', [200001, 500000]);
                    break;
                case '500001+':
                    $query->where('net_salary', '>', 500000);
                    break;
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        switch ($sortBy) {
            case 'employee_name':
                $query->join('employees', 'payroll_records.employee_id', '=', 'employees.employee_id')
                      ->orderBy('employees.first_name', $sortDirection)
                      ->orderBy('employees.surname', $sortDirection)
                      ->select('payroll_records.*');
                break;
            case 'net_salary':
            case 'basic_salary':
            case 'payroll_month':
            case 'payroll_id':
                $query->orderBy($sortBy, $sortDirection);
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $payrolls = $query->paginate($perPage);
        $appointmentTypes = \App\Models\AppointmentType::all();

        return view('payroll.index', compact('payrolls', 'appointmentTypes'));
    }

    // Generate pay slip
    public function generatePaySlip($payrollId)
    {
        $payroll = PayrollRecord::with(['employee', 'gradeLevel'])
            ->findOrFail($payrollId);

        // Get deductions for the payroll month
        $deductions = Deduction::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth)
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth);
                      });
            })
            ->with(['deductionType', 'employee.gradeLevel.steps'])
            ->get();

        // Get additions for the payroll month
        $additions = Addition::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth)
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth);
                      });
            })
            ->with(['additionType', 'employee.gradeLevel.steps'])
            ->get();

        $pdf = PDF::loadView('payroll.payslip', compact('payroll', 'deductions', 'additions'));

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_payslip',
            'description' => "Generated payslip for employee: {$payroll->employee->first_name} {$payroll->employee->surname} for payroll ID: {$payroll->payroll_id}",
            'action_timestamp' => now('Africa/Lagos'), // Using Nigerian timezone
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payroll->payroll_id, 'employee_id' => $payroll->employee->employee_id]),
        ]);

        return $pdf->download('payslip_' . $payroll->employee->first_name . '_' . $payroll->employee->surname . '_' . $payroll->payroll_id . '.pdf');
    }

    // Generate detailed payroll report PDF
    public function generateDetailedReport(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $category = $request->input('category', 'ALL STAFF');
        
        // Get payroll records for the specified month
        $query = PayrollRecord::with(['employee.gradeLevel', 'employee.bank'])
            ->whereYear('payroll_month', Carbon::parse($month . '-01')->year)
            ->whereMonth('payroll_month', Carbon::parse($month . '-01')->month);
        
        // Apply category filter if specified
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        
        $payrolls = $query->orderBy('employee_id')->get();
        
        $pdf = PDF::loadView('reports.payroll-detailed', compact('payrolls', 'month', 'category'))
            ->setPaper('a4', 'landscape');
        
        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_detailed_payroll_report',
            'description' => "Generated detailed payroll report for month: {$month}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Payroll', 'month' => $month, 'category' => $category]),
        ]);
        
        return $pdf->download('payroll_report_' . $month . '.pdf');
    }

    // Export payroll records to Excel with filters
    public function exportPayroll(Request $request)
    {
        // Apply the same filters as in index method
        $query = PayrollRecord::with(['employee', 'gradeLevel', 'transaction']);

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('payroll_id', 'like', "%{$searchTerm}%")
                  ->orWhere('remarks', 'like', "%{$searchTerm}%")
                  ->orWhereHas('employee', function($employeeQuery) use ($searchTerm) {
                      $employeeQuery->where('first_name', 'like', "%{$searchTerm}%")
                                   ->orWhere('surname', 'like', "%{$searchTerm}%")
                                   ->orWhere('employee_id', 'like', "%{$searchTerm}%")
                                   ->orWhere('staff_no', 'like', "%{$searchTerm}%")
                                   ->orWhereRaw("CONCAT_WS(' ', first_name, surname) LIKE ?", ["%{$searchTerm}%"])
                                   ->orWhereRaw("CONCAT_WS(' ', surname, first_name) LIKE ?", ["%{$searchTerm}%"])
                                   ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, surname) LIKE ?", ["%{$searchTerm}%"]);
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Employee status filter (Active/Suspended - from employee table)
        if ($request->filled('employee_status')) {
            $query->whereHas('employee', function($employeeQuery) use ($request) {
                $employeeQuery->where('status', $request->employee_status);
            });
        }

        // Appointment type filter (Contract/Permanent - from employee table)
        if ($request->filled('appointment_type')) {
            $query->whereHas('employee', function($employeeQuery) use ($request) {
                $employeeQuery->where('appointment_type_id', $request->appointment_type);
            });
        }

        // Month filter
        if ($request->filled('month_filter')) {
            $monthFilter = $request->month_filter . '-01';
            $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                  ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
        }

        // Salary range filter
        if ($request->filled('salary_range')) {
            $salaryRange = $request->salary_range;
            switch ($salaryRange) {
                case '0-50000':
                    $query->whereBetween('net_salary', [0, 50000]);
                    break;
                case '50001-100000':
                    $query->whereBetween('net_salary', [50001, 100000]);
                    break;
                case '100001-200000':
                    $query->whereBetween('net_salary', [100001, 200000]);
                    break;
                case '200001-500000':
                    $query->whereBetween('net_salary', [200001, 500000]);
                    break;
                case '500001+':
                    $query->where('net_salary', '>', 500000);
                    break;
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        switch ($sortBy) {
            case 'employee_name':
                $query->join('employees', 'payroll_records.employee_id', '=', 'employees.employee_id')
                      ->orderBy('employees.first_name', $sortDirection)
                      ->orderBy('employees.surname', $sortDirection)
                      ->select('payroll_records.*');
                break;
            case 'net_salary':
            case 'basic_salary':
            case 'payroll_month':
            case 'payroll_id':
                $query->orderBy($sortBy, $sortDirection);
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        $payrolls = $query->get();

        $filename = 'payroll_records_' . now()->format('Y_m_d_His');
        if ($request->hasAny(['search', 'status', 'month_filter', 'salary_range', 'date_from', 'date_to'])) {
            $filename .= '_filtered';
        }
        $filename .= '.xlsx';

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'exported_payroll',
            'description' => "Exported payroll records with filters. Format: " . ($request->get('detailed', false) ? 'Detailed Excel' : 'Excel'),
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Payroll', 'entity_id' => null, 'format' => ($request->get('detailed', false) ? 'Detailed Excel' : 'Excel'), 'filters' => $request->all()]),
        ]);

        // Check if detailed export is requested
        if ($request->get('detailed', false)) {
            return Excel::download(new \App\Exports\PayrollRecordsDetailedExport($payrolls), $filename);
        } else {
            return Excel::download(new PayrollRecordsExport($payrolls), $filename);
        }
    }

    public function showDeductions(Request $request, $employeeId)
    {
        $employee = \App\Models\Employee::with('gradeLevel.steps')->findOrFail($employeeId);
        $deductions = \App\Models\Deduction::with(['deductionType', 'employee.gradeLevel.steps'])
            ->where('employee_id', $employeeId)
            ->paginate(10, ['*'], 'deductions_page');
        $deductionTypes = \App\Models\DeductionType::where('is_statutory', false)->get();

        return view('payroll.show_deductions', compact('employee', 'deductions', 'deductionTypes'));
    }

    public function showAdditions(Request $request, $employeeId)
    {
        $employee = \App\Models\Employee::findOrFail($employeeId);
        $additions = \App\Models\Addition::where('employee_id', $employeeId)
            ->paginate(10, ['*'], 'additions_page');
        $additionTypes = \App\Models\AdditionType::where('is_statutory', false)->get();

        return view('payroll.show_additions', compact('employee', 'additions', 'additionTypes'));
    }

    public function storeDeduction(Request $request, $employeeId)
    {
        $employee = Employee::with('gradeLevel.steps', 'step', 'appointmentType')->findOrFail($employeeId);
        $deductionType = DeductionType::find($request->deduction_type_id);

        // Prevent creating statutory deductions individually - they should be created via bulk process
        if ($deductionType && $deductionType->is_statutory) {
            return redirect()->back()
                ->withErrors(['error' => 'Statutory deductions must be created via the bulk deduction process.'])
                ->withInput();
        }

        // Validation rules for non-statutory deductions
        $rules = [
            'deduction_type_id' => 'required|exists:deduction_types,id',
            'amount_type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:OneTime,Monthly,Perpetual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];

        $request->validate($rules);

        // For non-statutory deductions, we need amount_type and amount
        if ($deductionType && !$deductionType->is_statutory) {
            $rules['amount_type'] = 'required|in:fixed,percentage';
            $rules['amount'] = 'required|min:0';
        }

        $request->validate($rules);

        if ($deductionType) {
            $amount = 0;

            // Calculate the deduction amount for non-statutory deductions
            if (!$deductionType->is_statutory) {
                if ($request->amount_type === 'percentage') {
                    // Check if employee is a Casual employee using the new method
                    $isCasualEmployee = $employee->isCasualEmployee();

                    if ($isCasualEmployee) {
                        // For Casual employees, use the amount field instead of step basic salary
                        if ($employee->amount && $employee->amount > 0) {
                            $contractAmount = $employee->amount;
                            if ($contractAmount > 0 && $request->amount > 0) {
                                $amount = ($request->amount / 100) * $contractAmount;
                            }
                        } else {
                            return redirect()->back()
                                ->withErrors(['error' => 'Casual employee does not have a valid amount for percentage calculation.'])
                                ->withInput();
                        }
                    } else {
                        // For regular employees, check if employee has a step with basic salary
                        if ($employee->step && $employee->step->basic_salary) {
                            // Use the specific step's basic salary for percentage calculation
                            $basicSalary = $employee->step->basic_salary;
                            if ($basicSalary > 0 && $request->amount > 0) {
                                $amount = ($request->amount / 100) * $basicSalary;
                            }
                        } else {
                            return redirect()->back()
                                ->withErrors(['error' => 'Employee does not have a valid step with basic salary.'])
                                ->withInput();
                        }
                    }
                } else {
                    // Fixed amount - use the provided amount directly
                    $amount = $request->amount;
                }

                // Update the deduction type with the provided amount/percentage for non-statutory deductions
                $deductionType->rate_or_amount = $request->amount;
                $deductionType->calculation_type = $request->amount_type === 'percentage' ? 'percentage' : 'fixed_amount';
                $deductionType->save();
            }

            $deduction = Deduction::create([
                'employee_id' => $employeeId,
                'deduction_type' => $deductionType->name,
                'amount' => $amount,
                'amount_type' => $request->amount_type ?? null,
                'deduction_period' => $request->period,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'deduction_type_id' => $deductionType->id,
            ]);

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'created_deduction',
                'description' => "Created deduction '{$deductionType->name}' for employee ID: {$employeeId}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Deduction', 'entity_id' => $deduction->id, 'employee_id' => $employeeId, 'deduction_type' => $deductionType->name]),
            ]);
        }

        return redirect()->route('payroll.deductions.show', $employeeId)
            ->with('success', 'Deduction added successfully.');
    }

    public function storeAddition(Request $request, $employeeId)
    {
        $rules = [
            'addition_type_id' => 'required|exists:addition_types,id',
            'amount_type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0.01',
            'period' => 'required|in:OneTime,Monthly,Perpetual',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date', // Require end_date for all periods now
        ];

        // For OneTime period, end_date is not required as per business logic
        if ($request->period === 'OneTime') {
            $rules['end_date'] = 'nullable|date|after_or_equal:start_date';
        }

        $request->validate($rules);

        $additionType = AdditionType::find($request->addition_type_id);

        // Prevent creating statutory additions individually - they should be created via bulk process
        if ($additionType && $additionType->is_statutory) {
            return redirect()->back()
                ->withErrors(['error' => 'Statutory additions must be created via the bulk addition process.'])
                ->withInput();
        }

        $employee = Employee::with('step', 'appointmentType')->findOrFail($employeeId);

        if ($additionType) {
            $amount = 0;

            if ($request->amount_type === 'percentage') {
                // Check if employee is a Casual employee
                $isCasualEmployee = $employee->isCasualEmployee();

                if ($isCasualEmployee) {
                    // For Casual employees, use the amount field instead of step basic salary
                    if ($employee->amount && $employee->amount > 0) {
                        $contractAmount = $employee->amount;
                        $amount = ($request->amount / 100) * $contractAmount;

                        if ($amount < 0.01) {
                            return redirect()->back()
                                ->withErrors(['error' => 'Calculated addition amount is too small or zero. Check employee\'s contract amount.'])
                                ->withInput();
                        }
                    } else {
                        return redirect()->back()
                            ->withErrors(['error' => 'Casual employee does not have a valid amount for percentage calculation.'])
                            ->withInput();
                    }
                } else {
                    // For regular employees, check if employee has a step with basic salary
                    if (!$employee->step || !$employee->step->basic_salary) {
                        return redirect()->back()
                            ->withErrors(['error' => 'Employee does not have a valid step with basic salary for percentage calculation.'])
                            ->withInput();
                    }
                    // Calculate addition amount based on the employee's step basic salary
                    $basicSalary = $employee->step->basic_salary;
                    $amount = ($request->amount / 100) * $basicSalary;

                    if ($amount < 0.01) {
                        return redirect()->back()
                            ->withErrors(['error' => 'Calculated addition amount is too small or zero. Check employee\'s basic salary.'])
                            ->withInput();
                    }
                }
            } else {
                // Fixed amount - use the provided amount directly
                $amount = $request->amount;
            }

            // If OneTime, force end_date to be null (or same as start_date if system requires it, but usually null is fine for OneTime)
            // The request says "if onetime just use that start date".
            $endDate = $request->period === 'OneTime' ? null : $request->end_date;

            $addition = Addition::create([
                'employee_id' => $employeeId,
                'addition_type_id' => $request->addition_type_id,
                'amount_type' => $request->amount_type,
                'amount' => $amount,
                'addition_period' => $request->period,
                'start_date' => $request->start_date,
                'end_date' => $endDate,
            ]);

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'created_addition',
                'description' => "Created addition '{$additionType->name}' for employee ID: {$employeeId}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Addition', 'entity_id' => $addition->id, 'employee_id' => $employeeId, 'addition_type' => $additionType->name]),
            ]);
        }

        return redirect()->route('payroll.additions.show', $employeeId)
            ->with('success', 'Addition added successfully.');
    }

    public function manageAllAdjustments(Request $request)
    {
        // Get employee type from request (default to 'active')
        $employeeType = $request->input('employee_type', 'active');

        // Base query with relationships
        $query = \App\Models\Employee::with(['department', 'gradeLevel']);

        // Filter by employee type (active or retired)
        if ($employeeType === 'retired') {
            // Show only retired employees (pensioners)
            $query->where('status', 'Retired');
        } else {
            // Show active and suspended employees (default)
            $query->whereIn('status', ['Active', 'Suspended'])
                  ->where('status', '!=', 'Hold');  // Exclude employees with hold status
        }

        // Search functionality - include full name search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('staff_no', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', surname) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', surname) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CONCAT(surname, ' ', first_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Status filter
        if ($request->filled('employee_status')) {
            $query->where('status', $request->employee_status);
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'employee_id');
        $sortDirection = $request->get('sort_direction', 'asc');

        $allowedSorts = ['employee_id', 'first_name', 'surname'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('employee_id', 'asc');
        }

        $employees = $query->paginate(10)->withQueryString();

        $deductionTypes = \App\Models\DeductionType::where('is_statutory', false)->get();
        $additionTypes = \App\Models\AdditionType::where('is_statutory', false)->get();

        // Get departments for filter dropdown
        $departments = \App\Models\Department::orderBy('department_name')->get();

        return view('payroll.manage_all_adjustments', compact('employees', 'deductionTypes', 'additionTypes', 'departments', 'employeeType'));
    }



    // Advanced search method for AJAX requests
    public function search(Request $request)
    {
        $query = PayrollRecord::with(['employee', 'gradeLevel']);

        if ($request->filled('term')) {
            $term = $request->term;
            $query->where(function($q) use ($term) {
                $q->where('payroll_id', 'like', "%{$term}%")
                  ->orWhereHas('employee', function($employeeQuery) use ($term) {
                      $employeeQuery->where('first_name', 'like', "%{$term}%")
                                   ->orWhere('surname', 'like', "%{$term}%")
                                   ->orWhere('employee_id', 'like', "%{$term}%");
                  });
            });
        }

        $results = $query->limit(10)->get()->map(function($payroll) {
            return [
                'id' => $payroll->payroll_id,
                'text' => $payroll->employee->first_name . ' ' . $payroll->employee->surname . ' - ' . $payroll->payroll_id,
                'employee_name' => $payroll->employee->first_name . ' ' . $payroll->employee->surname,
                'net_salary' => number_format($payroll->net_salary, 2),
                'status' => $payroll->status
            ];
        });

        return response()->json($results);
    }

    // Get payroll statistics for dashboard
    public function getStatistics(Request $request)
    {
        $query = PayrollRecord::query();

        // Apply same filters as index method if provided
        if ($request->filled('month_filter')) {
            $monthFilter = $request->month_filter . '-01';
            $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                  ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
        }

        $stats = [
            'total_records' => $query->count(),
            'total_net_salary' => $query->sum('net_salary'),
            'total_basic_salary' => $query->sum('basic_salary'),
            'total_additions' => $query->sum('total_additions'),
            'total_deductions' => $query->sum('total_deductions'),
            'status_breakdown' => $query->select('status', DB::raw('count(*) as count'))
                                       ->groupBy('status')
                                       ->pluck('count', 'status')
                                       ->toArray(),
            'average_salary' => $query->avg('net_salary'),
        ];

        return response()->json($stats);
    }

    // Show individual payroll record
    public function show($payrollId)
    {
        $payroll = PayrollRecord::with(['employee', 'gradeLevel', 'transaction'])
            ->findOrFail($payrollId);

        $deductions = Deduction::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth)
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth);
                      });
            })
            ->with(['deductionType', 'employee.gradeLevel.steps', 'loan']) // Eager load relationships for amount calculation
            ->get()
            ->filter(function ($deduction) use ($payroll) {
                // If this is a loan-related deduction, check if the loan was still active during this payroll period
                if ($deduction->loan_id) {
                    $loan = $deduction->loan;
                    if ($loan) {
                        // Check if the loan was still active during the payroll month
                        $payrollMonth = Carbon::parse($payroll->payroll_month);
                        $loanStart = Carbon::parse($loan->start_date);
                        $loanEnd = Carbon::parse($loan->end_date);

                        // Loan is active if payroll month falls within loan's start and end dates
                        // and the loan status is not completed
                        $isLoanActive = $payrollMonth->between($loanStart, $loanEnd) && $loan->status !== 'completed';

                        // Additionally, check if loan was completed before this payroll month
                        if (!$isLoanActive && $loan->status === 'completed') {
                            // Check if loan ended before this payroll month
                            $loanWasActiveBefore = $loanEnd->lt($payrollMonth);
                            // If loan ended before this payroll month, it should not appear in this payroll
                            if ($loanWasActiveBefore) {
                                return false;
                            }
                        }

                        // For loans that have existing LoanDeduction records for this month,
                        // check if they were already processed this month (to avoid showing both template deduction and processed loan deduction)
                        $existingLoanDeduction = \App\Models\LoanDeduction::where('loan_id', $loan->loan_id)
                            ->where('payroll_month', $payrollMonth->format('Y-m'))
                            ->first();

                        // If a loan has an existing LoanDeduction for this month, it means it was processed
                        // by the payroll calculation service, so we should not show the template deduction
                        return !$existingLoanDeduction && $isLoanActive;
                    }
                }
                return true; // For non-loan deductions, use the original logic
            });

        $additions = Addition::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth->endOfMonth())
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth->startOfMonth());
                      });
            })
            ->with(['additionType', 'employee.gradeLevel.steps'])
            ->get()
            ->filter(function ($addition) use ($payroll) {
                if ($addition->addition_period === 'OneTime') {
                    $additionStartDate = Carbon::parse($addition->start_date)->startOfDay();
                    $payrollMonthStart = Carbon::parse($payroll->payroll_month)->startOfMonth();
                    $payrollMonthEnd = Carbon::parse($payroll->payroll_month)->endOfMonth();
                    return $additionStartDate->between($payrollMonthStart, $payrollMonthEnd);
                }
                return true;
            });

        return view('payroll.show', compact('payroll', 'deductions', 'additions'));
    }

    // Get detailed payroll information with deductions and additions for a specific month
    public function getDetailedPayroll($payrollId)
    {
        $payroll = PayrollRecord::with(['employee', 'gradeLevel'])
            ->findOrFail($payrollId);

        // Get deductions for the payroll month
        $deductions = Deduction::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth)
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth);
                      });
            })
            ->with(['deductionType', 'employee.gradeLevel.steps'])
            ->get();

        // Get additions for the payroll month
        $additions = Addition::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth)
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth);
                      });
            })
            ->with(['additionType', 'employee.gradeLevel.steps'])
            ->get()
            ->filter(function ($addition) use ($payroll) {
                if ($addition->addition_period === 'OneTime') {
                    $additionStartDate = Carbon::parse($addition->start_date)->startOfDay();
                    $payrollMonthStart = Carbon::parse($payroll->payroll_month)->startOfMonth();
                    $payrollMonthEnd = Carbon::parse($payroll->payroll_month)->endOfMonth();
                    return $additionStartDate->between($payrollMonthStart, $payrollMonthEnd);
                }
                return true;
            });

        // Format the response
        $deductionsData = $deductions->map(function ($deduction) {
            return [
                'type' => $deduction->deduction_type,
                'amount' => $deduction->amount,
                'formatted_amount' => $deduction->formatted_amount,
                'calculation_type' => $deduction->calculation_type_description,
            ];
        });

        $additionsData = $additions->map(function ($addition) {
            return [
                'type' => $addition->additionType ? $addition->additionType->name : $addition->addition_type,
                'amount' => $addition->amount,
                'formatted_amount' => $addition->formatted_amount,
                'calculation_type' => $addition->calculation_type_description,
            ];
        });

        return response()->json([
            'payroll' => $payroll,
            'deductions' => $deductionsData,
            'additions' => $additionsData,
            'payroll_month' => Carbon::parse($payroll->payroll_month)->format('F Y'),
        ]);
    }

    // Edit payroll record
    public function edit($payrollId)
    {
        $payroll = PayrollRecord::with(['employee', 'gradeLevel'])
            ->findOrFail($payrollId);

        return view('payroll.edit', compact('payroll'));
    }

    // Update payroll record
    public function update(Request $request, $payrollId)
    {
        $request->validate([
            'status' => 'required|in:Pending,Processed,Approved,Paid',
            'payment_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:500',
        ]);

        $payroll = PayrollRecord::findOrFail($payrollId);

        $payroll->update([
            'status' => $request->status,
            'payment_date' => $request->payment_date,
            'remarks' => $request->remarks,
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated_payroll',
            'description' => "Updated payroll record ID: {$payrollId}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId, 'status' => $request->status]),
        ]);

        return redirect()->route('payroll.show', $payrollId)
            ->with('success', 'Payroll record updated successfully.');
    }

    // Delete payroll record
    public function destroy($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);

        // Delete associated transaction if exists
        if ($payroll->transaction) {
            $payroll->transaction->delete();
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'deleted_payroll',
            'description' => "Deleted payroll record ID: {$payrollId}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        $payroll->delete();

        return redirect()->route('payroll.index')
            ->with('success', 'Payroll record deleted successfully.');
    }

    // Bulk update payroll status
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'payroll_ids' => 'required|array',
            'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            'status' => 'required|in:Pending,Processed,Approved,Paid',
        ]);

        $updated = PayrollRecord::whereIn('payroll_id', $request->payroll_ids)
                                ->update([
                                    'status' => $request->status,
                                    'updated_at' => now()
                                ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_updated_payroll_status',
            'description' => "Bulk updated status to '{$request->status}' for " . count($request->payroll_ids) . " payroll records.",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'payroll_ids' => $request->payroll_ids, 'status' => $request->status]),
        ]);

        // --- Gratuity Paid Status Update Logic for Bulk Update ---
        if ($request->status === 'Approved' || $request->status === 'Paid') {
            $gratuityRecords = PayrollRecord::whereIn('payroll_id', $request->payroll_ids)
                ->where('payment_type', 'Gratuity')
                ->get();

            foreach ($gratuityRecords as $record) {
                $pensioner = \App\Models\Pensioner::where('employee_id', $record->employee_id)->first();
                
                if ($pensioner && !$pensioner->is_gratuity_paid) {
                    $pensioner->update([
                        'is_gratuity_paid' => true,
                        'gratuity_paid_date' => now()
                    ]);
                    \Illuminate\Support\Facades\Log::info("Auto-marked Gratuity as PAID for Pensioner ID: {$pensioner->id} (Employee: {$pensioner->employee_id}) upon Bulk Update to {$request->status}.");
                }
            }
        }
        // ----------------------------------------

        return redirect()->back()->with('success', "Updated {$updated} payroll records to {$request->status} status.");
    }

    // Recalculate payroll for a specific record
    public function recalculate($payrollId)
    {
        $payroll = PayrollRecord::with(['employee', 'gradeLevel'])
            ->findOrFail($payrollId);

        if (!$payroll->employee || !$payroll->employee->gradeLevel) {
            return redirect()->back()
                ->with('error', 'Cannot recalculate: Employee or grade level not found.');
        }

        // Get the payroll month
        $month = Carbon::parse($payroll->payroll_month)->format('Y-m');

        // Check if the employee is suspended and pass the correct flag
        $isSuspended = $payroll->employee->status === 'Suspended';

        // Recalculate using the payroll service
        $calculation = $this->payrollCalculationService->calculatePayroll($payroll->employee, $month, $isSuspended);

        // Update the payroll record
        $payroll->update([
            'basic_salary' => $calculation['basic_salary'], // Use calculated basic salary (might be halved for suspended)
            'total_additions' => $calculation['total_additions'],
            'total_deductions' => $calculation['total_deductions'],
            'net_salary' => $calculation['net_salary'],
            'remarks' => ($payroll->remarks ?? '') . ' | Recalculated on ' . now()->format('Y-m-d H:i:s'),
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'recalculated_payroll',
            'description' => "Recalculated payroll for record ID: {$payrollId}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        // Update associated transaction amount if exists
        if ($payroll->transaction) {
            $payroll->transaction->update([
                'amount' => $calculation['net_salary']
            ]);
        }

        return redirect()->back()
            ->with('success', 'Payroll recalculated successfully.');
    }

    // Approve payroll record
    public function approve($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);

        $payroll->update([
            'status' => 'Approved',
            'updated_at' => now()
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'approved_payroll',
            'description' => "Approved payroll record ID: {$payrollId}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        // --- Gratuity Paid Status Update Logic for Single Approve ---
        if ($payroll->payment_type === 'Gratuity') {
             $pensioner = \App\Models\Pensioner::where('employee_id', $payroll->employee_id)->first();
             if ($pensioner && !$pensioner->is_gratuity_paid) {
                 $pensioner->update([
                     'is_gratuity_paid' => true,
                     'gratuity_paid_date' => now()
                 ]);
                 \Illuminate\Support\Facades\Log::info("Auto-marked Gratuity as PAID for Pensioner ID: {$pensioner->id} (Employee: {$pensioner->employee_id}) upon Single Approval.");
             }
        }
        // ----------------------------------------

        return redirect()->back()
            ->with('success', 'Payroll record approved successfully.');
    }

    // Reject payroll record
    public function reject(Request $request, $payrollId)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $payroll = PayrollRecord::findOrFail($payrollId);

        $payroll->update([
            'status' => 'Rejected',
            'remarks' => ($payroll->remarks ?? '') . ' | Rejected: ' . $request->reason,
            'updated_at' => now()
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'rejected_payroll',
            'description' => "Rejected payroll record ID: {$payrollId} with reason: {$request->reason}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId, 'reason' => $request->reason]),
        ]);

        return redirect()->back()
            ->with('success', 'Payroll record rejected.');
    }

    public function additions(Request $request)
    {
        $allAdditionTypes = AdditionType::all();
        $statutoryAdditions = $allAdditionTypes->where('is_statutory', true);
        $nonStatutoryAdditions = $allAdditionTypes->where('is_statutory', false);

        $departments = Department::orderBy('department_name')->get();
        $gradeLevels = GradeLevel::orderBy('name')->get();
        $appointmentTypes = \App\Models\AppointmentType::all();

        // Get employee type from request (default to 'active')
        $employeeType = $request->input('employee_type', 'active');

        // Base query with department and grade level relationships
        $employeesQuery = Employee::with(['department', 'gradeLevel']);

        // Filter by employee type (active or retired)
        if ($employeeType === 'retired') {
            // Show only retired employees (pensioners)
            $employeesQuery->where('status', 'Retired');
        } else {
            // Show active and suspended employees (default)
            $employeesQuery->where(function($query) {
                $query->whereIn('status', ['Active', 'Suspended'])
                      ->orWhere(function($q) {
                          $q->where('status', 'Retired')
                            ->where(function($d) {
                                 $d->whereBetween('date_of_retirement', [now()->startOfMonth(), now()->endOfMonth()])
                                   ->orWhereHas('pensioner', function($p) {
                                       $p->whereBetween('date_of_retirement', [now()->startOfMonth(), now()->endOfMonth()]);
                                   });
                            });
                      });
            });
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $employeesQuery->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('surname', 'like', "%{$searchTerm}%")
                    ->orWhere('employee_id', 'like', "%{$searchTerm}%")
                    ->orWhere('staff_no', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }

        if ($request->filled('grade_level_id')) {
            $employeesQuery->where('grade_level_id', $request->grade_level_id);
        }

        if ($request->filled('appointment_type_id')) {
            $employeesQuery->where('appointment_type_id', $request->appointment_type_id);
        }

        $employees = $employeesQuery->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('payroll._employee_rows', compact('employees'))->render(),
                'next_page_url' => $employees->nextPageUrl(),
            ]);
        }

        return view('payroll.additions', compact('statutoryAdditions', 'nonStatutoryAdditions', 'departments', 'gradeLevels', 'employees', 'appointmentTypes', 'employeeType'));
    }

    public function storeBulkAdditions(Request $request)
    {
        $request->validate([
            'addition_types' => 'sometimes|array',
            'addition_types.*' => 'integer|exists:addition_types,id',
            'type_id' => 'sometimes|integer|exists:addition_types,id',
            'statutory_addition_month' => 'required_with:addition_types|date_format:Y-m',
            'period' => 'required_with:type_id|nullable|in:OneTime,Monthly,Perpetual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'required_with:type_id|nullable|numeric|min:0',
            'amount_type' => 'required_with:type_id|nullable|in:fixed,percentage',
            'employee_ids' => 'required_if:select_all_pages,0|array',
            'employee_ids.*' => 'exists:employees,employee_id',
        ]);

        if (!$request->filled('addition_types') && !$request->filled('type_id')) {
            return redirect()->back()
                ->withErrors(['addition_error' => 'Please select at least one addition type.'])
                ->withInput();
        }

        $employees = collect();

        if ($request->input('select_all_pages') == '1') {
            $employeesQuery = Employee::where(function($query) {
                    $query->whereIn('status', ['Active', 'Suspended'])
                          ->orWhere(function($q) {
                              $q->where('status', 'Retired')
                                ->where(function($d) {
                                     $d->whereBetween('date_of_retirement', [now()->startOfMonth(), now()->endOfMonth()])
                                       ->orWhereHas('pensioner', function($p) {
                                           $p->whereBetween('date_of_retirement', [now()->startOfMonth(), now()->endOfMonth()]);
                                       });
                                });
                          });
                })
                ->where('status', '!=', 'Hold')  // Exclude employees with hold status
                ->with('gradeLevel');

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $employeesQuery->where(function ($q) use ($searchTerm) {
                    $q->where('first_name', 'like', "%{$searchTerm}%")
                        ->orWhere('surname', 'like', "%{$searchTerm}%")
                        ->orWhere('employee_id', 'like', "%{$searchTerm}%")
                        ->orWhere('staff_no', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->filled('department_id')) {
                $employeesQuery->where('department_id', $request->department_id);
            }

            if ($request->filled('grade_level_id')) {
                $employeesQuery->where('grade_level_id', $request->grade_level_id);
            }

            // Always apply appointment type filter, defaulting to 1 (Permanent) if not specified
            $appointmentTypeId = $request->input('appointment_type_id', 1);
            $employeesQuery->where('appointment_type_id', $appointmentTypeId);

            $employees = $employeesQuery->get();
        } else {
            $employees = Employee::whereIn('employee_id', $request->employee_ids)
                ->where('status', '!=', 'Hold')  // Exclude employees with hold status
                ->with('gradeLevel', 'step')->get();
        }

        $additionTypeIds = $request->input('addition_types', []);
        if ($request->filled('type_id')) {
            $additionTypeIds[] = $request->input('type_id');
        }

        $additionTypes = AdditionType::findMany($additionTypeIds);

        $data = $request->only(['period', 'start_date', 'end_date', 'amount', 'amount_type', 'statutory_addition_month']);

        foreach ($employees as $employee) {
            foreach ($additionTypes as $additionType) {
                $amount = 0;
                if ($additionType->is_statutory) {
                    if ($additionType->calculation_type === 'percentage') {
                        if ($employee->step && $employee->step->basic_salary) {
                            $amount = ($additionType->rate_or_amount / 100) * $employee->step->basic_salary;
                        }
                    } else {
                        $amount = $additionType->rate_or_amount;
                    }
                } else {
                    if ($data['amount_type'] === 'percentage') {
                        if ($employee->gradeLevel && $employee->gradeLevel->basic_salary) {
                            $amount = ($data['amount'] / 100) * $employee->gradeLevel->basic_salary;
                        } else {
                            continue;
                        }
                    } else {
                        $amount = $data['amount'];
                    }
                }

                Addition::create([
                    'employee_id' => $employee->employee_id,
                    'addition_type_id' => $additionType->id,
                    'amount' => $amount,
                    'addition_period' => $additionType->is_statutory ? 'Monthly' : $data['period'],
                    'start_date' => $additionType->is_statutory ?
                        (isset($data['statutory_addition_month']) && $data['statutory_addition_month'] ?
                            $data['statutory_addition_month'] . '-01' :
                            date('Y-m') . '-01') :
                        $data['start_date'],
                    'end_date' => $additionType->is_statutory ?
                        date('Y-m-t', strtotime(
                            (isset($data['statutory_addition_month']) && $data['statutory_addition_month'] ?
                                $data['statutory_addition_month'] :
                                date('Y-m')) . '-01'
                        )) :
                        $data['end_date'],
                ]);
            }
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_created_additions',
            'description' => "Bulk created additions for " . $employees->count() . " employees.",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Addition', 'entity_id' => null, 'employee_count' => $employees->count(), 'addition_types' => $additionTypeIds]),
        ]);

        return redirect()->route('payroll.additions')
            ->with('success', 'Bulk addition assignment completed successfully for ' . $employees->count() . ' employees.');
    }

    public function deductions(Request $request)
    {
        $allDeductionTypes = DeductionType::all();

        // Filter out loan-related deductions from non-statutory deductions
        $loanKeywords = ['loan', 'advance', 'cash advance', 'special loan', 'staff loan', 'salary advance'];

        $statutoryDeductions = $allDeductionTypes->where('is_statutory', true);

        $nonStatutoryDeductions = $allDeductionTypes->where('is_statutory', false)->filter(function ($deductionType) use ($loanKeywords) {
            // Check if the deduction type name contains any loan-related keywords (case-insensitive)
            $lowerName = strtolower($deductionType->name);
            foreach ($loanKeywords as $keyword) {
                if (strpos($lowerName, $keyword) !== false) {
                    return false; // Exclude this deduction type
                }
            }
            return true; // Include this deduction type
        });

        $departments = Department::orderBy('department_name')->get();
        $gradeLevels = GradeLevel::orderBy('name')->get();
        $appointmentTypes = \App\Models\AppointmentType::all();

        // Get employee type from request (default to 'active')
        $employeeType = $request->input('employee_type', 'active');

        // Base query with department and grade level relationships
        $employeesQuery = Employee::with(['department', 'gradeLevel']);

        // Filter by employee type (active or retired)
        if ($employeeType === 'retired') {
            // Show only retired employees (pensioners)
            $employeesQuery->where('status', 'Retired');
        } else {
            // Show active and suspended employees (default)
            $employeesQuery->where(function($query) {
                $query->whereIn('status', ['Active', 'Suspended'])
                      ->orWhere(function($q) {
                          $q->where('status', 'Retired')
                            ->where(function($d) {
                                 $d->whereBetween('date_of_retirement', [now()->startOfMonth(), now()->endOfMonth()])
                                   ->orWhereHas('pensioner', function($p) {
                                       $p->whereBetween('date_of_retirement', [now()->startOfMonth(), now()->endOfMonth()]);
                                   });
                            });
                      });
            });
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $employeesQuery->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('surname', 'like', "%{$searchTerm}%")
                    ->orWhere('employee_id', 'like', "%{$searchTerm}%")
                    ->orWhere('staff_no', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }

        if ($request->filled('grade_level_id')) {
            $employeesQuery->where('grade_level_id', $request->grade_level_id);
        }

        if ($request->filled('appointment_type_id')) {
            $employeesQuery->where('appointment_type_id', $request->appointment_type_id);
        }

        $employees = $employeesQuery->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('payroll._employee_rows', compact('employees'))->render(),
                'next_page_url' => $employees->nextPageUrl(),
            ]);
        }

        return view('payroll.deductions', compact('statutoryDeductions', 'nonStatutoryDeductions', 'departments', 'gradeLevels', 'employees', 'appointmentTypes', 'employeeType'));
    }

    public function storeBulkDeductions(Request $request)
    {
        $request->validate([
            'deduction_types' => 'sometimes|array',
            'deduction_types.*' => 'integer|exists:deduction_types,id',
            'type_id' => 'sometimes|integer|exists:deduction_types,id',
            'statutory_deduction_month' => 'required_with:deduction_types|date_format:Y-m',
            'period' => 'required_with:type_id|nullable|in:OneTime,Monthly,Perpetual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'required_with:type_id|nullable|numeric|min:0',
            'amount_type' => 'required_with:type_id|nullable|in:fixed,percentage',
            'employee_ids' => 'required_if:select_all_pages,0|array',
            'employee_ids.*' => 'exists:employees,employee_id',
        ]);

        if (!$request->filled('deduction_types') && !$request->filled('type_id')) {
            return redirect()->back()
                ->withErrors(['deduction_error' => 'Please select at least one deduction type.'])
                ->withInput();
        }

        $employees = collect();

        if ($request->input('select_all_pages') == '1') {
            $employeesQuery = Employee::where(function($query) {
                    $query->whereIn('status', ['Active', 'Suspended'])
                          ->orWhere(function($q) {
                              $q->where('status', 'Retired')
                                ->where(function($d) {
                                     $d->whereBetween('date_of_retirement', [now()->startOfMonth(), now()->endOfMonth()])
                                       ->orWhereHas('pensioner', function($p) {
                                           $p->whereBetween('date_of_retirement', [now()->startOfMonth(), now()->endOfMonth()]);
                                       });
                                });
                          });
                })
                ->where('status', '!=', 'Hold')  // Exclude employees with hold status
                ->with('gradeLevel', 'appointmentType');

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $employeesQuery->where(function ($q) use ($searchTerm) {
                    $q->where('first_name', 'like', "%{$searchTerm}%")
                        ->orWhere('surname', 'like', "%{$searchTerm}%")
                        ->orWhere('employee_id', 'like', "%{$searchTerm}%")
                        ->orWhere('staff_no', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->filled('department_id')) {
                $employeesQuery->where('department_id', $request->department_id);
            }

            if ($request->filled('grade_level_id')) {
                $employeesQuery->where('grade_level_id', $request->grade_level_id);
            }

            $appointmentTypeId = $request->input('appointment_type_id', 1); // Default to Permanent
            $employeesQuery->where('appointment_type_id', $appointmentTypeId);

            $employees = $employeesQuery->get();
        } else {
            $employees = Employee::whereIn('employee_id', $request->employee_ids)
                ->where('status', '!=', 'Hold')  // Exclude employees with hold status
                ->with('gradeLevel', 'appointmentType')->get();
        }

        $deductionTypeIds = $request->input('deduction_types', []);
        if ($request->filled('type_id')) {
            $deductionTypeIds[] = $request->input('type_id');
        }

        $deductionTypes = DeductionType::findMany($deductionTypeIds);

        $data = $request->only(['period', 'start_date', 'end_date', 'amount', 'amount_type', 'statutory_deduction_month']);

        $deductionsCreated = 0;
        $casualSkipped = 0;

        foreach ($employees as $employee) {
            foreach ($deductionTypes as $deductionType) {
                $amount = 0;
                
                // Skip statutory deductions for casual employees
                if ($deductionType->is_statutory && $employee->appointmentType && $employee->appointmentType->name === 'Casual') {
                    $casualSkipped++;
                    continue;
                }
                
                if ($deductionType->is_statutory) {
                    if ($deductionType->calculation_type === 'percentage') {
                        // Check if employee has a grade level with steps for statutory deductions
                        if ($employee->gradeLevel && $employee->gradeLevel->steps->isNotEmpty()) {
                            // Use the specific step if assigned, otherwise use the first step
                            $basicSalary = $employee->step ? $employee->step->basic_salary : $employee->gradeLevel->steps->first()->basic_salary;
                            if ($basicSalary > 0) {
                                // Calculate base deduction amount based on employee's basic salary
                                $amount = ($deductionType->rate_or_amount / 100) * $basicSalary;

                                // For suspended employees, halve the calculated percentage-based deduction
                                if ($employee->status === 'Suspended') {
                                    $amount = $amount / 2;
                                }
                            }
                        }
                    } else {
                        // For fixed amount statutory deductions
                        // For suspended employees, halve the fixed amount
                        if ($employee->status === 'Suspended') {
                            $amount = $deductionType->rate_or_amount / 2;
                        } else {
                            $amount = $deductionType->rate_or_amount;
                        }
                    }
                } else {
                    if ($data['amount_type'] === 'percentage') {
                        if ($employee->gradeLevel && $employee->gradeLevel->basic_salary) {
                            // Calculate non-statutory percentage based deduction
                            $amount = ($data['amount'] / 100) * $employee->gradeLevel->basic_salary;

                            // For suspended employees, also halve non-statutory percentage-based deductions if they are linked to basic salary
                            if ($employee->status === 'Suspended') {
                                $amount = $amount / 2;
                            }
                        } else {
                            continue;
                        }
                    } else {
                        // Fixed amount non-statutory deduction remains full for suspended employees
                        $amount = $data['amount'];
                    }
                }

                // --- Apply Proration for Retiring Staff (User Request: "Check right from bulk deduction") ---
                // Determine the target month for this deduction
                $targetDate = null;
                if ($deductionType->is_statutory && isset($data['statutory_deduction_month'])) {
                    $targetDate = \Carbon\Carbon::parse($data['statutory_deduction_month'] . '-01');
                } elseif (isset($data['start_date'])) {
                    $targetDate = \Carbon\Carbon::parse($data['start_date']);
                }

                if ($targetDate) {
                     $retirementDate = null;
                     if ($employee->date_of_retirement) {
                         $retirementDate = \Carbon\Carbon::parse($employee->date_of_retirement);
                     } elseif ($employee->pensioner && $employee->pensioner->date_of_retirement) {
                         $retirementDate = \Carbon\Carbon::parse($employee->pensioner->date_of_retirement);
                     }

                     // If retiring within this target month, scale down the deduction amount
                     if ($retirementDate && $retirementDate->isSameMonth($targetDate)) {
                         $daysInMonth = $targetDate->daysInMonth;
                         $activeDays = $retirementDate->day; // e.g. 15th = 15 days
                         $prorationFactor = $activeDays / $daysInMonth;
                         
                         $amount = $amount * $prorationFactor;
                     }
                }
                // -----------------------------------------------------------------------------------------

                Deduction::create([
                    'employee_id' => $employee->employee_id,
                    'deduction_type' => $deductionType->name,
                    'amount' => $amount,
                    'deduction_period' => $deductionType->is_statutory ? 'Monthly' : $data['period'],
                    'start_date' => $deductionType->is_statutory ?
                        (isset($data['statutory_deduction_month']) && $data['statutory_deduction_month'] ?
                            $data['statutory_deduction_month'] . '-01' :
                            date('Y-m') . '-01') :
                        $data['start_date'],
                    'end_date' => $deductionType->is_statutory ?
                        date('Y-m-t', strtotime(
                            (isset($data['statutory_deduction_month']) && $data['statutory_deduction_month'] ?
                                $data['statutory_deduction_month'] :
                                date('Y-m')) . '-01'
                        )) :
                        $data['end_date'],
                    'deduction_type_id' => $deductionType->id,
                ]);
                
                $deductionsCreated++;
            }
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_created_deductions',
            'description' => "Bulk created {$deductionsCreated} deductions for " . $employees->count() . " employees. Skipped {$casualSkipped} statutory deductions for casual employees.",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Deduction', 'entity_id' => null, 'employee_count' => $employees->count(), 'deduction_types' => $deductionTypeIds, 'deductions_created' => $deductionsCreated, 'casual_skipped' => $casualSkipped]),
        ]);

        $staffCount = $employees->count();
        $successMessage = "Bulk deduction assignment completed: {$staffCount} staff members assigned deductions ({$deductionsCreated} deduction records created).";
        if ($casualSkipped > 0) {
            $successMessage .= " ({$casualSkipped} statutory deductions skipped for casual employees)";
        }

        return redirect()->route('payroll.deductions')
            ->with('success', $successMessage);
    }

    public function bulkDeductions()
    {
        return view('payroll.bulk_deductions');
    }

    // Bulk send all payroll records for review
    public function bulkSendForReview(Request $request)
    {
        if ($request->has('select_all_pages') && $request->input('select_all_pages') == '1') {
            // Apply the same filters used in the index method to get all matching records
            $query = PayrollRecord::query();

            // Apply filters similar to index method
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('month_filter')) {
                $monthFilter = $request->month_filter . '-01';
                $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                      ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
            }

            // Only payroll records with 'Pending Review' status can be sent for review
            $query->where('status', 'Pending Review');

            $updated = $query->update([
                'status' => 'Under Review',
                'updated_at' => now()
            ]);

            $payrollIds = $query->pluck('payroll_id')->toArray();
        } else {
            $request->validate([
                'payroll_ids' => 'required|array',
                'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            ]);

            // Only payroll records with 'Pending Review' status can be sent for review
            $updated = PayrollRecord::whereIn('payroll_id', $request->payroll_ids)
                                    ->where('status', 'Pending Review')
                                    ->update([
                                        'status' => 'Under Review',
                                        'updated_at' => now()
                                    ]);

            $payrollIds = $request->payroll_ids;
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_sent_for_review',
            'description' => "Bulk sent {$updated} payroll records for review",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'payroll_ids' => $payrollIds, 'updated_count' => $updated]),
        ]);

        return redirect()->back()
            ->with('success', "Successfully sent {$updated} payroll records for review.");
    }

    // Bulk mark all payroll records as reviewed
    public function bulkMarkAsReviewed(Request $request)
    {
        if ($request->has('select_all_pages') && $request->input('select_all_pages') == '1') {
            // Apply the same filters used in the index method to get all matching records
            $query = PayrollRecord::query();

            // Apply filters similar to index method
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('month_filter')) {
                $monthFilter = $request->month_filter . '-01';
                $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                      ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
            }

            // Only payroll records with 'Under Review' status can be marked as reviewed
            $query->where('status', 'Under Review');

            $updated = $query->update([
                'status' => 'Reviewed',
                'updated_at' => now()
            ]);

            $payrollIds = $query->pluck('payroll_id')->toArray();
        } else {
            $request->validate([
                'payroll_ids' => 'required|array',
                'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            ]);

            // Only payroll records with 'Under Review' status can be marked as reviewed
            $updated = PayrollRecord::whereIn('payroll_id', $request->payroll_ids)
                                    ->where('status', 'Under Review')
                                    ->update([
                                        'status' => 'Reviewed',
                                        'updated_at' => now()
                                    ]);

            $payrollIds = $request->payroll_ids;
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_marked_as_reviewed',
            'description' => "Bulk marked {$updated} payroll records as reviewed",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'payroll_ids' => $payrollIds, 'updated_count' => $updated]),
        ]);

        return redirect()->back()
            ->with('success', "Successfully marked {$updated} payroll records as reviewed.");
    }

    // Bulk send all payroll records for final approval
    public function bulkSendForApproval(Request $request)
    {
        if ($request->has('select_all_pages') && $request->input('select_all_pages') == '1') {
            // Apply the same filters used in the index method to get all matching records
            $query = PayrollRecord::query();

            // Apply filters similar to index method
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('month_filter')) {
                $monthFilter = $request->month_filter . '-01';
                $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                      ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
            }

            // Only payroll records with 'Reviewed' status can be sent for final approval
            $query->where('status', 'Reviewed');

            $updated = $query->update([
                'status' => 'Pending Final Approval',
                'updated_at' => now()
            ]);

            $payrollIds = $query->pluck('payroll_id')->toArray();
        } else {
            $request->validate([
                'payroll_ids' => 'required|array',
                'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            ]);

            // Only payroll records with 'Reviewed' status can be sent for final approval
            $updated = PayrollRecord::whereIn('payroll_id', $request->payroll_ids)
                                    ->where('status', 'Reviewed')
                                    ->update([
                                        'status' => 'Pending Final Approval',
                                        'updated_at' => now()
                                    ]);

            $payrollIds = $request->payroll_ids;
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_sent_for_final_approval',
            'description' => "Bulk sent {$updated} payroll records for final approval",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'payroll_ids' => $payrollIds, 'updated_count' => $updated]),
        ]);

        return redirect()->back()
            ->with('success', "Successfully sent {$updated} payroll records for final approval.");
    }

    // Bulk final approve all payroll records
    public function bulkFinalApprove(Request $request)
    {
        if ($request->has('select_all_pages') && $request->input('select_all_pages') == '1') {
            // Apply the same filters used in the index method to get all matching records
            $query = PayrollRecord::query();

            // Apply filters similar to index method
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('month_filter')) {
                $monthFilter = $request->month_filter . '-01';
                $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                      ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
            }

            // Only payroll records with 'Pending Final Approval' status can be finally approved
            $query->where('status', 'Pending Final Approval');

            // FIX: Fetch IDs BEFORE update, otherwise query returns empty
            $payrollIds = $query->pluck('payroll_id')->toArray();

            $updated = $query->update([
                'status' => 'Approved',
                'updated_at' => now()
            ]);
        } else {
            $request->validate([
                'payroll_ids' => 'required|array',
                'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            ]);

            $payrollIds = $request->payroll_ids;
            
            // Only payroll records with 'Pending Final Approval' status can be finally approved
             $updated = PayrollRecord::whereIn('payroll_id', $payrollIds)
                                    ->where('status', 'Pending Final Approval')
                                    ->update([
                                        'status' => 'Approved',
                                        'updated_at' => now()
                                    ]);
        }

        // --- Gratuity Paid Status Update Logic ---
        \Illuminate\Support\Facades\Log::info("Bulk Final Approve: checking IDs: " . implode(',', $payrollIds));

        // Fetch approved records that are for Gratuity
        $gratuityRecords = PayrollRecord::whereIn('payroll_id', $payrollIds)
            ->where('payment_type', 'Gratuity')
            ->get();

        \Illuminate\Support\Facades\Log::info("Bulk Final Approve: Found " . $gratuityRecords->count() . " Gratuity records.");

        foreach ($gratuityRecords as $record) {
            $pensioner = \App\Models\Pensioner::where('employee_id', $record->employee_id)->first();
            
            \Illuminate\Support\Facades\Log::info("Processing Record {$record->payroll_id}: Employee {$record->employee_id}, Pensioner found? " . ($pensioner ? 'Yes' : 'No'));

            if ($pensioner && !$pensioner->is_gratuity_paid) {
                $pensioner->update([
                    'is_gratuity_paid' => true,
                    'gratuity_paid_date' => now()
                ]);
                \Illuminate\Support\Facades\Log::info("Auto-marked Gratuity as PAID for Pensioner ID: {$pensioner->id} (Employee: {$pensioner->employee_id}) upon Final Approval.");
            }
        }
        // ----------------------------------------

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_final_approved_payroll',
            'description' => "Bulk finally approved {$updated} payroll records",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'payroll_ids' => $payrollIds, 'updated_count' => $updated]),
        ]);

        return redirect()->back()
            ->with('success', "Successfully finally approved {$updated} payroll records.");
    }

    // Individual send payroll for review
    public function sendForReview($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);

        // Only payroll records with 'Pending Review' status can be sent for review
        if ($payroll->status !== 'Pending Review') {
            return redirect()->back()
                ->with('error', 'Payroll record is not in Pending Review status.');
        }

        $payroll->update([
            'status' => 'Under Review',
            'updated_at' => now()
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'sent_payroll_for_review',
            'description' => "Sent payroll record ID: {$payrollId} for review",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        return redirect()->back()
            ->with('success', 'Payroll record sent for review successfully.');
    }

    // Individual mark payroll as reviewed
    public function markAsReviewed($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);

        // Only payroll records with 'Under Review' status can be marked as reviewed
        if ($payroll->status !== 'Under Review') {
            return redirect()->back()
                ->with('error', 'Payroll record is not in Under Review status.');
        }

        $payroll->update([
            'status' => 'Reviewed',
            'updated_at' => now()
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'marked_payroll_as_reviewed',
            'description' => "Marked payroll record ID: {$payrollId} as reviewed",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        return redirect()->back()
            ->with('success', 'Payroll record marked as reviewed successfully.');
    }

    // Individual send payroll for approval
    public function sendForApproval($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);

        // Only payroll records with 'Reviewed' status can be sent for final approval
        if ($payroll->status !== 'Reviewed') {
            return redirect()->back()
                ->with('error', 'Payroll record is not in Reviewed status.');
        }

        $payroll->update([
            'status' => 'Pending Final Approval',
            'updated_at' => now()
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'sent_payroll_for_final_approval',
            'description' => "Sent payroll record ID: {$payrollId} for final approval",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        return redirect()->back()
            ->with('success', 'Payroll record sent for final approval successfully.');
    }

    // Individual final approve payroll
    public function finalApprove($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);

        // Only payroll records with 'Pending Final Approval' status can be finally approved
        if ($payroll->status !== 'Pending Final Approval') {
            return redirect()->back()
                ->with('error', 'Payroll record is not in Pending Final Approval status.');
        }

        $payroll->update([
            'status' => 'Approved',
            'updated_at' => now()
        ]);

        // --- Gratuity Paid Status Update Logic ---
        if ($payroll->payment_type === 'Gratuity') {
             $pensioner = \App\Models\Pensioner::where('employee_id', $payroll->employee_id)->first();
             if ($pensioner && !$pensioner->is_gratuity_paid) {
                 $pensioner->update([
                     'is_gratuity_paid' => true,
                     'gratuity_paid_date' => now()
                 ]);
                 \Illuminate\Support\Facades\Log::info("Auto-marked Gratuity as PAID for Pensioner ID: {$pensioner->id} (Employee: {$pensioner->employee_id}) upon Single Final Approval.");
             }
        }
        // ----------------------------------------

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'finally_approved_payroll',
            'description' => "Finally approved payroll record ID: {$payrollId}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        return redirect()->back()
            ->with('success', 'Payroll record finally approved successfully.');
    }




    // Delete entire payroll for a specific month
    public function destroyMonth(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $monthStart = \Carbon\Carbon::parse($request->month . '-01')->startOfMonth();
        $monthEnd = \Carbon\Carbon::parse($request->month . '-01')->endOfMonth();

        // 1. Safety Check: Are there any Approved or Paid records?
        $lockedRecords = PayrollRecord::whereBetween('payroll_month', [$monthStart, $monthEnd])
            ->whereIn('status', ['Approved', 'Paid'])
            ->exists();

        if ($lockedRecords) {
            return redirect()->back()->with('error', 'Cannot delete payroll for this month. Some records have already been Final Approved or Paid.');
        }

        // 2. Cascade Delete: Delete OneTime AND Monthly Additions/Deductions starting in this month
        // We delete 'Monthly' items starting this month because users often use Bulk Tools to create them for the current payroll.
        // If we don't delete them, re-running the Bulk Tool would create duplicates.
        // Items starting in previous months (historical) will NOT be touched because of the start_date check.
        
        // First, get all additions that will be deleted to check for associated loans
        $additionsToDelete = \App\Models\Addition::whereBetween('start_date', [$monthStart, $monthEnd])
            ->whereIn('addition_period', ['OneTime', 'Monthly'])
            ->get();
        
        // Delete loans that were created from these additions
        $deletedLoans = 0;
        $deletedLoanDeductions = 0;
        foreach ($additionsToDelete as $addition) {
            // Find loans associated with this addition by matching employee_id and creation date
            $loans = \App\Models\Loan::where('employee_id', $addition->employee_id)
                ->whereDate('created_at', '>=', $monthStart)
                ->whereDate('created_at', '<=', $monthEnd)
                ->get();
            
            foreach ($loans as $loan) {
                // Delete the loan's deduction record first
                $loanDeduction = \App\Models\Deduction::where('loan_id', $loan->loan_id)->first();
                if ($loanDeduction) {
                    $loanDeduction->delete();
                    $deletedLoanDeductions++;
                }
                
                // Delete any loan deduction history
                \App\Models\LoanDeduction::where('loan_id', $loan->loan_id)->delete();
                
                // Delete the loan itself
                $loan->delete();
                $deletedLoans++;
            }
        }
        
        // Now delete the additions
        $deletedAdditions = \App\Models\Addition::whereBetween('start_date', [$monthStart, $monthEnd])
            ->whereIn('addition_period', ['OneTime', 'Monthly'])
            ->delete();

        $deletedDeductions = \App\Models\Deduction::whereBetween('start_date', [$monthStart, $monthEnd])
            ->whereIn('deduction_period', ['OneTime', 'Monthly'])
            ->whereNull('loan_id') // Protect loan deductions!
            ->delete();
            
        // 3. Revert Loan Balances and Delete Loan History
        // We must revert the balance for ALL loans that had a deduction in this month.
        // Whether the loan started this month or years ago, we just undo the transaction.
        $loanDeductions = \App\Models\LoanDeduction::where('payroll_month', $request->month)->get();
        
        foreach ($loanDeductions as $loanDeduction) {
            $loan = \App\Models\Loan::find($loanDeduction->loan_id);
            if ($loan) {
                // Revert balance and repaid amount
                $loan->remaining_balance += $loanDeduction->amount_deducted;
                $loan->total_repaid -= $loanDeduction->amount_deducted;
                
                // Reset status to active if it was completed
                if ($loan->status === 'completed') {
                    $loan->status = 'active';
                    $loan->end_date = null;
                }
                
                // Increment remaining months since we are effectively canceling a month's payment
                $loan->remaining_months += 1; 

                $loan->save();
            }
            // Delete the history record so it can be re-processed
            $loanDeduction->delete();
        }

        // 4. Delete Payroll Records
        $recordsToDelete = PayrollRecord::whereBetween('payroll_month', [$monthStart, $monthEnd]);
        $count = $recordsToDelete->count();
        
        $payrollIds = $recordsToDelete->pluck('payroll_id');
        \App\Models\PaymentTransaction::whereIn('payroll_id', $payrollIds)->delete();
        
        $recordsToDelete->delete();

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'deleted_monthly_payroll',
            'description' => "Deleted entire payroll batch for {$request->month}. Removed {$count} records, {$deletedAdditions} additions, {$deletedDeductions} deductions, {$deletedLoans} loans, and {$deletedLoanDeductions} loan deductions.",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Payroll', 'month' => $request->month, 'records_count' => $count, 'loans_deleted' => $deletedLoans]),
        ]);

        return redirect()->route('payroll.index')->with('success', "Payroll for {$request->month} deleted successfully. ({$count} records removed)");
    }

    // Bulk request deletion of payroll records
    public function bulkRequestDelete(Request $request)
    {
        if ($request->has('select_all_pages') && $request->input('select_all_pages') == '1') {
            
            // Safety Check: Require month filter for bulk deletion request
            if (!$request->filled('month_filter')) {
                return redirect()->back()->with('error', 'Action Aborted: You must select a specific month filter to perform a bulk delete of all pages.');
            }

            $query = PayrollRecord::query();

            // Apply filters similar to index method
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('month_filter')) {
                $monthFilter = $request->month_filter . '-01';
                $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                      ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
            }

            // Can only request delete for specific statuses if needed, or allow all except already deleted (which aren't here)
            // For now, let's allow requesting delete for anything that isn't already 'Pending Deletion'
            $query->where('status', '!=', 'Pending Deletion');

            $updated = $query->update([
                'status' => 'Pending Deletion',
                'updated_at' => now()
            ]);
            
            $payrollIds = $query->pluck('payroll_id')->toArray();

        } else {
            $request->validate([
                'payroll_ids' => 'required|array',
                'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            ]);

            // Safety Check: Require month filter for bulk deletion request if checking multiple items manually, 
            // though client-side check should catch it. Let's enforce it here too for consistency if desired.
            if (!$request->filled('month_filter')) {
                 return redirect()->back()->with('error', 'Action Aborted: You must select a specific month filter to perform a bulk delete.');
            }

            $updated = PayrollRecord::whereIn('payroll_id', $request->payroll_ids)
                                    ->where('status', '!=', 'Pending Deletion')
                                    ->update([
                                        'status' => 'Pending Deletion',
                                        'updated_at' => now()
                                    ]);
            $payrollIds = $request->payroll_ids;
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_requested_delete_payroll',
            'description' => "Bulk requested deletion for {$updated} payroll records",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'payroll_ids' => $payrollIds ?? [], 'updated_count' => $updated]),
        ]);

        return redirect()->back()
            ->with('success', "Successfully requested deletion for {$updated} payroll records.");
    }

    // Bulk approve deletion of payroll records (Permanent Delete)
    public function bulkApproveDelete(Request $request)
    {
        if ($request->has('select_all_pages') && $request->input('select_all_pages') == '1') {
            $query = PayrollRecord::query();

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('month_filter')) {
                $monthFilter = $request->month_filter . '-01';
                $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                      ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
            }

            // Only 'Pending Deletion' records can be permanently deleted
            $query->where('status', 'Pending Deletion');
            
            // Get IDs for cascading delete
            $payrollIds = $query->pluck('payroll_id')->toArray();
            
            // Delete related records first
            if (!empty($payrollIds)) {
                \App\Models\PaymentTransaction::whereIn('payroll_id', $payrollIds)->delete();
            }

            $count = $query->count();
            $query->delete(); 
            
        } else {
            $request->validate([
                'payroll_ids' => 'required|array',
                'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            ]);

            $payrollIds = $request->payroll_ids;
            
            // Delete related records first for selected items
            \App\Models\PaymentTransaction::whereIn('payroll_id', $payrollIds)->delete();

            $count = PayrollRecord::whereIn('payroll_id', $payrollIds)
                                  ->where('status', 'Pending Deletion')
                                  ->delete();
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_approved_delete_payroll',
            'description' => "Bulk permanently deleted {$count} payroll records",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'deleted_count' => $count]),
        ]);

        return redirect()->back()
            ->with('success', "Successfully deleted {$count} payroll records.");
    }

}


