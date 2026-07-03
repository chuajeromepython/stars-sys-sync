<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $academic_years = [
            'from' => '2024',
            'to' => '2025',
            'is_active' => '1',
        ];
        DB::table('tbl_academic_years')->insert($academic_years);
    }
}
