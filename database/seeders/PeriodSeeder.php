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
        $periods = array(
            
            array('period' => 'First'),
            array('period' => 'Second'),
            array('period' => 'Third'),
            array('period' => 'Fourth'),
            array('period' => 'Prelim'),
            array('period' => 'Midterm'),
            array('period' => 'Pre-Final'),
            array('period' => 'Final'),

        );

        DB::table('tbl_periods')->insert($periods);
    }
}
