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
        $tracks = [
            ['name' => 'Academic Track'],
            ['name' => 'Technical-Vocational-Livelihood Track'],
            ['name' => 'Sports Track'],
            ['name' => 'Arts and Design Track'],
        ];

        DB::table('tbl_tracks')->insert($tracks);
    }
}
