<?php

namespace App\Http\Controllers;

use App\Models\Pensioner;
use App\Models\PendingPensionerChange;
use App\Models\Employee;
use App\Models\Retirement;
use App\Models\ComputeBeneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Imports\PensionersImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon; // Added Carbon import

class PensionerController extends Controller
{
    protected $calculationService;

    public function __construct(\App\Services\PensionCalculationService $calculationService)
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_pensioners'], ['only' => ['index', 'show']]);
        $this->middleware(['permission:manage_pensioners'], ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
        $this->middleware(['permission:approve_pensioner_changes'], ['only' => ['approvePendingChange', 'rejectPendingChange']]);
        $this->calculationService = $calculationService;
    }

    /**
     * Display a listing of the pensioners.
     */
    public function index(Request $request)
    {
        $query = Pensioner::with(['department', 'rank', 'gradeLevel', 'bank', 'retirement', 'employee']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('employee_id', 'like', "%{$searchTerm}%")
                  ->orWhere('full_name', 'like', "%{$searchTerm}%")
                  ->orWhere('surname', 'like', "%{$searchTerm}%")
                  ->orWhere('first_name', 'like', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply retirement type filter
        if ($request->filled('retirement_type')) {
            $query->where('retirement_type', $request->retirement_type);
        }

        $pensioners = $query->orderBy('surname', 'asc')->paginate(10);

        return view('pensioners.index', compact('pensioners'));
    }

    /**
     * Show the form for creating a new pensioner.
     */
    public function create()
    {
        $retirements = Retirement::with('employee')->whereDoesntHave('pensioner')->get();
        $pensioners = Pensioner::all();
        
        return view('pensioners.create', compact('retirements', 'pensioners'));
    }

    /**
     * Store a newly created pensioner in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'retirement_id' => 'required|exists:retirements,id|unique:pensioners,retirement_id',
                'pension_amount' => 'required|numeric|min:0',
                'gratuity_amount' => 'required|numeric|min:0',
                'bank_id' => 'nullable|exists:bank_list,id',
                'account_number' => 'nullable|string|max:255',
                'account_name' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();

            // Get the retirement record with employee details
            $retirement = Retirement::with('employee.department', 'employee.rank', 'employee.step', 'employee.gradeLevel', 'employee.salaryScale')->findOrFail($validated['retirement_id']);
            $employee = $retirement->employee;

            // Get the beneficiary computation record if it exists
            $beneficiaryComputation = ComputeBeneficiary::where('id_no', $employee->employee_id)->orWhere('id_no', $employee->staff_id)->first();

            $pensioner = Pensioner::create([
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->full_name,
                'surname' => $employee->surname,
                'first_name' => $employee->first_name,
                'middle_name' => $employee->middle_name,
                'email' => $employee->email,
                'phone_number' => $employee->phone,
                'date_of_birth' => $employee->date_of_birth,
                'place_of_birth' => $employee->place_of_birth,
                'date_of_first_appointment' => $employee->date_of_first_appointment,
                'date_of_retirement' => $retirement->retirement_date,
                'retirement_reason' => $retirement->retire_reason,
                'retirement_type' => 'RB', // Default to Regular Benefits
                'department_id' => $employee->department_id,
                'rank_id' => $employee->rank_id,
                'step_id' => $employee->step_id,
                'grade_level_id' => $employee->grade_level_id,
                'salary_scale_id' => $employee->salary_scale_id,
                'local_gov_area_id' => $employee->lga_id,
                'bank_id' => $validated['bank_id'] ?? $employee->bank_id,
                'account_number' => $validated['account_number'] ?? $employee->account_number,
                'account_name' => $validated['account_name'] ?? $employee->account_name,
                'pension_amount' => $validated['pension_amount'],
                'gratuity_amount' => $validated['gratuity_amount'],
                'total_death_gratuity' => $validated['gratuity_amount'], // Default to gratuity amount
                'years_of_service' => $retirement->years_of_service ?? $this->calculateYearsOfService($employee->date_of_first_appointment, $retirement->retirement_date),
                'pension_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_pension : 0,
                'gratuity_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_gratuity : 0,
                'address' => $employee->address,
                'next_of_kin_name' => $employee->next_of_kin_name,
                'next_of_kin_phone' => $employee->next_of_kin_phone,
                'next_of_kin_address' => $employee->next_of_kin_address,
                'status' => ($retirement->retire_reason === 'Death in Service') ? 'Deceased' : 
                            (($validated['gratuity_amount'] == 0 && $validated['pension_amount'] == 0) ? 'Not Eligible' : 
                            ($validated['status'] ?? 'Active')),
                'retirement_id' => $validated['retirement_id'],
                'beneficiary_computation_id' => $beneficiaryComputation ? $beneficiaryComputation->id : null,
                'created_by' => auth()->id(),
            ]);

            // Update the employee status to 'Pensioner' or mark as fully processed
            $employee->update(['status' => 'Pensioner']);

            DB::commit();

            return redirect()->route('pensioners.index')->with('success', 'Pensioner created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating pensioner: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'An error occurred while creating the pensioner: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified pensioner.
     */
    public function show($id)
    {
        $pensioner = Pensioner::with(['department', 'rank', 'step', 'gradeLevel', 'salaryScale', 'localGovArea', 'bank', 'retirement', 'beneficiaryComputation'])->findOrFail($id);

        // Calculate Overstay
        $dob = \Carbon\Carbon::parse($pensioner->date_of_birth);
        $apptDate = \Carbon\Carbon::parse($pensioner->date_of_first_appointment);
        $retirementDate = \Carbon\Carbon::parse($pensioner->date_of_retirement);

        $ageSpan = $this->calculationService->getDateSpan($dob, $retirementDate);
        $serviceSpan = $this->calculationService->getDateSpan($apptDate, $retirementDate);
        
        $overstayRemark = $this->calculationService->calculateOverstay($ageSpan, $serviceSpan);
        
        // Get overstay data from pensioner record (already calculated with grace period)
        $overstayedDays = $pensioner->overstayed_days ?? 0;
        $overstayAmount = $pensioner->overstayed_deduction_amount ?? 0;
        $expectedRetirementDate = $pensioner->expected_retirement_date;
        
        // Determine grace period status
        $gracePeriodStatus = null;
        $graceWaived = false;
        
        if ($overstayedDays > 0) {
            if ($overstayedDays <= 25) {
                $gracePeriodStatus = "Waived - Within 25-day grace period";
                $graceWaived = true;
            } else {
                $gracePeriodStatus = "Applied - Exceeded 25-day grace period (deduction for all {$overstayedDays} days)";
                $graceWaived = false;
            }
        }

        return view('pensioners.show', compact(
            'pensioner', 
            'overstayRemark', 
            'overstayAmount', 
            'overstayedDays',
            'expectedRetirementDate',
            'gracePeriodStatus',
            'graceWaived'
        ));
    }

    /**
     * Show the form for editing the specified pensioner.
     */
    public function edit($id)
    {
        $pensioner = Pensioner::findOrFail($id);
        $banks = DB::table('bank_list')->where('is_active', 1)->get(['id as id', 'bank_name as name']);

        return view('pensioners.edit', compact('pensioner', 'banks'));
    }

    /**
     * Update the specified pensioner in storage. This will create a pending change that requires approval.
     */
    public function update(Request $request, $id)
    {
        try {
            $pensioner = Pensioner::findOrFail($id);

            $validated = $request->validate([
                'pension_amount' => 'required|numeric|min:0',
                'gratuity_amount' => 'required|numeric|min:0',
                'bank_id' => 'nullable|exists:bank_list,id',
                'account_number' => 'nullable|string|max:255',
                'account_name' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:255',
                'next_of_kin_name' => 'nullable|string|max:255',
                'next_of_kin_phone' => 'nullable|string|max:15',
            ]);

            // Create a pending change record instead of updating directly
            $previousData = [
                'pension_amount' => $pensioner->pension_amount,
                'gratuity_amount' => $pensioner->gratuity_amount,
                'bank_id' => $pensioner->bank_id,
                'account_number' => $pensioner->account_number,
                'account_name' => $pensioner->account_name,
                'status' => $pensioner->status,
                'next_of_kin_name' => $pensioner->next_of_kin_name,
                'next_of_kin_phone' => $pensioner->next_of_kin_phone,
            ];

            PendingPensionerChange::create([
                'pensioner_id' => $pensioner->id,
                'requested_by' => auth()->id(),
                'change_type' => 'update',
                'data' => $validated,
                'previous_data' => $previousData,
                'reason' => $request->reason ?? 'Pensioner information update',
                'status' => 'pending',
            ]);

            return redirect()->route('pensioners.index')->with('success', 'Pensioner update request submitted successfully. Awaiting approval.');
        } catch (\Exception $e) {
            \Log::error('Error creating pending pensioner change: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while submitting the pensioner update request: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified pensioner from storage.
     */
    public function destroy($id)
    {
        try {
            $pensioner = Pensioner::findOrFail($id);
            $pensioner->delete();

            return redirect()->route('pensioners.index')->with('success', 'Pensioner deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Error deleting pensioner: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'An error occurred while deleting the pensioner: ' . $e->getMessage());
        }
    }

    /**
     * Move retired employees to pensioners
     */
    public function moveRetiredToPensioners()
    {
        try {
            // Find all retired employees that don't have pensioner records yet
            $retiredEmployees = Employee::where('status', 'Retired')
                ->whereDoesntHave('pensioner')
                ->get();

            $processedCount = 0;

            foreach ($retiredEmployees as $employee) {
                // Find the corresponding retirement record
                $retirement = Retirement::where('employee_id', $employee->employee_id)->first();

                if (!$retirement) {
                    continue; // Skip if no retirement record
                }

                // Check if pensioner already exists for this retirement
                if (Pensioner::where('retirement_id', $retirement->id)->exists()) {
                    continue; // Skip if pensioner already exists
                }

                // Get the beneficiary computation record if it exists
                $beneficiaryComputation = ComputeBeneficiary::where('id_no', $employee->employee_id)
                    ->orWhere('id_no', $employee->staff_id)->first();

                $status = ($retirement->retire_reason === 'Death in Service') ? 'Deceased' : 
                          ((float)$retirement->gratuity_amount <= 0.001 ? 'Not Eligible' : 
                          'Active');

                \Illuminate\Support\Facades\Log::info("Moving Retired ID {$employee->employee_id}: Gratuity Amount [{$retirement->gratuity_amount}], Float Cast [" . (float)$retirement->gratuity_amount . "], Status assigned: [{$status}]");

                // Create pensioner record
                Pensioner::create([
                    'employee_id' => $employee->employee_id,
                    'full_name' => $employee->full_name,
                    'surname' => $employee->surname,
                    'first_name' => $employee->first_name,
                    'middle_name' => $employee->middle_name,
                    'email' => $employee->email,
                    'phone_number' => $employee->phone,
                    'date_of_birth' => $employee->date_of_birth,
                    'place_of_birth' => $employee->place_of_birth,
                    'date_of_first_appointment' => $employee->date_of_first_appointment,
                    'date_of_retirement' => $retirement->retirement_date,
                    'retirement_reason' => $retirement->retire_reason,
                    'retirement_type' => 'RB', // Default to Regular Benefits
                    'department_id' => $employee->department_id,
                    'rank_id' => $employee->rank_id,
                    'step_id' => $employee->step_id,
                    'grade_level_id' => $employee->grade_level_id,
                    'salary_scale_id' => $employee->salary_scale_id,
                    'local_gov_area_id' => $employee->lga_id,
                    'bank_id' => $employee->bank_id,
                    'account_number' => $employee->account_number,
                    'account_name' => $employee->account_name,
                    'pension_amount' => $retirement->gratuity_amount, // Using gratuity as pension for now
                    'gratuity_amount' => $retirement->gratuity_amount,
                    'total_death_gratuity' => $retirement->gratuity_amount,
                    'years_of_service' => $this->calculateYearsOfService($employee->date_of_first_appointment, $retirement->retirement_date),
                    'pension_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_pension : 0,
                    'gratuity_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_gratuity : 0,
                    'address' => $employee->address,
                    'next_of_kin_name' => $employee->next_of_kin_name,
                    'next_of_kin_phone' => $employee->next_of_kin_phone,
                    'next_of_kin_address' => $employee->next_of_kin_address,
                    'status' => $status,
                    'retirement_id' => $retirement->id,
                    'beneficiary_computation_id' => $beneficiaryComputation ? $beneficiaryComputation->id : null,
                    'created_by' => auth()->id(),
                ]);

                $processedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "{$processedCount} retired employees have been moved to pensioners.",
                'count' => $processedCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error moving retired employees to pensioners: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while moving retired employees to pensioners: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate years of service
     */
    private function calculateYearsOfService($dateOfFirstAppointment, $retirementDate)
    {
        if (!$dateOfFirstAppointment || !$retirementDate) {
            return 0;
        }

        $startDate = \Carbon\Carbon::parse($dateOfFirstAppointment);
        $endDate = \Carbon\Carbon::parse($retirementDate);
        
        return $startDate->diffInYears($endDate);
    }

    /**
     * Get pensioners by retirement type
     */
    public function getPensionersByType($type)
    {
        $pensioners = Pensioner::where('retirement_type', $type)
            ->with(['department', 'rank', 'gradeLevel'])
            ->orderBy('surname', 'asc')
            ->get();

        return response()->json([
            'pensioners' => $pensioners,
            'count' => $pensioners->count()
        ]);
    }

    /**
     * Mark gratuity as paid for a pensioner
     */
    public function markGratuityPaid(Request $request, $id)
    {
        try {
            $pensioner = Pensioner::findOrFail($id);
            
            $pensioner->update([
                'is_gratuity_paid' => true,
                'gratuity_paid_date' => now(),
                'updated_by' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'Gratuity marked as paid successfully.');
        } catch (\Exception $e) {
            \Log::error('Error marking gratuity as paid: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error marking gratuity as paid.');
        }
    }

    /**
     * Mark a pensioner as deceased
     */
    public function markDeceased(Request $request, $id)
    {
        try {
            $pensioner = Pensioner::findOrFail($id);

            $pensioner->update([
                'status' => 'Deceased',
                'retirement_reason' => 'Death in Service',
                'updated_by' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'Pensioner marked as deceased successfully.');
        } catch (\Exception $e) {
            \Log::error('Error marking pensioner as deceased: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error marking pensioner as deceased.');
        }
    }

    /**
     * Approve a pending pensioner change
     */
    public function approvePendingChange(Request $request, $changeId)
    {
        $pendingChange = PendingPensionerChange::findOrFail($changeId);

        // Verify user has permission to approve pensioner changes
        if (!auth()->user()->can('approve_pensioner_changes')) {
            abort(403, 'You do not have permission to approve pensioner changes.');
        }

        DB::transaction(function () use ($pendingChange) {
            // Update the pending change status
            $pendingChange->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            // Apply the changes to the pensioner
            $pensioner = $pendingChange->pensioner;
            $pensioner->update($pendingChange->data);

            // Add audit trail
            \App\Models\AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'approved_pensioner_change',
                'description' => "Approved pensioner change for: " . $pendingChange->pensioner_name .
                    ". Changes: " . $pendingChange->change_description,
                'action_timestamp' => now(),
                'entity_type' => 'PendingPensionerChange',
                'entity_id' => $pendingChange->id,
            ]);
        });

        return redirect()->back()->with('success', 'Pensioner change approved successfully.');
    }

    /**
     * Reject a pending pensioner change
     */
    public function rejectPendingChange(Request $request, $changeId)
    {
        $pendingChange = PendingPensionerChange::findOrFail($changeId);

        // Verify user has permission to approve pensioner changes
        if (!auth()->user()->can('approve_pensioner_changes')) {
            abort(403, 'You do not have permission to approve pensioner changes.');
        }

        $pendingChange->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        // Add audit trail
        \App\Models\AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'rejected_pensioner_change',
            'description' => "Rejected pensioner change for: " . $pendingChange->pensioner_name .
                ". Changes: " . $pendingChange->change_description,
            'action_timestamp' => now(),
            'entity_type' => 'PendingPensionerChange',
            'entity_id' => $pendingChange->id,
        ]);

        return redirect()->back()->with('success', 'Pensioner change rejected.');
    }

    /**
     * Show the legacy pensioner import form
     */
    public function importLegacy()
    {
        return view('pensioners.import_legacy');
    }

    /**
     * Process the legacy pensioner import
     */
    public function processImportLegacy(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        try {
            $import = new PensionersImport;
            Excel::import($import, $request->file('file'));

            $rows = $import->getRowCount();
            $skipped = $import->getSkippedCount();

            $message = "Import processing complete. {$rows} pensioners processed successfully.";
            if ($skipped > 0) {
                $message .= " {$skipped} rows were skipped (employee not found).";
            }

            // Log to Audit Trail
            \App\Models\AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'imported_legacy_pensioners',
                'description' => "Imported legacy pensioners. Processed: {$rows}, Skipped: {$skipped}",
                'action_timestamp' => now(),
                'log_data' => [
                    'rows_processed' => $rows,
                    'rows_skipped' => $skipped,
                    'file_name' => $request->file('file')->getClientOriginalName(),
                    'status_set_to' => 'Retired'
                ]
            ]);

            return redirect()->route('pensioners.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error importing legacy pensioners: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error during import: ' . $e->getMessage());
        }
    }

    /**
     * Download the pre-filled legacy pensioner import template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="legacy_pensioner_template_prefilled.csv"',
        ];

        $columns = [
            'S/N',
            'Staff Number', 
            'First Name', 
            'Middle Name', 
            'Surname', 
            'Department',
            'Retired Grade Level',
            'New Pension', 
            'Bank Name',
            'Bank Code',
            'Account Number',
            'Account Name',
            'Remark'
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            // Fetch Active employees who are eligible for retirement
            $employees = \App\Models\Employee::with(['gradeLevel.salaryScale', 'department', 'bank'])
                ->whereIn('status', ['Active', 'Deceased'])
                ->get();

            $eligibleEmployees = $employees->filter(function ($employee) {
                // Automatically include Deceased employees
                if ($employee->status === 'Deceased') {
                    return true;
                }

                if (!$employee->gradeLevel || !$employee->gradeLevel->salaryScale) {
                    return false;
                }

                $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
                $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;

                // Check if required dates exist before parsing
                if (!$employee->date_of_birth || !$employee->date_of_first_appointment) {
                    return false;
                }

                $age = \Carbon\Carbon::parse($employee->date_of_birth)->age;
                $serviceDuration = \Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now());

                return $age >= $retirementAge || $serviceDuration >= $yearsOfService;
            });
            
            $sn = 1;
            foreach ($eligibleEmployees as $employee) {
                fputcsv($file, [
                    $sn++,
                    $employee->staff_no,
                    $employee->first_name,
                    $employee->middle_name,
                    $employee->surname,
                    $employee->department ? $employee->department->name : '',
                    $employee->gradeLevel ? $employee->gradeLevel->name : '',
                    '0', // New Pension
                    $employee->bank ? $employee->bank->bank_name : '', // Bank Name
                    $employee->bank ? $employee->bank->bank_code : '', // Bank Code
                    $employee->account_number,
                    $employee->account_name,
                    '' // Remark
                ]);
            }
            
            if ($eligibleEmployees->isEmpty()) {
                // Determine why it's empty - maybe just give a generic sample if logic fails
                fputcsv($file, ['1', 'RG20002', 'Maryam', 'Sample', 'Only', 'IT Dept', 'GL 10', '50000', 'GTBank', '058', '1234567890', 'Maryam Sample', 'No Eligible Found']);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}