<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProbationService
{
    /**
     * Start probation for an employee
     */
    public function startProbation(Employee $employee, Carbon $startDate, ?string $notes = null): bool
    {
        try {
            DB::beginTransaction();

            // Set probation to start immediately if no start date provided in the future
            $probationStartDate = $startDate;
            $probationEndDate = $probationStartDate->copy()->addMonths(3);

            $employee->update([
                'on_probation' => true,
                'probation_start_date' => $probationStartDate,
                'probation_end_date' => $probationEndDate,
                'probation_status' => 'pending',
                'probation_notes' => $notes,
                'status' => 'On Probation' // Update employee status to reflect probation
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error starting probation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve an employee's probation
     */
    public function approveProbation(Employee $employee, ?string $notes = null): bool
    {
        try {
            if (!$employee->on_probation) {
                throw new \Exception('Employee is not on probation.');
            }

            if (!$employee->canBeEvaluatedForProbation()) {
                throw new \Exception('Employee cannot be evaluated for probation yet. The 3-month probation period has not ended.');
            }

            DB::beginTransaction();

            $employee->update([
                'on_probation' => false,
                'probation_status' => 'approved',
                'status' => 'Active', // Set back to active status
                'probation_notes' => $notes ? $employee->probation_notes . "\n" . 'Approval Note: ' . $notes : $employee->probation_notes
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error approving probation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject an employee's probation
     */
    public function rejectProbation(Employee $employee, ?string $notes = null): bool
    {
        try {
            if (!$employee->on_probation) {
                throw new \Exception('Employee is not on probation.');
            }

            // Only allow rejection after probation period has ended - REMOVED per user request
            // if (!$employee->hasProbationPeriodEnded()) {
            //     throw new \Exception('Employee cannot be rejected before the 3-month probation period has ended.');
            // }

            DB::beginTransaction();

            $employee->update([
                'on_probation' => false,
                'probation_status' => 'rejected',
                'status' => 'Terminated', // Set status to terminated
                'probation_notes' => $notes ? $employee->probation_notes . "\n" . 'Rejection Note: ' . $notes : $employee->probation_notes
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error rejecting probation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extend probation period if needed
     */
    public function extendProbation(Employee $employee, int $extensionMonths, string $extensionReason): bool
    {
        try {
            if (!$employee->on_probation) {
                throw new \Exception('Employee is not on probation.');
            }

            if ($extensionMonths < 1 || $extensionMonths > 6) {
                throw new \Exception('Extension months must be between 1 and 6.');
            }

            DB::beginTransaction();

            // Extend the probation end date
            $extendedEndDate = Carbon::parse($employee->probation_end_date)->addMonths($extensionMonths);

            $employee->update([
                'probation_end_date' => $extendedEndDate,
                'probation_notes' => $employee->probation_notes . "\n" . 'Extension Note: ' . $extensionReason . ' - Extended by ' . $extensionMonths . ' month(s)'
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error extending probation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if an employee is eligible for salary during probation
     * According to the requirements, employees on probation for 3 months have no salary
     */
    public function isEligibleForSalary(Employee $employee): bool
    {
        // If employee is on probation and the 3-month period hasn't ended yet, they are not eligible for salary
        if ($employee->on_probation && $employee->probation_status === 'pending') {
            $startDate = Carbon::parse($employee->probation_start_date);
            $threeMonthsLater = $startDate->copy()->addMonths(3);
            
            // Before the 3 months are up, no salary
            if (Carbon::now()->lt($threeMonthsLater)) {
                return false;
            }
            
            // After 3 months, but before approval/rejection, no salary
            // Only approved probation employees should be eligible for salary after becoming permanent
            return false;
        }

        // If not on probation or probation is rejected, follow normal salary rules
        return $employee->probation_status !== 'rejected';
    }

    /**
     * Get probation status summary for reporting
     */
    public function getProbationSummary(): array
    {
        $totalProbationEmployees = Employee::where('on_probation', true)->count();
        $pendingProbationEmployees = Employee::where('probation_status', 'pending')->count();
        $approvedProbationEmployees = Employee::where('probation_status', 'approved')->count();
        $rejectedProbationEmployees = Employee::where('probation_status', 'rejected')->count();
        
        return [
            'total' => $totalProbationEmployees,
            'pending' => $pendingProbationEmployees,
            'approved' => $approvedProbationEmployees,
            'rejected' => $rejectedProbationEmployees
        ];
    }
}