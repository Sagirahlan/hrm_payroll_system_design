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
        // Update all 'warning' action types to 'query'
        \DB::table('disciplinary_actions')
          ->where('action_type', 'warning')
          ->update(['action_type' => 'query']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert all 'query' action types back to 'warning'
        \DB::table('disciplinary_actions')
          ->where('action_type', 'query')
          ->update(['action_type' => 'warning']);
    }
};
