<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $states = [
            "abia", "adamawa", "akwa-ibom", "anambra", "abuja", "bauchi", "bayelsa", "benue", "borno", "cross-river",
            "delta", "ebonyi", "edo", "ekiti", "enugu", "gombe", "imo", "jigawa", "kaduna", "kano",
            "katsina", "kebbi", "kogi", "kwara", "lagos", "nasarawa", "niger", "ogun", "ondo", "osun",
            "oyo", "plateau", "rivers", "sokoto", "taraba", "yobe", "zamfara"
        ];

        foreach ($states as $stateName) {
            State::create(['name' => $stateName]);
        }
    }
}
