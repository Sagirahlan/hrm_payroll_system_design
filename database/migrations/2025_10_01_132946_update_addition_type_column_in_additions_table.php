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
        // First, make sure the column change is possible by setting a temporary default
        Schema::table('additions', function (Blueprint $table) {
            $table->string('addition_type')->nullable()->default(null)->change();
        });
        
        // If there are existing records with addition_type_id but no addition_type, populate the addition_type from the addition_types table
        $this->populateAdditionTypeFromAdditionTypeId();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additions', function (Blueprint $table) {
            $table->string('addition_type')->nullable(false)->default('')->change();
        });
    }
    
    /**
     * Populate addition_type column from addition_type_id relationship
     */
    private function populateAdditionTypeFromAdditionTypeId(): void
    {
        // Get all additions that have addition_type_id but no addition_type
        $additions = DB::table('additions')
            ->whereNotNull('addition_type_id')
            ->where(function($query) {
                $query->whereNull('addition_type')
                      ->orWhere('addition_type', '');
            })
            ->get(['addition_id', 'addition_type_id']);
        
        foreach ($additions as $addition) {
            // Get the addition type name from the addition_types table
            $additionType = DB::table('addition_types')
                ->where('id', $addition->addition_type_id)
                ->first(['name']);
            
            if ($additionType) {
                // Update the addition record with the addition_type name
                DB::table('additions')
                    ->where('addition_id', $addition->addition_id)
                    ->update(['addition_type' => $additionType->name]);
            }
        }
    }
};
