<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pensioners', function (Blueprint $table) {
            $table->string('staff_no')->nullable()->after('employee_id');
        });

        // Sync existing data
        $pensioners = \App\Models\Pensioner::all();
        foreach ($pensioners as $pensioner) {
            $employee = \App\Models\Employee::where('employee_id', $pensioner->employee_id)->first();
            
            if (!$employee) {
                // If the employee doesn't exist, create one as requested
                // Note: This requires all fields or at least nullable ones. 
                // Since this is a migration, we'll try to populate it with what we have.
                $employee = \App\Models\Employee::create([
                    'first_name' => $pensioner->first_name,
                    'surname' => $pensioner->surname,
                    'middle_name' => $pensioner->middle_name,
                    'email' => $pensioner->email,
                    'mobile_no' => $pensioner->phone_number,
                    'date_of_birth' => $pensioner->date_of_birth,
                    'date_of_first_appointment' => $pensioner->date_of_first_appointment,
                    'status' => 'Retired',
                    'staff_no' => str_pad($pensioner->id, 6, '0', STR_PAD_LEFT), // Default if missing
                ]);
                
                $pensioner->employee_id = $employee->employee_id;
            }
            
            $pensioner->staff_no = $employee->staff_no;
            $pensioner->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pensioners', function (Blueprint $table) {
            $table->dropColumn('staff_no');
        });
    }
};
