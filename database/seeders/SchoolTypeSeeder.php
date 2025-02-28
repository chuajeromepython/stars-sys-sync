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
        $school_types = array(
            array('type' => 'Elementary'),
            array('type' => 'Junior High School'),
            array('type' => 'Stand Alone Senior High'),
            array('type' => 'Integrated'),
            array('type' => 'ALS')
        );

        DB::table('tbl_school_types')->insert($school_types);
    }
}
