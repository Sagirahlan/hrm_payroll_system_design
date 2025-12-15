<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BankList;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class BankListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to the JSON file
        $jsonPath = database_path('seeders/bank_codes_response_1694712440408.json');

        // Check if the file exists
        if (!File::exists($jsonPath)) {
            $this->command->error("Bank codes JSON file not found at: $jsonPath");
            return;
        }

        // Read and decode the JSON file
        $jsonData = File::get($jsonPath);
        $banks = json_decode($jsonData, true);

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        DB::table('bank_list')->truncate();

        // Insert new data
        foreach ($banks as $bank) {
            BankList::create([
                'bank_name' => $bank['bankName'],
                'bank_code' => $bank['id'],
                'is_active' => $bank['isActive'] ?? true,
            ]);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Bank list table seeded successfully with ' . count($banks) . ' banks.');
    }
}
