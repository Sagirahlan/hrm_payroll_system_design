<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PendingEmployeeChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovePendingChange extends Command
{
    protected $signature = 'approve:pending-change {changeId}';
    protected $description = 'Approve a pending employee change to trigger probation placement';

    public function handle()
    {
        $changeId = $this->argument('changeId');

        $pendingChange = PendingEmployeeChange::find($changeId);

        if (!$pendingChange) {
            $this->error("Pending change with ID {$changeId} not found.");
            return 1;
        }

        try {
            // Create a proper request object
            $request = new Request([
                'approval_notes' => 'Automated approval for probation testing'
            ]);

            $controller = new \App\Http\Controllers\PendingEmployeeChangeController();

            // Call the approve method like the web interface would
            $response = $controller->approve($pendingChange, $request);

            $this->info("Pending change {$changeId} approved successfully!");
            $this->info("Employee should now be on probation.");

            // Refresh the change to get the latest data
            $pendingChange->refresh();

            // Check if the employee was created and placed on probation
            if ($pendingChange->employee) {
                $employee = $pendingChange->employee;
                $this->info("Employee details:");
                $this->info("- Name: {$employee->first_name} {$employee->surname}");
                $this->info("- Staff No: {$employee->staff_no}");
                $this->info("- On Probation: " . ($employee->on_probation ? 'Yes' : 'No'));
                if ($employee->on_probation) {
                    $this->info("- Probation Start: {$employee->probation_start_date}");
                    $this->info("- Probation End: {$employee->probation_end_date}");
                    $this->info("- Probation Status: {$employee->probation_status}");
                    $this->info("- Employee Status: {$employee->status}");
                }
            } else {
                $this->info("No employee associated with this pending change.");
            }
        } catch (\Exception $e) {
            $this->error("Error approving change: " . $e->getMessage());
            \Log::error("Error in approve command: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return 1;
        }

        return 0;
    }
}