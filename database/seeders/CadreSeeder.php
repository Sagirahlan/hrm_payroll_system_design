<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cadre;

class CadreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Cadre::create(['cadre_name' => 'Junior Staff']);
        Cadre::create(['cadre_name' => 'Senior Staff']);
        Cadre::create(['cadre_name' => 'Management']);
    }
}