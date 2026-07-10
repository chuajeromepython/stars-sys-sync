<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $weeks = [
            ['week' => 'Week 1'],
            ['week' => 'Week 2'],
            ['week' => 'Week 3'],
            ['week' => 'Week 4'],
            ['week' => 'Week 5'],
            ['week' => 'Week 6'],
            ['week' => 'Week 7'],
            ['week' => 'Week 8'],
            ['week' => 'Week 9'],
            ['week' => 'Week 10'],
            ['week' => 'Week 11'],
            ['week' => 'Week 12'],
        ];

        DB::table('tbl_weeks')->insert($weeks);
    }
}
