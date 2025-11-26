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

class VerifyStatutoryAdditionMonth extends Command
{
    protected $signature = 'verify:statutory-addition-month';
    protected $description = 'Verify statutory addition month selection';

    public function handle()
    {
        $this->info('Starting Statutory Addition Month Verification...');

        // 1. Setup Data
        $user = User::first();
        if (!$user) {
             $user = User::create([
                'username' => 'admin_test_add',
                'email' => 'admin_add@example.com',
                'password_hash' => bcrypt('password'),
            ]);
        }
        auth()->login($user);

        // Create Employee
        $employee = new Employee();
        $employee->first_name = 'Addition';
        $employee->surname = 'Test';
        $employee->employee_id = rand(100000, 999999);
        $employee->staff_no = 'ADD' . rand(1000, 9999);
        $employee->status = 'Active';
        $employee->grade_level_id = 1;
        $employee->step_id = 1;
        $employee->email = 'add' . rand(1000, 9999) . '@example.com';
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

        // Create Statutory Addition Type
        $additionType = AdditionType::create([
            'name' => 'Test Statutory Addition',
            'code' => 'TSA' . rand(100, 999),
            'is_statutory' => true,
            'calculation_type' => 'fixed_amount',
            'rate_or_amount' => 5000,
        ]);
        $this->info("Created Addition Type: {$additionType->name}");

        // 2. Simulate Request
        $controller = new PayrollController(new PayrollCalculationService());
        
        $selectedMonth = '2025-05';
        $request = new Request([
            'addition_types' => [$additionType->id],
            'statutory_addition_month' => $selectedMonth,
            'employee_ids' => [$employee->employee_id],
            'select_all_pages' => '0',
            // Required fields for validation even if not used for statutory
            'start_date' => date('Y-m-d'), 
        ]);

        try {
            $controller->storeBulkAdditions($request);
            $this->info("Store method called successfully.");
        } catch (\Exception $e) {
            // Check if it's a redirect (success) or actual error
            if (!str_contains($e->getMessage(), 'Redirect')) {
                 // In console, redirects might not throw standard exceptions but we catch just in case
                 // If it's not a redirect exception, it might be a validation error or other logic error
                 // However, validation errors usually throw ValidationException which we can catch specifically if needed
                 // For now, let's assume if it doesn't crash, it might be okay, but we verify DB next.
                 $this->info("Exception (might be redirect): " . $e->getMessage());
            }
        }

        // 3. Verify Data
        $addition = Addition::where('employee_id', $employee->employee_id)
            ->where('addition_type_id', $additionType->id)
            ->first();

        if ($addition) {
            $this->info("Addition created.");
            $expectedStart = $selectedMonth . '-01';
            $expectedEnd = Carbon::parse($expectedStart)->endOfMonth()->format('Y-m-d');

            $actualStart = Carbon::parse($addition->start_date)->format('Y-m-d');
            $actualEnd = Carbon::parse($addition->end_date)->format('Y-m-d');

            if ($actualStart == $expectedStart && $actualEnd == $expectedEnd) {
                $this->info("PASS: Start Date is {$actualStart}");
                $this->info("PASS: End Date is {$actualEnd}");
            } else {
                $this->error("FAIL: Dates do not match.");
                $this->info("Expected Start: {$expectedStart}, Actual: {$actualStart}");
                $this->info("Expected End: {$expectedEnd}, Actual: {$actualEnd}");
            }
        } else {
            $this->error("FAIL: Addition not found in database.");
        }

        // Cleanup
        if ($addition) $addition->delete();
        $additionType->delete();
        $employee->delete();
        $this->info("\nCleanup Done.");
        
        return 0;
    }
}
