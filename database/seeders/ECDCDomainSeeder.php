<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ECDCDomainSeeder extends Seeder
{
    public function run()
    {

        $ecdc_domains = [
            [
                'id' => 1,
                'domain' => 'GROSS MOTOR DOMAIN',
            ],
            [
                'id' => 2,
                'domain' => 'FINE MOTOR DOMAIN',
            ],
            [
                'id' => 3,
                'domain' => 'SELF HELP DOMAIN',
            ],
            [
                'id' => 4,
                'domain' => 'RECEPTIVE LANGUAGE DOMAIN',
            ],
            [
                'id' => 5,
                'domain' => 'EXPRESSIVE LANGUAGE DOMAIN',
            ],
            [
                'id' => 6,
                'domain' => 'COGNITIVE DOMAIN',
            ],
            [
                'id' => 7,
                'domain' => 'SOCIO-EMOTIONAL DOMAIN',
            ],
        ];
        DB::table('tbl_ecdc_domains')->insert($ecdc_domains);

    }
}
