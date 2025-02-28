<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $strands = array(
            // ACADEMIC TRACK
            // 1
            array('name' => 'Accountancy, Business and Management Strand (ABM)', 'track_id' => '1'), 
            // 2
            array('name' => 'Humanities and Social Sciences Strand (HUMMS)', 'track_id' => '1'),
            // 3
            array('name' => 'Science, Technology, Engineering and Mathematics Strand (STEM)', 'track_id' => '1'),
            // 4
            array('name' => 'General Academic Strand', 'track_id' => '1'),
         
            // 5 -SPORTS
            array('name' => 'Sports',  'track_id' => '3'),
            
           
            
            
            //6 - ARTS  
            array('name' => 'Arts and Design',  'track_id' => '4'),
            // 7
            array('name' => 'Music', 'track_id' => '4'),
            // 8
            array('name' => 'Theater', 'track_id' => '4'),
            // 9
            array('name' => 'Visual Arts', 'track_id' => '4'),
            // 10
            array('name' => 'Media Arts', 'track_id' => '4'),
            // 11
            array('name' => 'Dance', 'track_id' => '4'),
           

            //12 - TVL
            array('name' => 'Home Economics', 'track_id' => '2'),
            //13
            array('name' => 'Information and Communications Technology', 'track_id' => '2'),
            //14
            array('name' => 'Agri-Fishery Arts', 'track_id' => '2'),
            // 15
            array('name' => 'Industrial Arts', 'track_id' => '2'),
        );
        
        DB::table('tbl_strands')->insert($strands);
    }
}
