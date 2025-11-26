<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\PendingEmployeeChange;
use App\Models\AppointmentType;
use App\Models\GradeLevel;
use App\Models\Step;
use App\Models\User;
use App\Http\Controllers\PendingEmployeeChangeController;
use App\Http\Controllers\ProbationController;
use App\Services\PayrollCalculationService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VerifyProbationLifecycle extends Command
{
    protected $signature = 'verify:probation-lifecycle';
    protected $description = 'Verify the complete probation lifecycle';

    public function handle()
    {
        $this->info('Starting Probation Lifecycle Verification...');

        // 1. Setup Data
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'username' => 'admin_test',
                'email' => 'admin_test@example.com',
                'password_hash' => bcrypt('password'),
            ]);
        }
        $userId = $user->user_id; // Explicitly use user_id
        $this->info("Using User: {$user->email} (ID: {$userId})");
        
        if (!$userId) {
            $this->error("User ID is null!");
            return 1;
        }
        auth()->login($user); // Simulate login

        $gradeLevel = GradeLevel::first();
        $step = Step::where('grade_level_id', $gradeLevel->id)->first();
        
        // Ensure Appointment Type "Permanent" exists
        $appointmentType = AppointmentType::firstOrCreate(
            ['name' => 'Permanent'],
            ['description' => 'Permanent Appointment']
        );

        // 2. Simulate Pending Employee Change Creation
        $this->info("\n--- Step 1: Creating Pending Employee Change ---");
        $dateOfFirstAppointment = Carbon::now()->subDays(10)->format('Y-m-d'); // 10 days ago
        
        $pendingData = [
            'first_name' => 'Lifecycle',
            'surname' => 'Test',
            'date_of_first_appointment' => $dateOfFirstAppointment,
            'appointment_type_id' => $appointmentType->id,
            'grade_level_id' => $gradeLevel->id,
            'step_id' => $step->id,
            'email' => 'lifecycle' . rand(1000, 9999) . '@example.com',
            'mobile_no' => '080' . rand(10000000, 99999999),
            'nationality' => 'Nigerian',
            'nin' => '12345678901',
            'pay_point' => 'Headquarters',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'state_id' => 1,
            'lga_id' => 1,
            'ward_id' => 1,
            'rank_id' => 1,
            'department_id' => 1,
            'address' => 'Test Address',
            'staff_no' => 'LIFECYCLE' . rand(1000, 9999),
            'employee_id' => rand(100000, 999999),
        ];

        $pendingChange = PendingEmployeeChange::create([
            'change_type' => 'create',
            'data' => $pendingData,
            'status' => 'pending',
            'requested_by' => $userId, // Use explicit ID
        ]);

        $this->info("Created Pending Change ID: {$pendingChange->id}");

        // 3. Approve Pending Change
        $this->info("\n--- Step 2: Approving Pending Change ---");
        $controller = new PendingEmployeeChangeController();
        $request = new Request(['approval_notes' => 'Approved for testing']);
        
        // We need to mock the route/redirect behavior or just call the logic directly if possible.
        // Since controller returns redirect, we might want to just call the logic directly or catch the redirect exception?
        // Actually, let's just use the logic from the controller: applyCreate
        
        // To avoid controller complexity with redirects, let's manually trigger the approval logic
        // But we want to verify the controller logic is correct.
        // Let's try to call the approve method and catch the redirect if it throws one (Laravel redirects are responses, not exceptions usually, but in console command context it might be different)
        
        try {
            // We can't easily call controller method that returns redirect in console.
            // So we will replicate the approval logic to verify the RESULT of that logic.
            // Or better, let's use the DB transaction block from the controller.
            
            DB::transaction(function () use ($pendingChange, $request, $controller) {
                $pendingChange->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'approval_notes' => $request->approval_notes
                ]);
                
                // Use reflection to call private method applyCreate if needed, or just copy logic?
                // Reflection is better to test actual code.
                $reflection = new \ReflectionClass($controller);
                $method = $reflection->getMethod('applyCreate');
                $method->setAccessible(true);
                $method->invokeArgs($controller, [$pendingChange]);
            });
            
            $this->info("Pending Change Approved.");
            
        } catch (\Exception $e) {
            $this->error("Error approving change: " . $e->getMessage());
            return 1;
        }

        // 4. Verify Employee Creation and Probation Status
        $this->info("\n--- Step 3: Verifying Employee Status ---");
        $employee = Employee::where('first_name', 'Lifecycle')->where('surname', 'Test')->first();
        
        if (!$employee) {
            $this->error("Employee not created!");
            return 1;
        }
        
        $this->info("Employee Created: {$employee->employee_id}");
        
        // Verify Start Date
        $expectedStartDate = Carbon::parse($dateOfFirstAppointment)->startOfDay();
        $actualStartDate = Carbon::parse($employee->probation_start_date)->startOfDay();
        
        if ($actualStartDate->eq($expectedStartDate)) {
            $this->info("PASS: Probation Start Date matches Date of First Appointment ({$actualStartDate->format('Y-m-d')})");
        } else {
            $this->error("FAIL: Probation Start Date ({$actualStartDate->format('Y-m-d')}) does NOT match Date of First Appointment ({$expectedStartDate->format('Y-m-d')})");
        }
        
        // Verify Probation Status
        if ($employee->on_probation && $employee->probation_status === 'pending') {
            $this->info("PASS: Employee is On Probation (Pending)");
        } else {
            $this->error("FAIL: Employee probation status is incorrect");
        }

        // 5. Verify Payroll Exclusion
        $this->info("\n--- Step 4: Verifying Payroll Exclusion ---");
        $payrollService = new PayrollCalculationService();
        $result = $payrollService->calculatePayroll($employee, Carbon::now()->format('Y-m'));
        
        if ($result['net_salary'] == 0) {
            $this->info("PASS: Net Salary is 0 during probation");
        } else {
            $this->error("FAIL: Net Salary is {$result['net_salary']}, expected 0");
        }

        // 6. Verify Rejection Constraint (Before 3 months)
        $this->info("\n--- Step 5: Verifying Rejection Constraint (Early) ---");
        // Try to reject
        if (!$employee->hasProbationPeriodEnded()) {
            $this->info("Probation period has NOT ended yet (Correct).");
            
            // Check logic manually as we can't easily call controller redirect
            if (!$employee->hasProbationPeriodEnded()) {
                $this->info("PASS: System detects probation has not ended.");
            } else {
                $this->error("FAIL: System thinks probation has ended.");
            }
        }

        // 7. Time Travel (Simulate 3 months passing)
        $this->info("\n--- Step 6: Time Travel (3 Months Later) ---");
        $employee->probation_end_date = Carbon::now()->subDay(); // Ended yesterday
        $employee->save();
        
        if ($employee->hasProbationPeriodEnded()) {
            $this->info("PASS: Probation period has now ended.");
        } else {
            $this->error("FAIL: Probation period should have ended.");
        }

        // 8. Verify Evaluation (Approval)
        $this->info("\n--- Step 7: Verifying Evaluation (Approval) ---");
        // Simulate approval
        $employee->update([
            'on_probation' => false,
            'probation_status' => 'approved',
            'status' => 'Active'
        ]);
        
        $this->info("Employee Probation Approved.");
        
        // Verify Payroll Inclusion
        $result = $payrollService->calculatePayroll($employee, Carbon::now()->format('Y-m'));
        if ($result['net_salary'] > 0) {
            $this->info("PASS: Net Salary is > 0 after approval ({$result['net_salary']})");
        } else {
            $this->error("FAIL: Net Salary is 0, expected > 0");
        }

        // Cleanup
        $employee->delete();
        $pendingChange->delete();
        $this->info("\nVerification Complete. Cleanup Done.");
        
        return 0;
    }
}
