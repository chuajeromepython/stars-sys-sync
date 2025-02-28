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
        $courses = array(

            // 1
            array('course' => 'Applied Economics ',  'strand_id' => '1'),
            array('course' => 'Business Ethics and Social Responsibility',  'strand_id' => '1'),
            array('course' => 'Fundamentals of Accountancy, Business and Management 1',  'strand_id' => '1'),
            array('course' => 'Fundamentals of Accountancy, Business and Management 2',  'strand_id' => '1'),
            array('course' => 'Business Math ',  'strand_id' => '1'),
            array('course' => 'Organization and Management ',  'strand_id' => '1'),
            array('course' => 'Principles of Marketing',  'strand_id' => '1'),


            array('course' => 'Creative Writing/ Malikhaing Pagsulat ',  'strand_id' => '2'),
            array('course' => 'Introduction to World Religions and Belief Systems ',  'strand_id' => '2'),
            array('course' => 'Creative Nonfiction',  'strand_id' => '2'),
            array('course' => 'Trends, Networks, and Critical Thinking in the 21st Century Culture ',  'strand_id' => '2'),
            array('course' => 'Philippine Politics and Governance',  'strand_id' => '2'),
            array('course' => 'Community Engagement, Solidarity, and Citizenship',  'strand_id' => '2'),
            array('course' => 'Disciplines and Ideas in the Social Sciences ',  'strand_id' => '2'),
            array('course' => 'Disciplines and Ideas in the Applied Social Sciences',  'strand_id' => '2'),


            array('course' => 'Pre-Calculus',  'strand_id' => '3'),
            array('course' => 'Basic Calculus',  'strand_id' => '3'),
            array('course' => 'General Biology 1',  'strand_id' => '3'),
            array('course' => 'General Biology 2',  'strand_id' => '3'),
            array('course' => 'General Physics 1',  'strand_id' => '3'),
            array('course' => 'General Physics 2',  'strand_id' => '3'),
            array('course' => 'General Chemistry 1',  'strand_id' => '3'),
            array('course' => 'General Chemistry 2',  'strand_id' => '3'),


            array('course' => 'Humanities 1* ',  'strand_id' => '4'),
            array('course' => 'Humanities 2*',  'strand_id' => '4'),
            array('course' => 'Social Science 1** ',  'strand_id' => '4'),
            array('course' => 'Applied Economics',  'strand_id' => '4'),
            array('course' => 'Organization and Management ',  'strand_id' => '4'),
            array('course' => 'Disaster Readiness and Risk Reduction ',  'strand_id' => '4'),
            array('course' => 'Elective 1 (from any Track/Strand)***',  'strand_id' => '4'),
            array('course' => 'Elective 2 (from any Track/Strand)*** ',  'strand_id' => '4'),


            array('course' => 'Safety and First Aid', 'strand_id' => '5'),
            array('course' => 'Human Movement', 'strand_id' => '5'),
            array('course' => 'Fundamentals of Coaching', 'strand_id' => '5'),
            array('course' => 'Sports Officiating and Activity Management', 'strand_id' => '5'),
            array('course' => 'Fitness, Sports and Recreation Leadership', 'strand_id' => '5'),
            array('course' => 'Psychosocial Aspects of Sports and Exercise', 'strand_id' => '5'),
            array('course' => 'Fitness Testing and Basic Exercise Programming', 'strand_id' => '5'),

            
            array('course' => 'Creative Industries I: Arts and Design Appreciation and Production', 'strand_id' => '6'),
            array('course' => 'Creative Industries II: Performing Arts', 'strand_id' => '6'),
            array('course' => 'Physical and Personal Development in the Arts ', 'strand_id' => '6'),
            array('course' => 'Developing Filipino Identity in the Arts', 'strand_id' => '6'),
            array('course' => 'Integrating the Elements and Principles of Organization in the Arts ', 'strand_id' => '6'),
            array('course' => 'Leadership and Management in Different Arts Fields ', 'strand_id' => '6'),

            array('course' => 'Singing', 'strand_id' => '7'),
            array('course' => 'Solo Vocal Performance', 'strand_id' => '7'),
            array('course' => 'Live Sound Technician', 'strand_id' => '7'),


            array('course' => 'Acting', 'strand_id' => '8'),
            array('course' => 'Technical Lights Crew', 'strand_id' => '8'),
            array('course' => 'Props and Set Designing', 'strand_id' => '8'),
            array('course' => 'Scriptwriting', 'strand_id' => '8'),


            array('course' => 'Illustration', 'strand_id' => '9'),
            array('course' => 'Product Designing', 'strand_id' => '9'),
            array('course' => 'Production Designing', 'strand_id' => '9'),
            array('course' => 'Graphic Designing', 'strand_id' => '9'),

            array('course' => '2D Animation Artist', 'strand_id' => '10'),
            array('course' => '3D Animation Artist', 'strand_id' => '10'),
            array('course' => 'Web Designing', 'strand_id' => '10'),
            array('course' => 'Film Video Utility', 'strand_id' => '10'),
            array('course' => 'Game Arts Developing', 'strand_id' => '10'),
            array('course' => 'Photography', 'strand_id' => '10'),


            array('course' => 'Dancing', 'strand_id' => '11'),
            array('course' => 'Ballroom Dancing', 'strand_id' => '11'),
            array('course' => 'Dance Choreography', 'strand_id' => '11'),
            array('course' => 'Dance Instruction', 'strand_id' => '11'),

            array('course' => 'Hairdressing', 'strand_id' => '12'),
            array('course' => 'Tailoring', 'strand_id' => '12'),
            array('course' => 'Caregiving', 'strand_id' => '12'),
            array('course' => 'Food and Beverage Services', 'strand_id' => '12'),
            array('course' => 'Bread and Pastry Production', 'strand_id' => '12'),
            array('course' => 'Housekeeping', 'strand_id' => '12'),
            array('course' => 'Tour Guiding Services', 'strand_id' => '12'),
            array('course' => 'Tourism Promotion Services', 'strand_id' => '12'),
            array('course' => 'Attractions and Theme Parks Tourism', 'strand_id' => '12'),
            array('course' => 'Handicraft', 'strand_id' => '12'),

           
            array('course' => 'Computer Programming', 'strand_id' => '13'),
            array('course' => 'Medical Transcription', 'strand_id' => '13'),
            array('course' => 'Animation', 'strand_id' => '13'),

            
            array('course' => 'Agricrop Production : Horticulture', 'strand_id' => '14'),
            array('course' => 'Agricrop Production : Landscape Installation and Maintenance', 'strand_id' => '14'),
            array('course' => 'Agricrop Production : Organic Agriculture Production', 'strand_id' => '14'),
            array('course' => 'Agricrop Production : Pest Management', 'strand_id' => '14'),
            array('course' => 'Agricrop Production : Rice Machinery Operation', 'strand_id' => '14'),

            array('course' => 'Animal Production : Animal Production 11', 'strand_id' => '14'),
            array('course' => 'Animal Production : Artificial Insemination- Large Ruminants', 'strand_id' => '14'),
            array('course' => 'Animal Production : Artificial Insemination- Swine', 'strand_id' => '14'),
            array('course' => 'Animal Production : Slaughtering', 'strand_id' => '14'),

            array('course' => 'Fish Production : Fish Nursery Operation', 'strand_id' => '14'),
            array('course' => 'Fish Production : Fish or Shrimp Grow Out Operation', 'strand_id' => '14'),
            array('course' => 'Fish Production : Fishport/Wharf Operation', 'strand_id' => '14'),
            array('course' => 'Fish Production : Fish Processing', 'strand_id' => '14'),

            array('course' => 'Automotive Servicing', 'strand_id' => '15'),
            array('course' => 'Refrigeration and Air-Conditioning', 'strand_id' => '15'),
            array('course' => 'Consumer Electronics Servicing', 'strand_id' => '15'),
            array('course' => 'Electrical Installation and Maintenance', 'strand_id' => '15'),
            array('course' => 'Shielded Metal-Arc Welding', 'strand_id' => '15'),
            array('course' => 'Carpentry', 'strand_id' => '15'),
            array('course' => 'Plumbing', 'strand_id' => '15'),
            array('course' => 'Tile Setting', 'strand_id' => '15')

        );

        DB::table('tbl_courses')->insert($courses);
    }
}
