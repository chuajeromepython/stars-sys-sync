<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $persons = [
            [
                'first_name' => 'Nerrie',
                'middle_name' => 'Agbagala',
                'last_name' => 'Afurong',
                'gender' => 'F',
                'birth_date' => '1998-01-19',
            ],
            [
                'first_name' => 'System',
                'middle_name' => '-',
                'last_name' => 'Admininstrator',
                'gender' => 'F',
                'birth_date' => '1998-01-19',
            ],
            [
                'first_name' => 'Frederick',
                'middle_name' => 'B.',
                'last_name' => 'Zaide',
                'gender' => 'M',
                'birth_date' => '1998-01-19',
            ],
        ];

        DB::table('tbl_persons')->insert($persons);
    }
}
