<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('ranks')->truncate();
        Schema::enableForeignKeyConstraints();

        $ranks = [
            ['name' => 'GL 01–06', 'title' => 'Junior staff (clerical, attendants, messengers)'],
            ['name' => 'GL 07', 'title' => 'Entry-level graduates (Administrative Officer II, Engineer II, etc.)'],
            ['name' => 'GL 08–09', 'title' => 'Senior Officer / Principal Officer (depending on profession)'],
            ['name' => 'GL 10–14', 'title' => 'Assistant Director / Deputy Director levels'],
            ['name' => 'GL 15–16', 'title' => 'Director levels'],
            ['name' => 'GL 17', 'title' => 'Permanent Secretary / Head of Service'],
        ];

        foreach ($ranks as $rank) {
            DB::table('ranks')->insert($rank);
        }
    }
}
