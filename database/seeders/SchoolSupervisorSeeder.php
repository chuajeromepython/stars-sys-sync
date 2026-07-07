<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\School;
use App\Models\SchoolSupervisor;
use App\Models\User;
use Illuminate\Database\Seeder;

class SchoolSupervisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csv_file = fopen(public_path("seeders\SCHOOL_HEAD_SEEDER_CSV.csv"), 'r');
        $index = 0;
        $last_name = 0;
        $first_name = 1;
        $middle_name = 2;
        $suffix = 3;
        $email_address = 4;
        $gender = 5;
        $birth_date = 6;
        $school_id = 7;

        while (($data = fgetcsv($csv_file)) !== false) {

            if ($index > 0) {
                $school = School::where('code', $data[$school_id])->first();
                $person = new Person;
                $person->first_name = $data[$first_name];
                $person->middle_name = $data[$middle_name];
                $person->last_name = $data[$last_name];
                $person->suffix = $data[$suffix];
                $person->gender = $data[$gender];
                $person->birth_date = $data[$birth_date];
                $person->save();

                $user = new User;
                $user->username = $data[$email_address];
                $user->password = bcrypt('12345');
                $user->classification = 'School Head';
                $user->status = true;
                $user->person_id = $person->id;
                $user->save();

                $supervisor = new SchoolSupervisor;
                $supervisor->status = true;
                $supervisor->user_id = $user->id;
                $supervisor->school_id = $school->id;
                $supervisor->email = $data[$email_address];
                $supervisor->save();
            }
            $index++;
        }
    }
}
