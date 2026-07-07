<?php

namespace Database\Seeders;

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
        $semesters = [

            ['semester' => 'First'],
            ['semester' => 'Second'],
            ['semester' => 'Third'],

        ];

        DB::table('tbl_semesters')->insert($semesters);
    }
}
