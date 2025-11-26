<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Services\PayrollCalculationService;
use App\Models\GradeLevel;
use App\Models\Step;
use Carbon\Carbon;

class VerifyProbationPayroll extends Command
{
    protected $signature = 'verify:probation-payroll';
    protected $description = 'Verify that employees on probation get 0 salary';

    public function handle()
    {
        $this->info('Starting Probation Payroll Verification...');

        // 1. Setup Test Data
        $gradeLevel = GradeLevel::first();
        if (!$gradeLevel) {
            $this->error('No grade levels found. Cannot test.');
            return 1;
        }
        
        $step = Step::where('grade_level_id', $gradeLevel->id)->first();
        if (!$step) {
             $this->error('No steps found for grade level. Cannot test.');
             return 1;
        }

        $this->info("Using Grade Level: {$gradeLevel->name}, Step: {$step->name}, Basic Salary: {$step->basic_salary}");

        // Create a dummy employee
        $employee = new Employee();
        $employee->first_name = 'Test';
        $employee->surname = 'Probation';
        $employee->employee_id = rand(10000, 99999); // Use random integer
        $employee->staff_no = 'TEST' . rand(1000, 9999);
        $employee->grade_level_id = $gradeLevel->id;
        $employee->step_id = $step->id;
        $employee->on_probation = true;
        $employee->probation_start_date = Carbon::now()->subDays(10); // Started 10 days ago
        $employee->probation_end_date = Carbon::now()->addMonths(3)->subDays(10);
        $employee->probation_status = 'pending';
        $employee->status = 'On Probation';
        
        // Add other likely required fields
        $employee->gender = 'Male';
        $employee->date_of_birth = '1990-01-01';
        $employee->date_of_first_appointment = '2023-01-01';
        // $employee->current_appointment_date = '2023-01-01'; // Removed as it doesn't exist
        $employee->department_id = 1; // Assuming department 1 exists
        $employee->cadre_id = 1; // Assuming cadre 1 exists
        $employee->state_id = 1;
        $employee->lga_id = 1;
        $employee->ward_id = 1;
        $employee->rank_id = 1;
        $employee->appointment_type_id = 1;
        $employee->email = 'test' . rand(1000, 9999) . '@example.com';
        $employee->mobile_no = '080' . rand(10000000, 99999999);
        $employee->nationality = 'Nigerian';
        $employee->nin = '12345678901';
        $employee->pay_point = 'Headquarters';
        // $employee->marital_status = 'Single'; // Removed
        // $employee->religion = 'Christianity'; // Removed
        $employee->address = 'Test Address';
        // $employee->city = 'Test City'; // Removed
        // Mock other required fields if necessary, but for calculation service this should be enough if we mock relationships or use actual DB
        // We are using actual DB models so we need to save it or mock the service input.
        // The service takes an Employee model. Let's save it to DB to be safe with relationships.
        $employee->save();
        
        $this->info("Created Test Employee: {$employee->employee_id} (On Probation)");

        $service = new PayrollCalculationService();
        $month = Carbon::now()->format('Y-m');

        // 2. Test Case 1: On Probation (Pending)
        $this->info("\nTest Case 1: Employee is On Probation (Pending)");
        $result = $service->calculatePayroll($employee, $month);
        
        $this->info("Net Salary: " . $result['net_salary']);
        
        if ($result['net_salary'] == 0) {
            $this->info("PASS: Net salary is 0 as expected.");
        } else {
            $this->error("FAIL: Net salary is {$result['net_salary']}, expected 0.");
        }

        // 3. Test Case 2: Probation Approved
        $this->info("\nTest Case 2: Probation Approved");
        $employee->on_probation = false;
        $employee->probation_status = 'approved';
        $employee->status = 'Active';
        $employee->save();
        
        $result = $service->calculatePayroll($employee, $month);
        $this->info("Net Salary: " . $result['net_salary']);

        if ($result['net_salary'] > 0) {
             $this->info("PASS: Net salary is > 0 as expected ({$result['net_salary']}).");
        } else {
             $this->error("FAIL: Net salary is 0, expected > 0.");
        }

        // Cleanup
        $employee->delete();
        $this->info("\nTest Employee deleted.");
        
        return 0;
    }
}
