<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ECDCDomainSeeder extends Seeder
{
   
    public function run()
    {
        

        $ecdc_domains = array(
             array(
               "id"=>1,
               "domain"=>"GROSS MOTOR DOMAIN"
             ),
             array(
               "id"=>2,
               "domain"=>"FINE MOTOR DOMAIN"
             ),
             array(
               "id"=>3,
               "domain"=>"SELF HELP DOMAIN"
             ),
             array(
               "id"=>4,
               "domain"=>"RECEPTIVE LANGUAGE DOMAIN"
             ),
             array(
               "id"=>5,
               "domain"=>"EXPRESSIVE LANGUAGE DOMAIN"
             ),
             array(
               "id"=>6,
               "domain"=>"COGNITIVE DOMAIN"
             ),
             array(
               "id"=>7,
               "domain"=>"SOCIO-EMOTIONAL DOMAIN"
             )
        );
        DB::table('tbl_ecdc_domains')->insert($ecdc_domains);


    }


}
