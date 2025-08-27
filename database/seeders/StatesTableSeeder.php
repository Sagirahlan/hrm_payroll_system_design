<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing states
        DB::table('states')->truncate();
        
        // Nigeria's 36 states and 1 FCT
        $nigerianStates = [
            ['state_name' => 'Abia'],
            ['state_name' => 'Adamawa'],
            ['state_name' => 'Akwa Ibom'],
            ['state_name' => 'Anambra'],
            ['state_name' => 'Bauchi'],
            ['state_name' => 'Bayelsa'],
            ['state_name' => 'Benue'],
            ['state_name' => 'Borno'],
            ['state_name' => 'Cross River'],
            ['state_name' => 'Delta'],
            ['state_name' => 'Ebonyi'],
            ['state_name' => 'Edo'],
            ['state_name' => 'Ekiti'],
            ['state_name' => 'Enugu'],
            ['state_name' => 'Gombe'],
            ['state_name' => 'Imo'],
            ['state_name' => 'Jigawa'],
            ['state_name' => 'Kaduna'],
            ['state_name' => 'Kano'],
            ['state_name' => 'Katsina'],
            ['state_name' => 'Kebbi'],
            ['state_name' => 'Kogi'],
            ['state_name' => 'Kwara'],
            ['state_name' => 'Lagos'],
            ['state_name' => 'Nasarawa'],
            ['state_name' => 'Niger'],
            ['state_name' => 'Ogun'],
            ['state_name' => 'Ondo'],
            ['state_name' => 'Osun'],
            ['state_name' => 'Oyo'],
            ['state_name' => 'Plateau'],
            ['state_name' => 'Rivers'],
            ['state_name' => 'Sokoto'],
            ['state_name' => 'Taraba'],
            ['state_name' => 'Yobe'],
            ['state_name' => 'Zamfara'],
            ['state_name' => 'Federal Capital Territory (FCT)']
        ];
        
        // Insert states with timestamps
        $statesToInsert = [];
        foreach ($nigerianStates as $state) {
            $statesToInsert[] = array_merge($state, [
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        DB::table('states')->insert($statesToInsert);
    }
}