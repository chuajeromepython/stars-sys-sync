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
        $weeks = array(
            array('week' => 'Week 1'),
            array('week' => 'Week 2'),
            array('week' => 'Week 3'),
            array('week' => 'Week 4'),
            array('week' => 'Week 5'),
            array('week' => 'Week 6'),
            array('week' => 'Week 7'),
            array('week' => 'Week 8'),
            array('week' => 'Week 9'),
            array('week' => 'Week 10'),
            array('week' => 'Week 11'),
            array('week' => 'Week 12'),
        );

        DB::table('tbl_weeks')->insert($weeks);
    }
}
