$e = App\Models\Employee::with(['gradeLevel.steps', 'step', 'appointmentType'])->find(92);
if ($e) {
    echo "Name: " . $e->full_name . PHP_EOL;
    echo "AppType: " . ($e->appointmentType ? $e->appointmentType->name : 'None') . PHP_EOL;
    echo "Amount: " . $e->amount . PHP_EOL;
    // Check grade level and step
    if ($e->gradeLevel) {
         echo "GL: " . $e->gradeLevel->name . PHP_EOL;
         if ($e->step) {
             echo "Step: " . $e->step->name . " (Basic: " . $e->step->basic_salary . ")" . PHP_EOL;
         } else {
             echo "Step: None" . PHP_EOL;
         }
    } else {
         echo "GL: None" . PHP_EOL;
    }

    echo "Deductions from DB:" . PHP_EOL;
    $d = App\Models\Deduction::where('employee_id', 92)->get();
    foreach($d as $ded) {
        $type = $ded->deductionType ? $ded->deductionType->name : 'Unknown';
        echo " - " . $type . ": " . $ded->amount . " (CalcType: " . $ded->calculation_type_description . ")" . PHP_EOL;
    }

    // Check Additions
    echo "Additions from DB:" . PHP_EOL;
     $a = App\Models\Addition::where('employee_id', 92)->get();
    foreach($a as $add) {
        $type = $add->additionType ? $add->additionType->name : 'Unknown';
        echo " - " . $type . ": " . $add->amount . PHP_EOL;
    }

} else {
    echo "Employee not found." . PHP_EOL;
}
