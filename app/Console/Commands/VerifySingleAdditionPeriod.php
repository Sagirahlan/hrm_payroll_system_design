<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\User;
use App\Models\AdditionType;
use App\Models\Addition;
use Illuminate\Http\Request;
use App\Http\Controllers\PayrollController;
use App\Services\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class VerifySingleAdditionPeriod extends Command
{
    protected $signature = 'verify:single-addition-period';
    protected $description = 'Verify single addition period logic';

    public function handle()
    {
        $this->info('Starting Single Addition Period Verification...');

        // 1. Setup Data
        $user = User::first();
        if (!$user) {
             $user = User::create([
                'username' => 'admin_test_single',
                'email' => 'admin_single@example.com',
                'password_hash' => bcrypt('password'),
            ]);
        }
        auth()->login($user);

        // Create Employee
        $employee = new Employee();
        $employee->first_name = 'Single';
        $employee->surname = 'Addition';
        $employee->employee_id = rand(100000, 999999);
        $employee->staff_no = 'SA' . rand(1000, 9999);
        $employee->status = 'Active';
        $employee->grade_level_id = 1;
        $employee->step_id = 1;
        $employee->email = 'sa' . rand(1000, 9999) . '@example.com';
        $employee->mobile_no = '080' . rand(10000000, 99999999);
        $employee->nationality = 'Nigerian';
        $employee->nin = '12345678901';
        $employee->pay_point = 'Headquarters';
        $employee->gender = 'Male';
        $employee->date_of_birth = '1990-01-01';
        $employee->date_of_first_appointment = Carbon::now()->format('Y-m-d');
        $employee->state_id = 1;
        $employee->lga_id = 1;
        $employee->ward_id = 1;
        $employee->rank_id = 1;
        $employee->department_id = 1;
        $employee->address = 'Test Address';
        $employee->appointment_type_id = 1;
        $employee->save();
        $this->info("Created Employee: {$employee->employee_id}");

        // Create Non-Statutory Addition Type
        $additionType = AdditionType::create([
            'name' => 'Test Single Addition',
            'code' => 'TSA' . rand(100, 999),
            'is_statutory' => false,
        ]);
        $this->info("Created Addition Type: {$additionType->name}");

        $controller = new PayrollController(new PayrollCalculationService());

        // 2. Test Case 1: OneTime Addition (Should ignore end_date)
        $this->info("\nTest Case 1: OneTime Addition");
        $request = new Request([
            'addition_type_id' => $additionType->id,
            'amount_type' => 'fixed',
            'amount' => 5000,
            'period' => 'OneTime',
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-30', // Should be ignored/nulled
        ]);

        try {
            $controller->storeAddition($request, $employee->employee_id);
            $this->info("Store method called for OneTime.");
            
            $addition = Addition::where('employee_id', $employee->employee_id)
                ->where('addition_type_id', $additionType->id)
                ->where('addition_period', 'OneTime')
                ->latest()
                ->first();
            
            if ($addition) {
                if ($addition->end_date === null) {
                    $this->info("PASS: OneTime addition has NULL end_date.");
                } else {
                    $this->error("FAIL: OneTime addition has end_date: {$addition->end_date}");
                }
            } else {
                $this->error("FAIL: OneTime addition not created.");
            }

        } catch (\Exception $e) {
             if (!str_contains($e->getMessage(), 'Redirect')) {
                 $this->error("Exception in OneTime test: " . $e->getMessage());
             } else {
                 // Redirect means success usually, check DB
                 $addition = Addition::where('employee_id', $employee->employee_id)
                    ->where('addition_type_id', $additionType->id)
                    ->where('addition_period', 'OneTime')
                    ->latest()
                    ->first();
                if ($addition && $addition->end_date === null) {
                    $this->info("PASS: OneTime addition has NULL end_date (verified after redirect).");
                }
             }
        }

        // 3. Test Case 2: Monthly Addition WITHOUT End Date (Should Fail Validation)
        $this->info("\nTest Case 2: Monthly Addition WITHOUT End Date");
        $request = new Request([
            'addition_type_id' => $additionType->id,
            'amount_type' => 'fixed',
            'amount' => 5000,
            'period' => 'Monthly',
            'start_date' => '2025-07-01',
            // Missing end_date
        ]);

        try {
            $controller->storeAddition($request, $employee->employee_id);
            $this->error("FAIL: Monthly addition succeeded without end_date!");
        } catch (ValidationException $e) {
            $this->info("PASS: Validation failed as expected (End Date required for Monthly).");
        } catch (\Exception $e) {
            $this->info("Exception caught: " . $e->getMessage());
        }

        // 4. Test Case 3: Monthly Addition WITH End Date (Should Succeed)
        $this->info("\nTest Case 3: Monthly Addition WITH End Date");
        $request = new Request([
            'addition_type_id' => $additionType->id,
            'amount_type' => 'fixed',
            'amount' => 5000,
            'period' => 'Monthly',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
        ]);

        try {
            $controller->storeAddition($request, $employee->employee_id);
            
            $addition = Addition::where('employee_id', $employee->employee_id)
                ->where('addition_type_id', $additionType->id)
                ->where('addition_period', 'Monthly')
                ->latest()
                ->first();
            
            if ($addition) {
                $actualEnd = Carbon::parse($addition->end_date)->format('Y-m-d');
                if ($actualEnd == '2025-12-31') {
                    $this->info("PASS: Monthly addition created with correct end_date.");
                } else {
                    $this->error("FAIL: Monthly addition end_date mismatch. Got: {$actualEnd}");
                }
            } else {
                // Check if redirect happened
                 $this->info("Checking DB for Monthly addition...");
            }

        } catch (\Exception $e) {
             if (!str_contains($e->getMessage(), 'Redirect')) {
                 $this->error("Exception in Monthly test: " . $e->getMessage());
             } else {
                  $addition = Addition::where('employee_id', $employee->employee_id)
                    ->where('addition_type_id', $additionType->id)
                    ->where('addition_period', 'Monthly')
                    ->latest()
                    ->first();
                    
                  if ($addition && $addition->end_date == '2025-12-31') {
                    $this->info("PASS: Monthly addition created with correct end_date (verified after redirect).");
                  }
             }
        }

        // Cleanup
        Addition::where('employee_id', $employee->employee_id)->delete();
        $additionType->delete();
        $employee->delete();
        $this->info("\nCleanup Done.");
        
        return 0;
    }
}
