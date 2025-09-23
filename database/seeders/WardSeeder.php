<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ward;
use App\Models\Lga;

class WardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Load the ward data from the JSON file
        $jsonPath = database_path('seeders/states-and-lgas-and-wards.json');
        $wardsData = json_decode(file_get_contents($jsonPath), true);

        foreach ($wardsData as $stateData) {
            foreach ($stateData['lgas'] as $lgaData) {
                // Find the LGA by name
                $lga = Lga::where('name', $lgaData['lga'])->first();
                
                // If LGA exists, create the wards
                if ($lga) {
                    foreach ($lgaData['wards'] as $wardName) {
                        Ward::firstOrCreate([
                            'ward_name' => $wardName,
                            'lga_id' => $lga->id  // Using $lga->id since Lga model uses default primary key
                        ]);
                    }
                }
            }
        }
    }
}
