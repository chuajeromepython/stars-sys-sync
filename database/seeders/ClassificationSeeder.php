<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $classifications = array(
            array('classification' => 'System Administrator'),
            array('classification' => 'Division Supervisor'),
            array('classification' => 'District Supervisor'),
            array('classification' => 'School Head'),
            array('classification' => 'Teacher'),
            array('claissfication' => 'Student'),
            array('classification' => 'Division Administrator'),
            array('classification' => 'Division Superintendent'),
            array('classification' => 'Assistant Division Superintendent'),
            array('classification' => 'Chief of CID'),
            array('classification' => 'Chief of SGOD'),
            array('classification' => 'Department Head'),
        );

        DB::table('tbl_classifications')->insert($classifications);
    }
}
