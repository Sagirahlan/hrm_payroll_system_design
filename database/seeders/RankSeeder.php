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
            ['name' => 'Junior staff (clerical, attendants, messengers)', 'title' => 'Junior staff (clerical, attendants, messengers)'],
            ['name' => 'Entry-level graduates (Administrative Officer II, Engineer II, etc.)', 'title' => 'Entry-level graduates (Administrative Officer II, Engineer II, etc.)'],
            ['name' => 'Senior Officer / Principal Officer (depending on profession', 'title' => 'Senior Officer / Principal Officer (depending on profession)'],
            ['name' => 'Assistant Director / Deputy Director levels', 'title' => 'Assistant Director / Deputy Director levels'],
            ['name' => 'Director levels', 'title' => 'Director levels'],
            ['name' => 'Permanent Secretary / Head of Service', 'title' => 'Permanent Secretary / Head of Service'],
        ];

        foreach ($ranks as $rank) {
            DB::table('ranks')->insert($rank);
        }
    }
}
