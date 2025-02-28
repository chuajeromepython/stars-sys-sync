<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subject_components = [
            ["name" => "Music", 'subject_id' =>39],
            ["name" => "Arts", 'subject_id' =>39],
            ["name" => "PE", 'subject_id' =>39],
            ["name" => "Health", 'subject_id' =>39],
        ];
        
        DB::table('tbl_subject_components')->insert($subject_components);
    }
}
