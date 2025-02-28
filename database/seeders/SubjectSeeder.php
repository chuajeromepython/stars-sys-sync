<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subjects = [
            ["title" => "21st Century Literature from the Philippines and the World"],
            ["title" => "Applied Economics Organization and Management "],
            ["title" => "Araling Panlipunan"],
            ["title" => "Community Engagement, Solidarity, and Citizenship"],
            ["title" => "Contemporary Philippine Arts from the Regions"],
            ["title" => "Creative Industries I: Arts and Design Appreciation and Production"],
            ["title" => "Creative Industries II: Performing Arts"],
            ["title" => "Creative Nonfiction"],
            ["title" => "Creative Writing/ Malikhaing Pagsulat "],
            ["title" => "Dance"],
            ["title" => "Developing Filipino Identity in the Arts"],
            ["title" => "Disaster Readiness and Risk Reduction"],
            ["title" => "Disaster Readiness and Risk Reduction "],
            ["title" => "Disciplines and Ideas in the Applied Social Sciences"],
            ["title" => "Disciplines and Ideas in the Social Sciences "],
            ["title" => "Earth and Life Science"],
            ["title" => "Earth Science "],
            ["title" => "Edukasyon sa Pagpapakatao (EsP)"],
            ["title" => "Edukasyong Pantahanan at Pangkabuhayan (EPP)"],
            ["title" => "Empowerment Technologies "],
            ["title" => "English"],
            ["title" => "English for Academic and Professional Purposes"],
            ["title" => "Entrepreneurship"],
            ["title" => "Filipino"],
            ["title" => "Filipino sa Piling Larangan"],
            ["title" => "Fitness Testing and Exercise Programming"],
            ["title" => "Fitness, Sports, and Recreation Leadership"],
            ["title" => "Fundamentals of Coaching"],
            ["title" => "General Math"],
            ["title" => "Human Movement"],
            ["title" => "Humanities 1"],
            ["title" => "Humanities 2"],
            ["title" => "Integrating the Elements and Principles of Organization in the Arts "],
            ["title" => "Introduction to the Philosophy of the Human Person"],
            ["title" => "Introduction to World Religions and Belief Systems "],
            ["title" => "Komunikasyon at Pananaliksik sa Wika at Kulturang Pilipino"],
            ["title" => "Leadership and Management in Different Arts Fields "],
            ["title" => "Literary Arts"],
            ["title" => "MAPEH"],
            ["title" => "Mathematics"],
            ["title" => "Media and Information Literacy"],
            ["title" => "Media Arts and Visual Arts"],
            ["title" => "Mother Tongue"],
            ["title" => "Music"],
            ["title" => "Oral Communication"],
            ["title" => "Pagbasa at Pagsusuri ng Iba’t-Ibang Teksto Tungo sa Pananaliksik"],
            ["title" => "Personal Development"],
            ["title" => "Philippine Politics and Governance"],
            ["title" => "Physical and Personal Development in the Arts "],
            ["title" => "Physical Education and Health"],
            ["title" => "Physical Science"],
            ["title" => "Practical Research 1"],
            ["title" => "Practical Research 2"],
            ["title" => "Psychosocial Aspects of Sports and Exercise"],
            ["title" => "Reading and Writing"],
            ["title" => "Safety and First Aid"],
            ["title" => "Science"],
            ["title" => "Social Science 1"],
            ["title" => "Sports Officiating and Activity Management"],
            ["title" => "Statistics and Probability"],
            ["title" => "Technology and Livelihood Education (TLE)"],
            ["title" => "Theater"],
            ["title" =>"Trends, Networks, and Critical Thinking in the 21st Century Culture "],
            ["title" => "Understanding Culture, Society and Politics"],
            ["title" => "ECD"],
        ];


        DB::table('tbl_subjects')->insert($subjects);
    }
}
