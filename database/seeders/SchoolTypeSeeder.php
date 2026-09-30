<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $school_types = [
            ['type' => 'Elementary'],
            ['type' => 'Junior High School'],
            ['type' => 'Stand Alone Senior High'],
            ['type' => 'Integrated'],
            ['type' => 'ALS'],
        ];

        DB::table('tbl_school_types')->insert($school_types);
    }
}
