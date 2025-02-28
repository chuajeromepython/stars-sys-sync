<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;


class ClassAssessment extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = "tbl_class_assessments";


    public static function getResults($class_assessment_id){

        $path = storage_path() . '\app\public\res-'.$class_assessment_id.'.json'; 
        $results = json_decode(file_get_contents($path), true);

        return $results;
    }

    public static function getScoreAnalysis($class_assessment_id){

        $data = ClassAssessment::getResults($class_assessment_id);
        $class_assessment = ClassAssessment::find($class_assessment_id);
        $assessment = Assessment::find($class_assessment->assessment_id);
        
        $tally = array();
        $sum_of_x = 0;
        $sum_of_xbar = 0;
        $sum_of_fxb = 0;
        $sum_of_count = 0;
        $sum_of_count = 0;
        $sum_of_hpg = 0;
        $sum_of_apg = 0;
        $sum_of_lpg = 0;

        $result = array();

        for ($score=1; $score <= $assessment->number_of_items ; $score++) { 

            $count = 0;
            foreach ($data as $key => $student) {
                if($student['score'] == $score){
                    $count++;
                }
            }
            $x = number_format($score * $count, 2, '.', '');
            $sum_of_x += $x;
            $sum_of_count += $count;

            $tally[$score] = array(
                "x" => $x,
                "count" => $count
            );

        }

        foreach ($data as $key => $student) {
            if($student['proficiency'] == "AP"){
                $sum_of_apg += 1;
            }
            if($student['proficiency'] == "LP"){
                $sum_of_lpg += 1;
            }
            if($student['proficiency'] == "HP"){
                $sum_of_hpg += 1;
            }
        }
        
        $mean = ($sum_of_count == 0) ? 0 :$sum_of_x / $sum_of_count;

        for ($score=1; $score <= $assessment['number_of_items'] ; $score++) { 
            
            $xbar = number_format(($score-$mean)*($score-$mean), 2, '.', '');
            $fxb = number_format($tally[$score]['count'] * $xbar, 2, '.', '');

            $tally[$score]['xbar'] = $xbar;
            $tally[$score]['fxb'] = $fxb;

            $sum_of_xbar += $xbar;
            $sum_of_fxb += $fxb;
        }
        
        $sd = ($sum_of_count == 0) 
            ? 0 
            : number_format(sqrt($sum_of_fxb / $sum_of_count - 1), 2, '.', '');

        $proficiency = ($sum_of_count == 0) 
            ? 0 
            : number_format(($sum_of_apg + $sum_of_hpg) / $sum_of_count *100 , 2, '.', '');

        $mastery = ($sum_of_count == 0) 
            ? 0 
            : number_format($sum_of_x / ($sum_of_count * $assessment['number_of_items'])*100, 2, '.', '');
            
        $results = array(
            "mean" => number_format($mean, 2, '.', ''),
            "mps" => number_format( (($mean/$assessment['number_of_items'])*100), 2, '.', ''),
            "sd" => $sd,
            "hpg" => $sum_of_hpg,
            "apg" => $sum_of_apg,
            "lpg" => $sum_of_lpg,
            "proficiency" => $proficiency,
            "mastery" => $mastery,
            "scores" => $tally,
            "id" => $class_assessment_id
        );

        return $results;
    }


    public static function getItemAnalysis($class_assessment_id){

        $data = ClassAssessment::getResults($class_assessment_id);
        $class_assessment = ClassAssessment::find($class_assessment_id);
        $assessment = Assessment::find($class_assessment->assessment_id);
        
        $results = array();
        $total_students = sizeof($data);

        for ($item_number=1; $item_number <= $assessment->number_of_items ; $item_number++) { 

            $correct_answers = 0;
            $a = 0; $b=0; $c=0; $d=0;

            foreach ($data as $key => $student) {
                foreach ($student['answers'] as $key => $answer) {
                    if($key == $item_number){
                        if($answer['is_correct'] == 1){
                            $correct_answers +=1;
                        }
                        if($answer['answer'] == "A"){ $a +=1; }
                        if($answer['answer'] == "B"){ $b +=1; }
                        if($answer['answer'] == "C"){ $c +=1; }
                        if($answer['answer'] == "D"){ $d +=1; }
                    }
                }
            }

            $percentage = number_format(($correct_answers / $total_students)*100, 2, '.', '');

            if($percentage >= 81){
                $difficulty = 'Very Easy';
            }elseif($percentage >= 61 && $percentage <= 81){
                $difficulty = 'Easy';
            }elseif($percentage >= 41 && $percentage <= 61){
                $difficulty = 'Average';
            }elseif($percentage >= 21 && $percentage <= 41){
                $difficulty = 'Difficult';
            }elseif($percentage >= 0 && $percentage <= 21){
                $difficulty = 'Very Difficult';
            }

            $options = array(
                "A" => $a,
                "B" => $b,
                "C" => $c,
                "D" => $d
            );

            $correct_option =  AssessmentOption::select('assignment')
                ->join('tbl_assessment_keys', 'tbl_assessment_options.assessment_key_id', 'tbl_assessment_keys.id')
                ->join('tbl_options', 'tbl_assessment_options.option_id', 'tbl_options.id')
                ->where('tbl_assessment_keys.assessment_id', $class_assessment->assessment_id)
                ->where('tbl_assessment_keys.item_number', $item_number)
                ->where('tbl_options.is_correct', 1)
                ->value('assignment');


            $results[$item_number] = array(
                'correct_answers' => $correct_answers,
                'percentage' => $percentage,
                'difficulty' => $difficulty,
                'correct_option' => $correct_option,
                'options' => $options,
            );

        }

        return $results;
    }

    public static function getDisriminationIndex($class_assessment_id){

        $data = ClassAssessment::getResults($class_assessment_id);
        $class_assessment = ClassAssessment::find($class_assessment_id);
        $assessment = Assessment::find($class_assessment->assessment_id);
        $results = array();
        $sum_of_hpg = 0;
        $sum_of_apg = 0;
        $sum_of_lpg = 0;


         foreach ($data as $key => $student) {
            if($student['proficiency'] == "AP"){
                $sum_of_apg += 1;
            }
            if($student['proficiency'] == "LP"){
                $sum_of_lpg += 1;
            }
            if($student['proficiency'] == "HP"){
                $sum_of_hpg += 1;
            }
        }



        for ($item_number=1; $item_number <= $assessment->number_of_items ; $item_number++) {

            $correct_hpg = 0;
            $correct_apg = 0;
            $correct_lpg = 0;

            foreach ($data as $key => $student) {
                if($student['proficiency'] == "HP" && $student['answers'][$item_number]['is_correct'] == 1){
                    $correct_hpg+= 1;
                }
                if($student['proficiency'] == "AP" && $student['answers'][$item_number]['is_correct'] == 1){
                    $correct_apg+= 1;
                }
                if($student['proficiency'] == "LP" && $student['answers'][$item_number]['is_correct'] == 1){
                    $correct_lpg+= 1;
                }
            }

            $index = number_format(($correct_hpg/$sum_of_hpg)-($correct_lpg/$sum_of_lpg), 2, '.', '');

            $percentage = $index * 100;
            if($percentage < 19){
                $classification = "Poor Item";
                $recommendation = "Modify the question and choices.";
            }elseif($percentage >= 20 && $percentage <= 29){
                $classification = "Fair Item";
                $recommendation = "Rephrase the question.";
            }elseif($percentage >= 30 && $percentage <= 39){
                $classification = "Good Item";
                $recommendation = "Revise the choices (ABCD).";
            }elseif($percentage >= 40 && $percentage <= 100){
                $classification = "Very Good Item";
                $recommendation = "Satisfactory / Accept the question.";
            }

            $results[$item_number] = array(
                'hpg' => $correct_hpg,
                'apg' => $correct_apg,
                'lpg' => $correct_lpg,
                'index' => $index,
                'classification' => $classification,
                'recommendation' => $recommendation,
            );

        }

        return $results;



    }
}
