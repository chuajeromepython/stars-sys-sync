<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = array(
            array(
                'username' => "glennnerrie",
                'status' => "1",
                'classification' => "Division Administrator",
                'password' => Hash::make('password'),
                'person_id' => "1",
            ),
            array(
                'username' => "sysad",
                'status' => "1",
                'classification' => "System Administrator",
                'password' => Hash::make('password'),
                'person_id' => "2",
            ),
            array(
                'username' => "frederick.zaide@deped.gov.ph",
                'status' => "1",
                'classification' => "Division Administrator",
                'password' => Hash::make('password'),
                'person_id' => "3",
            ),
            array(
                'username' => "frederick.zaide001@deped.gov.ph",
                'status' => "1",
                'classification' => "Division Administrator",
                'password' => Hash::make('password'),
                'person_id' => "3",
            )
        );

        
        DB::table('tbl_users')->insert($users);

    }
}
