<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class SchoolCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $school_categories = array(
            array('category' => 'Public'),
            array('category' => 'Private'),
        );

        DB::table('tbl_school_categories')->insert($school_categories);
    }
}
