<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Pensioner;
use App\Models\Bank;
use Illuminate\Support\Facades\DB;

echo "--- Fixing Musa Aliyu (Employee 799) --- \n";

DB::beginTransaction();

try {
    $p = Pensioner::find(66);
    $e = Employee::find(799);

    if (!$p || !$e) {
        throw new Exception("Pensioner 66 or Employee 799 not found.");
    }

    echo "Updating Employee 799 appointment type to 'Pensioners' (ID 4)...\n";
    $e->appointment_type_id = 4;
    $e->save();

    echo "Updating Employee 799 bank details from Pensioner 66 record...\n";
    // First, check if a bank record already exists for 799
    $bank = Bank::where('employee_id', 799)->first();
    if (!$bank) {
        $bank = new Bank();
        $bank->employee_id = 799;
    }

    // Get the bank name/code from the bank_list table if possible, or use defaults
    $bankInfo = DB::table('bank_list')->find($p->bank_id);
    
    $bank->bank_name = $bankInfo->bank_name ?? 'Unknown Bank';
    $bank->bank_code = $bankInfo->bank_code ?? '000';
    $bank->account_name = $p->account_name;
    $bank->account_no = $p->account_number;
    $bank->save();

    DB::commit();
    echo "SUCCESS: Musa Aliyu (799) fixed.\n";

} catch (Exception $ex) {
    DB::rollBack();
    echo "ERROR: " . $ex->getMessage() . "\n";
}
