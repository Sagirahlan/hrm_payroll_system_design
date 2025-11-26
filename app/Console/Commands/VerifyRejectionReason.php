<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ProbationController;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class VerifyRejectionReason extends Command
{
    protected $signature = 'verify:rejection-reason';
    protected $description = 'Verify that rejection requires a reason';

    public function handle()
    {
        $this->info('Starting Rejection Reason Verification...');

        // 1. Setup Data
        $user = User::first();
        if (!$user) {
             $user = User::create([
                'username' => 'admin_test_reason',
                'email' => 'admin_reason@example.com',
                'password_hash' => bcrypt('password'),
            ]);
        }
        auth()->login($user);

        // Create Employee
        $employee = new Employee();
        $employee->first_name = 'Reason';
        $employee->surname = 'Test';
        $employee->employee_id = rand(100000, 999999);
        $employee->staff_no = 'REASON' . rand(1000, 9999);
        $employee->on_probation = true;
        $employee->probation_start_date = Carbon::now();
        $employee->probation_end_date = Carbon::now()->addMonths(3);
        $employee->probation_status = 'pending';
        $employee->status = 'On Probation';
        
        // Fill required fields
        $employee->grade_level_id = 1;
        $employee->step_id = 1;
        $employee->email = 'reason' . rand(1000, 9999) . '@example.com';
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

        $controller = new ProbationController();

        // 2. Test Case 1: Reject WITHOUT reason (Should Fail)
        $this->info("\nTest Case 1: Reject WITHOUT reason");
        $request = new Request(); // Empty request
        
        try {
            $controller->reject($request, $employee);
            $this->error("FAIL: Rejection succeeded without reason!");
        } catch (ValidationException $e) {
            $this->info("PASS: Validation failed as expected (Reason required).");
            $errors = $e->errors();
            if (isset($errors['probation_notes'])) {
                $this->info("Validation Error: " . $errors['probation_notes'][0]);
            }
        } catch (\Exception $e) {
            $this->error("FAIL: Unexpected exception: " . $e->getMessage());
        }

        // 3. Test Case 2: Reject WITH reason (Should Succeed)
        $this->info("\nTest Case 2: Reject WITH reason");
        $reason = "Performance was below expectations.";
        $request = new Request(['probation_notes' => $reason]);
        
        try {
            // We expect a redirect, so we might need to catch that or just check DB
            // In console, redirect might throw exception or return RedirectResponse
            $response = $controller->reject($request, $employee);
            
            $employee->refresh();
            
            if ($employee->probation_status === 'rejected') {
                $this->info("PASS: Employee rejected successfully.");
                
                // Check if reason is saved
                if (str_contains($employee->probation_notes, $reason)) {
                    $this->info("PASS: Rejection reason saved correctly.");
                    $this->info("Notes: " . $employee->probation_notes);
                } else {
                    $this->error("FAIL: Rejection reason NOT found in notes.");
                    $this->info("Actual Notes: " . $employee->probation_notes);
                }
            } else {
                $this->error("FAIL: Employee status is {$employee->probation_status}");
            }
            
        } catch (\Exception $e) {
            // Redirects in tests/console often throw exceptions if not handled, but let's see
            $this->info("Exception during success test (might be redirect): " . $e->getMessage());
            $employee->refresh();
             if ($employee->probation_status === 'rejected') {
                $this->info("PASS: Employee rejected (verified via DB).");
             }
        }

        // Cleanup
        $employee->delete();
        $this->info("\nCleanup Done.");
        
        return 0;
    }
}
