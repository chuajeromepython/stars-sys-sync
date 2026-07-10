<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $districts = [
            ['id' => 1, 'name' => 'Alaminos', 'division_id' => 1],
            ['id' => 2, 'name' => 'Bay', 'division_id' => 1],
            ['id' => 3, 'name' => 'Calauan', 'division_id' => 1],
            ['id' => 4, 'name' => 'Cavinti', 'division_id' => 1],
            ['id' => 5, 'name' => 'Famy', 'division_id' => 1],
            ['id' => 6, 'name' => 'Kalayaan', 'division_id' => 1],
            ['id' => 7, 'name' => 'Liliw', 'division_id' => 1],
            ['id' => 8, 'name' => 'Los Baños', 'division_id' => 1],
            ['id' => 9, 'name' => 'Luisiana', 'division_id' => 1],
            ['id' => 10, 'name' => 'Lumban', 'division_id' => 1],
            ['id' => 11, 'name' => 'Mabitac', 'division_id' => 1],
            ['id' => 12, 'name' => 'Magdalena', 'division_id' => 1],
            ['id' => 13, 'name' => 'Majayjay', 'division_id' => 1],
            ['id' => 14, 'name' => 'Nagcarlan', 'division_id' => 1],
            ['id' => 15, 'name' => 'Paete', 'division_id' => 1],
            ['id' => 16, 'name' => 'Pagsanjan', 'division_id' => 1],
            ['id' => 17, 'name' => 'Pakil', 'division_id' => 1],
            ['id' => 18, 'name' => 'Pangil', 'division_id' => 1],
            ['id' => 19, 'name' => 'Pila', 'division_id' => 1],
            ['id' => 20, 'name' => 'Rizal', 'division_id' => 1],
            ['id' => 21, 'name' => 'Santa Cruz', 'division_id' => 1],
            ['id' => 22, 'name' => 'Santa Maria', 'division_id' => 1],
            ['id' => 23, 'name' => 'Siniloan', 'division_id' => 1],
            ['id' => 24, 'name' => 'Victoria', 'division_id' => 1],
        ];

        DB::table('tbl_districts')->insert($districts);
    }
}
