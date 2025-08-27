<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lgas = DB::table('lgas')->pluck('id');

        if ($lgas->isEmpty()) {
            return;
        }

        $wards = [];
        foreach ($lgas as $lgaId) {
            for ($i = 1; $i <= 5; $i++) {
                $wards[] = [
                    'ward_name' => 'Ward ' . $i,
                    'lga_id' => $lgaId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('wards')->insert($wards);
    }
}
