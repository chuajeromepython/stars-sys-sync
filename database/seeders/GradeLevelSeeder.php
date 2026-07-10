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
        $gradeLevels = [
            ['level' => 'Grade 1'],
            ['level' => 'Grade 2'],
            ['level' => 'Grade 3'],
            ['level' => 'Grade 4'],
            ['level' => 'Grade 5'],
            ['level' => 'Grade 6'],
            ['level' => 'Grade 7'],
            ['level' => 'Grade 8'],
            ['level' => 'Grade 9'],
            ['level' => 'Grade 10'],
            ['level' => 'Grade 11'],
            ['level' => 'Grade 12'],
            ['level' => 'Kinder'],
        ];

        DB::table('tbl_grade_levels')->insert($gradeLevels);
    }
}
