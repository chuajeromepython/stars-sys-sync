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
        $strands = [
            // ACADEMIC TRACK
            // 1
            ['name' => 'Accountancy, Business and Management Strand (ABM)', 'track_id' => '1'],
            // 2
            ['name' => 'Humanities and Social Sciences Strand (HUMMS)', 'track_id' => '1'],
            // 3
            ['name' => 'Science, Technology, Engineering and Mathematics Strand (STEM)', 'track_id' => '1'],
            // 4
            ['name' => 'General Academic Strand', 'track_id' => '1'],

            // 5 -SPORTS
            ['name' => 'Sports',  'track_id' => '3'],

            // 6 - ARTS
            ['name' => 'Arts and Design',  'track_id' => '4'],
            // 7
            ['name' => 'Music', 'track_id' => '4'],
            // 8
            ['name' => 'Theater', 'track_id' => '4'],
            // 9
            ['name' => 'Visual Arts', 'track_id' => '4'],
            // 10
            ['name' => 'Media Arts', 'track_id' => '4'],
            // 11
            ['name' => 'Dance', 'track_id' => '4'],

            // 12 - TVL
            ['name' => 'Home Economics', 'track_id' => '2'],
            // 13
            ['name' => 'Information and Communications Technology', 'track_id' => '2'],
            // 14
            ['name' => 'Agri-Fishery Arts', 'track_id' => '2'],
            // 15
            ['name' => 'Industrial Arts', 'track_id' => '2'],
        ];

        DB::table('tbl_strands')->insert($strands);
    }
}
