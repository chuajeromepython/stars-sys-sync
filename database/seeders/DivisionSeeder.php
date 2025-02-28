<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $divisions = array(
            array('name' => 'Laguna'),
            array('name' => 'San Pedro City')
        );
        DB::table('tbl_divisions')->insert($divisions);
    }
}
