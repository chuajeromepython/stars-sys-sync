<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sections = array(
            array('section' => 'APPLE', 'school_id' => "344"),
            array('section' => 'MANGO', 'school_id' => "344"),
            array('section' => 'GRAPES', 'school_id' => "344"),
            array('section' => 'ORANGE', 'school_id' => "344"),
        );

        
        DB::table('tbl_sections')->insert($sections);
    }
}
