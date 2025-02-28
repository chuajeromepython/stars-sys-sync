<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $gradeLevels = array(
            array('level' => "Grade 1"),
            array('level' => "Grade 2"),
            array('level' => "Grade 3"),
            array('level' => "Grade 4"),
            array('level' => "Grade 5"),
            array('level' => "Grade 6"),
            array('level' => "Grade 7"),
            array('level' => "Grade 8"),
            array('level' => "Grade 9"),
            array('level' => "Grade 10"),
            array('level' => "Grade 11"),
            array('level' => "Grade 12"),
            array('level' => "Kinder")
        );

        DB::table('tbl_grade_levels')->insert($gradeLevels);
    }
}
