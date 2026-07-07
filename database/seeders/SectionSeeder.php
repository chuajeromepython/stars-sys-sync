<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sections = [
            ['section' => 'APPLE', 'school_id' => '344'],
            ['section' => 'MANGO', 'school_id' => '344'],
            ['section' => 'GRAPES', 'school_id' => '344'],
            ['section' => 'ORANGE', 'school_id' => '344'],
        ];

        DB::table('tbl_sections')->insert($sections);
    }
}
