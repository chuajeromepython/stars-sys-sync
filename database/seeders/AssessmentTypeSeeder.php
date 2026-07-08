<?php

namespace Database\Seeders;

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
        $types = [
            ['type' => 'Summative'],
            ['type' => 'Term Exam'],
            ['type' => 'Diagnostic'],
            ['type' => 'ECD'],

        ];

        DB::table('tbl_assessment_types')->insert($types);
    }
}
