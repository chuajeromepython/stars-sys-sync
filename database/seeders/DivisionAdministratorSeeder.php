<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionAdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $division_administrators = array(
            array(
                'status' => "1",
                'user_id' => "1",
                'division_id' => "1",
                'email' => "glennnerrie@yahoo.com",
            ),
            array(
                'status' => "1",
                'user_id' => "3",
                'division_id' => "1",
                'email' => "frederick.zaide@deped.gov.ph",
            ),
            array(
                'status' => "1",
                'user_id' => "4",
                'division_id' => "2",
                'email' => "frederick.zaide001@deped.gov.ph",
            )
        );
        DB::table('tbl_division_administrators')->insert($division_administrators);
    }
}
