<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Deduction;
use App\Models\DeductionType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all suspended employees
        $suspendedEmployees = Employee::where('status', 'Suspended')->get();
        
        foreach ($suspendedEmployees as $employee) {
            // Get all deductions for this suspended employee
            $deductions = Deduction::where('employee_id', $employee->employee_id)->get();
            
            foreach ($deductions as $deduction) {
                // Get the deduction type to check if it's statutory
                $deductionType = DeductionType::find($deduction->deduction_type_id);
                
                if ($deductionType && $deductionType->is_statutory) {
                    // Halve the amount for statutory deductions of suspended employees
                    $newAmount = $deduction->amount / 2;
                    $deduction->update(['amount' => $newAmount]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get all suspended employees
        $suspendedEmployees = Employee::where('status', 'Suspended')->get();
        
        foreach ($suspendedEmployees as $employee) {
            // Get all deductions for this suspended employee
            $deductions = Deduction::where('employee_id', $employee->employee_id)->get();
            
            foreach ($deductions as $deduction) {
                // Get the deduction type to check if it's statutory
                $deductionType = DeductionType::find($deduction->deduction_type_id);
                
                if ($deductionType && $deductionType->is_statutory) {
                    // Double the amount to revert the halving
                    $newAmount = $deduction->amount * 2;
                    $deduction->update(['amount' => $newAmount]);
                }
            }
        }
    }
};
