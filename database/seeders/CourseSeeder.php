<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courses = [

            // 1
            ['course' => 'Applied Economics ',  'strand_id' => '1'],
            ['course' => 'Business Ethics and Social Responsibility',  'strand_id' => '1'],
            ['course' => 'Fundamentals of Accountancy, Business and Management 1',  'strand_id' => '1'],
            ['course' => 'Fundamentals of Accountancy, Business and Management 2',  'strand_id' => '1'],
            ['course' => 'Business Math ',  'strand_id' => '1'],
            ['course' => 'Organization and Management ',  'strand_id' => '1'],
            ['course' => 'Principles of Marketing',  'strand_id' => '1'],

            ['course' => 'Creative Writing/ Malikhaing Pagsulat ',  'strand_id' => '2'],
            ['course' => 'Introduction to World Religions and Belief Systems ',  'strand_id' => '2'],
            ['course' => 'Creative Nonfiction',  'strand_id' => '2'],
            ['course' => 'Trends, Networks, and Critical Thinking in the 21st Century Culture ',  'strand_id' => '2'],
            ['course' => 'Philippine Politics and Governance',  'strand_id' => '2'],
            ['course' => 'Community Engagement, Solidarity, and Citizenship',  'strand_id' => '2'],
            ['course' => 'Disciplines and Ideas in the Social Sciences ',  'strand_id' => '2'],
            ['course' => 'Disciplines and Ideas in the Applied Social Sciences',  'strand_id' => '2'],

            ['course' => 'Pre-Calculus',  'strand_id' => '3'],
            ['course' => 'Basic Calculus',  'strand_id' => '3'],
            ['course' => 'General Biology 1',  'strand_id' => '3'],
            ['course' => 'General Biology 2',  'strand_id' => '3'],
            ['course' => 'General Physics 1',  'strand_id' => '3'],
            ['course' => 'General Physics 2',  'strand_id' => '3'],
            ['course' => 'General Chemistry 1',  'strand_id' => '3'],
            ['course' => 'General Chemistry 2',  'strand_id' => '3'],

            ['course' => 'Humanities 1* ',  'strand_id' => '4'],
            ['course' => 'Humanities 2*',  'strand_id' => '4'],
            ['course' => 'Social Science 1** ',  'strand_id' => '4'],
            ['course' => 'Applied Economics',  'strand_id' => '4'],
            ['course' => 'Organization and Management ',  'strand_id' => '4'],
            ['course' => 'Disaster Readiness and Risk Reduction ',  'strand_id' => '4'],
            ['course' => 'Elective 1 (from any Track/Strand)***',  'strand_id' => '4'],
            ['course' => 'Elective 2 (from any Track/Strand)*** ',  'strand_id' => '4'],

            ['course' => 'Safety and First Aid', 'strand_id' => '5'],
            ['course' => 'Human Movement', 'strand_id' => '5'],
            ['course' => 'Fundamentals of Coaching', 'strand_id' => '5'],
            ['course' => 'Sports Officiating and Activity Management', 'strand_id' => '5'],
            ['course' => 'Fitness, Sports and Recreation Leadership', 'strand_id' => '5'],
            ['course' => 'Psychosocial Aspects of Sports and Exercise', 'strand_id' => '5'],
            ['course' => 'Fitness Testing and Basic Exercise Programming', 'strand_id' => '5'],

            ['course' => 'Creative Industries I: Arts and Design Appreciation and Production', 'strand_id' => '6'],
            ['course' => 'Creative Industries II: Performing Arts', 'strand_id' => '6'],
            ['course' => 'Physical and Personal Development in the Arts ', 'strand_id' => '6'],
            ['course' => 'Developing Filipino Identity in the Arts', 'strand_id' => '6'],
            ['course' => 'Integrating the Elements and Principles of Organization in the Arts ', 'strand_id' => '6'],
            ['course' => 'Leadership and Management in Different Arts Fields ', 'strand_id' => '6'],

            ['course' => 'Singing', 'strand_id' => '7'],
            ['course' => 'Solo Vocal Performance', 'strand_id' => '7'],
            ['course' => 'Live Sound Technician', 'strand_id' => '7'],

            ['course' => 'Acting', 'strand_id' => '8'],
            ['course' => 'Technical Lights Crew', 'strand_id' => '8'],
            ['course' => 'Props and Set Designing', 'strand_id' => '8'],
            ['course' => 'Scriptwriting', 'strand_id' => '8'],

            ['course' => 'Illustration', 'strand_id' => '9'],
            ['course' => 'Product Designing', 'strand_id' => '9'],
            ['course' => 'Production Designing', 'strand_id' => '9'],
            ['course' => 'Graphic Designing', 'strand_id' => '9'],

            ['course' => '2D Animation Artist', 'strand_id' => '10'],
            ['course' => '3D Animation Artist', 'strand_id' => '10'],
            ['course' => 'Web Designing', 'strand_id' => '10'],
            ['course' => 'Film Video Utility', 'strand_id' => '10'],
            ['course' => 'Game Arts Developing', 'strand_id' => '10'],
            ['course' => 'Photography', 'strand_id' => '10'],

            ['course' => 'Dancing', 'strand_id' => '11'],
            ['course' => 'Ballroom Dancing', 'strand_id' => '11'],
            ['course' => 'Dance Choreography', 'strand_id' => '11'],
            ['course' => 'Dance Instruction', 'strand_id' => '11'],

            ['course' => 'Hairdressing', 'strand_id' => '12'],
            ['course' => 'Tailoring', 'strand_id' => '12'],
            ['course' => 'Caregiving', 'strand_id' => '12'],
            ['course' => 'Food and Beverage Services', 'strand_id' => '12'],
            ['course' => 'Bread and Pastry Production', 'strand_id' => '12'],
            ['course' => 'Housekeeping', 'strand_id' => '12'],
            ['course' => 'Tour Guiding Services', 'strand_id' => '12'],
            ['course' => 'Tourism Promotion Services', 'strand_id' => '12'],
            ['course' => 'Attractions and Theme Parks Tourism', 'strand_id' => '12'],
            ['course' => 'Handicraft', 'strand_id' => '12'],

            ['course' => 'Computer Programming', 'strand_id' => '13'],
            ['course' => 'Medical Transcription', 'strand_id' => '13'],
            ['course' => 'Animation', 'strand_id' => '13'],

            ['course' => 'Agricrop Production : Horticulture', 'strand_id' => '14'],
            ['course' => 'Agricrop Production : Landscape Installation and Maintenance', 'strand_id' => '14'],
            ['course' => 'Agricrop Production : Organic Agriculture Production', 'strand_id' => '14'],
            ['course' => 'Agricrop Production : Pest Management', 'strand_id' => '14'],
            ['course' => 'Agricrop Production : Rice Machinery Operation', 'strand_id' => '14'],

            ['course' => 'Animal Production : Animal Production 11', 'strand_id' => '14'],
            ['course' => 'Animal Production : Artificial Insemination- Large Ruminants', 'strand_id' => '14'],
            ['course' => 'Animal Production : Artificial Insemination- Swine', 'strand_id' => '14'],
            ['course' => 'Animal Production : Slaughtering', 'strand_id' => '14'],

            ['course' => 'Fish Production : Fish Nursery Operation', 'strand_id' => '14'],
            ['course' => 'Fish Production : Fish or Shrimp Grow Out Operation', 'strand_id' => '14'],
            ['course' => 'Fish Production : Fishport/Wharf Operation', 'strand_id' => '14'],
            ['course' => 'Fish Production : Fish Processing', 'strand_id' => '14'],

            ['course' => 'Automotive Servicing', 'strand_id' => '15'],
            ['course' => 'Refrigeration and Air-Conditioning', 'strand_id' => '15'],
            ['course' => 'Consumer Electronics Servicing', 'strand_id' => '15'],
            ['course' => 'Electrical Installation and Maintenance', 'strand_id' => '15'],
            ['course' => 'Shielded Metal-Arc Welding', 'strand_id' => '15'],
            ['course' => 'Carpentry', 'strand_id' => '15'],
            ['course' => 'Plumbing', 'strand_id' => '15'],
            ['course' => 'Tile Setting', 'strand_id' => '15'],

        ];

        DB::table('tbl_courses')->insert($courses);
    }
}
