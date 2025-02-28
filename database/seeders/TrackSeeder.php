<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tracks = array(
            array('name' => 'Academic Track'),
            array('name' => 'Technical-Vocational-Livelihood Track'),
            array('name' => 'Sports Track'),
            array('name' => 'Arts and Design Track')
        );

        DB::table('tbl_tracks')->insert($tracks);
    }
}
