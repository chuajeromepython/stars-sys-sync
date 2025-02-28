<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class AssessmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $types = array(
            
            array('type' => 'Periodical'),
            array('type' => 'Summative'),
            array('type' => 'ECD'),

        );

        DB::table('tbl_assessment_types')->insert($types);
    }
}
