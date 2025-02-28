<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $semesters = array(
            
            array('semester' => 'First'),
            array('semester' => 'Second'),
            array('semester' => 'Third'),

        );

        DB::table('tbl_semesters')->insert($semesters);
    }
}
