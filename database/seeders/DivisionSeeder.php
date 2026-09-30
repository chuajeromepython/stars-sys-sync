<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $divisions = [
            ['name' => 'Laguna'],
            ['name' => 'San Pedro City'],
        ];
        DB::table('tbl_divisions')->insert($divisions);
    }
}
