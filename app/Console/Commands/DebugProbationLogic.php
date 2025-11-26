<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PendingEmployeeChange;
use App\Models\Employee;
use App\Models\AppointmentType;

class DebugProbationLogic extends Command
{
    protected $signature = 'debug:probation-logic';
    protected $description = 'Debug the probation logic in pending employee change';

    public function handle()
    {
        $this->info('Debugging probation logic...');
        
        // Get the pending change for our test employee
        $pendingChange = PendingEmployeeChange::where('data->first_name', 'John')
                                              ->where('data->surname', 'Probation')
                                              ->first();
        
        if (!$pendingChange) {
            $this->error('Pending change for John Probation not found');
            return 1;
        }
        
        $this->info('Pending change data:');
        $data = $pendingChange->data;
        
        // Check appointment type ID in the data
        if (isset($data['appointment_type_id'])) {
            $this->info("- Appointment Type ID in data: {$data['appointment_type_id']}");
            
            $appointmentType = AppointmentType::find($data['appointment_type_id']);
            if ($appointmentType) {
                $this->info("- Appointment Type Name: {$appointmentType->name}");
                $this->info("- Is not Contract: " . ($appointmentType->name !== 'Contract' ? 'Yes' : 'No'));
                
                // This should have triggered probation placement
                if ($appointmentType->name !== 'Contract') {
                    $this->info("✓ Condition should have been met to place employee on probation");
                } else {
                    $this->info("✗ Employee is a contract employee, so probation was not applied");
                }
            } else {
                $this->error("✗ Appointment type with ID {$data['appointment_type_id']} not found in database");
            }
        } else {
            $this->error("✗ Appointment type ID not found in pending change data");
        }
        
        // Find the employee that was created
        $employee = Employee::where('first_name', 'John')
                            ->where('surname', 'Probation')
                            ->first();
        
        if ($employee) {
            $this->info('\nActual employee in database:');
            $this->info("- ID: {$employee->employee_id}");
            $this->info("- Staff No: {$employee->staff_no}");
            $this->info("- Appointment Type ID: {$employee->appointment_type_id}");
            $this->info("- On Probation: " . ($employee->on_probation ? 'Yes' : 'No'));
            $this->info("- Probation Start Date: " . ($employee->probation_start_date ?: 'NULL'));
            $this->info("- Probation End Date: " . ($employee->probation_end_date ?: 'NULL'));
            $this->info("- Probation Status: " . ($employee->probation_status ?: 'NULL'));
            $this->info("- Status: {$employee->status}");
            
            // Check what appointment type the employee actually has
            $empAppointmentType = AppointmentType::find($employee->appointment_type_id);
            if ($empAppointmentType) {
                $this->info("- Employee's actual appointment type: {$empAppointmentType->name}");
            }
            
            // This is the issue: the update might not have happened, so let's manually fix it
            if ($empAppointmentType && $empAppointmentType->name !== 'Contract' && !$employee->on_probation) {
                $this->info("\nFixing probation status for employee...");
                
                $probationStartDate = now();
                $probationEndDate = $probationStartDate->copy()->addMonths(3);
                
                $employee->update([
                    'on_probation' => true,
                    'probation_start_date' => $probationStartDate,
                    'probation_end_date' => $probationEndDate,
                    'probation_status' => 'pending',
                    'status' => 'On Probation',
                    'probation_notes' => 'Manually fixed probation status after approval'
                ]);
                
                $this->info("✓ Employee probation status has been updated");
            }
        } else {
            $this->error("\nEmployee John Probation not found in database");
        }
        
        return 0;
    }
}