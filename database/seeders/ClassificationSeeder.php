<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $classifications = [
            ['classification' => 'System Administrator'],
            ['classification' => 'Division Supervisor'],
            ['classification' => 'District Supervisor'],
            ['classification' => 'School Head'],
            ['classification' => 'Teacher'],
            ['claissfication' => 'Student'],
            ['classification' => 'Division Administrator'],
            ['classification' => 'Division Superintendent'],
            ['classification' => 'Assistant Division Superintendent'],
            ['classification' => 'Chief of CID'],
            ['classification' => 'Chief of SGOD'],
            ['classification' => 'Department Head'],
        ];

        DB::table('tbl_classifications')->insert($classifications);
    }
}
