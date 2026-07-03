<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $periods = [

            ['period' => 'First'],
            ['period' => 'Second'],
            ['period' => 'Third'],
            ['period' => 'Fourth'],
            ['period' => 'Prelim'],
            ['period' => 'Midterm'],
            ['period' => 'Pre-Final'],
            ['period' => 'Final'],

        ];

        DB::table('tbl_periods')->insert($periods);
    }
}
