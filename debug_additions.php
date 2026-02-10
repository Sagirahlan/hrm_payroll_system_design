<?php

use App\Models\Employee;
use App\Models\Addition;
use App\Models\PayrollRecord;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employeeId = 190;

echo "Checking for Employee ID: $employeeId\n";

$employee = Employee::find($employeeId);

if (!$employee) {
    echo "Employee NOT FOUND.\n";
    // Check if maybe staff_no matches 190 (unlikely but possible)
    $byStaffNo = Employee::where('staff_no', $employeeId)->first();
    if ($byStaffNo) {
        echo "Found employee by staff_no: " . $byStaffNo->employee_id . "\n";
    }
} else {
    echo "Employee Found: " . $employee->first_name . " " . $employee->surname . "\n";
    echo "PK (employee_id): " . $employee->employee_id . "\n";
    echo "Status: " . $employee->status . "\n";
    echo "Appointment Type ID: " . $employee->appointment_type_id . "\n";
    echo "Grade Level ID: " . $employee->grade_level_id . "\n";
    echo "Department ID: " . $employee->department_id . "\n";
    
    $count = Addition::where('employee_id', $employeeId)->count();
    echo "Additions count for employee_id $employeeId: $count\n";
    
    if ($count > 0) {
        echo "Listing first 5 additions:\n";
        $additions = Addition::where('employee_id', $employeeId)->take(5)->get();
        foreach ($additions as $add) {
            echo " - ID: " . $add->addition_id . ", Type: " . $add->addition_type_id . ", Amount: " . $add->amount . "\n";
        }
    } else {
        echo "No additions found for this employee ID.\n";
    }

    // Check Payroll Records
    echo "\nPayroll Records check:\n";
    $payrolls = PayrollRecord::where('employee_id', $employeeId)->get();
    foreach ($payrolls as $pr) {
        echo " - Month: " . $pr->payroll_month . ", Status: " . $pr->status . "\n";
    }
}

echo "\nChecking random additions from DB to see schema:\n";
$randomAdds = Addition::take(5)->get();
foreach ($randomAdds as $add) {
    echo " - EmpID: " . $add->employee_id . ", ID: " . $add->addition_id . "\n";
}
