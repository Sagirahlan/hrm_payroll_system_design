<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('additions', function (Blueprint $table) {
            // Add the addition_type_id column as a foreign key
            $table->unsignedBigInteger('addition_type_id')->nullable()->after('addition_type');
            
            // Add foreign key constraint
            $table->foreign('addition_type_id')->references('id')->on('addition_types')->onDelete('set null');
        });
        
        // Optional: If you want to migrate existing addition_type values to addition_type_id
        // This requires matching existing addition_type values to addition_types records
        $this->migrateAdditionTypes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additions', function (Blueprint $table) {
            $table->dropForeign(['addition_type_id']);
            $table->dropColumn('addition_type_id');
        });
    }
    
    /**
     * Migrate existing addition_type values to addition_type_id
     */
    private function migrateAdditionTypes(): void
    {
        // This method maps existing string addition_type values to corresponding addition_type_id
        // First, get all additions with their addition_type values
        $additions = DB::table('additions')->select('addition_id', 'addition_type')->get();
        
        foreach ($additions as $addition) {
            // Find matching addition_type record by name
            $additionType = DB::table('addition_types')
                ->where('name', $addition->addition_type)
                ->orWhere('code', $addition->addition_type)
                ->first();
            
            if ($additionType) {
                // Update the addition record with the addition_type_id
                DB::table('additions')
                    ->where('addition_id', $addition->addition_id)
                    ->update(['addition_type_id' => $additionType->id]);
            }
        }
    }
};
