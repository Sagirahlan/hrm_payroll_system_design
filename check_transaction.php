$t = App\Models\PaymentTransaction::with(['employee', 'payroll'])->first();
if($t) {
    echo 'Transaction Found: ' . $t->transaction_id . PHP_EOL;
    echo 'Employee: ' . ($t->employee ? $t->employee->first_name . ' ' . $t->employee->surname : 'None') . PHP_EOL;
    // Check if payroll record exists and has a month. Note: PayrollRecord has payroll_month (date)
    if ($t->payroll) {
        echo 'Payroll ID: ' . $t->payroll->payroll_id . PHP_EOL;
        echo 'Payroll Month: ' . ($t->payroll->payroll_month ? $t->payroll->payroll_month->format('Y-m-d') : 'None') . PHP_EOL;
    } else {
        echo 'Payroll: None' . PHP_EOL;
    }
} else {
    echo 'No transactions found.';
}
