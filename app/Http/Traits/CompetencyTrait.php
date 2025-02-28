<?php

namespace App\Http\Traits;

use App\Models\Competency;
use App\Models\GradeLevel;
use App\Models\Period;
use App\Models\Subject;
use App\Models\Week;

trait CompetencyTrait {
    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function uploadSubject(&$errors, $row, $line, $week_id = null, $period_id = null, $error = array())
    {
        $data = array(
            "code" => $row[0],
            "description" => $row[1],
            "subject" => $row[2],
            "grade_level" => $row[3],
            "period" => $row[4],
            "week" => $row[5]
        );

        $this->check_competency_if_exist($data, $error, $line);

        $grade_level = $this->validate_grade_level($data, $error, $line);

        $week_id = $this->validate_week($data, $error, $line);

        $period_id = $this->validate_period($data, $error, $line);

        if($data['subject'] == null){
            $error[] = 'Error on row '.$line.' : Subject cannot be null.';
        }else{
            $subject = Subject::where('title', $data['subject'])->get();
            if($subject->count() == 0){
                $error[] = 'Error on row '.$line.' : Invalid Subject.';
            }
        }

        if(sizeOf($error) == 0){
            $competency = new Competency;
            $competency->code = $data['code'];
            $competency->description = $data['description'];
            $competency->grade_level_id = $grade_level[0]->id;
            $competency->subject_id = $subject[0]->id;
            $competency->week_id = $week_id;
            $competency->period_id = $period_id;
            $competency->save();
        }else{
            $errors[] = $error;
        }
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function uploadWithSubjectComponent(&$errors, $row, $line, $week_id = null, $period_id = null, $error = array())
    {
        $data = array(
            "code" => $row[0],
            "description" => $row[1],
            "subject" => $row[2],
            "subject_component" => $row[3],
            "grade_level" => $row[4],
            "period" => $row[5],
            "week" => $row[6]
        );
        
        $this->check_competency_if_exist($data, $error, $line);

        $grade_level = $this->validate_grade_level($data, $error, $line);

        $week_id = $this->validate_week($data, $error, $line);

        $period_id = $this->validate_period($data, $error, $line);

        if($data['subject'] == null){

            $error[] = 'Error on row '.$line.' : Subject cannot be null.';
        }else{

            $subject = Subject::where('title', $data['subject'])->get();
            if($subject->count() == 0){

                $error[] = 'Error on row '.$line.' : Invalid Subject.';
            }

            if($subject[0]->subject_components->count() > 0) {

                $sub_comp = $subject[0]->subject_components->toArray();
                $searchKey = 'name';
                $searchValue = $data['subject_component'];

                $subject_component_id = current(array_filter($sub_comp, function ($item) use ($searchKey, $searchValue) {
                    return isset($item[$searchKey]) && $item[$searchKey] === $searchValue;
                }));

                if(!$subject_component_id)
                    $error[] = 'Error on row '.$line.' : Subject Component not found!.';
            }
        }

        if(sizeOf($error) == 0){
            
            $competency = new Competency;
            $competency->code = $data['code'];
            $competency->description = $data['description'];
            $competency->grade_level_id = $grade_level[0]->id;
            $competency->subject_id = $subject[0]->id;
            $competency->subject_component_id = $subject_component_id['id'];
            $competency->week_id = $week_id;
            $competency->period_id = $period_id;
            $competency->save();
        }else{
            $errors[] = $error;
        }
    }

    public function check_competency_if_exist($data, &$error, $line)
    {
        $is_code_exist = Competency::where('code', $data['code'])->get();
        ($is_code_exist->count() > 0) ? $error[] = 'Error on row '.$line.' : Code already exist' : '';
    }

    public function validate_grade_level($data, &$error, $line)
    {
        if($data['grade_level'] == null){
            $error[] = 'Error on row '.$line.' : Grade Level cannot be null.';
        }else{
            $grade_level = GradeLevel::where('level', $data['grade_level'])->get();
            if($grade_level->count() == 0){
                $error[] = 'Error on row '.$line.' : Invalid Grade Level.';
            }
            return $grade_level;
        }
    }

    public function validate_week($data, &$error, $line)
    {
        if($data['week'] != null){
            $week = Week::where('week', $data['week'])->get();
            if($week->count() == 0){
                $error[] = 'Error on row '.$line.' : Invalid Week.';
            }else{
                return $week[0]->id;
            }
        }
    }

    public function validate_period($data, &$error, $line)
    {
        if($data['period'] != null){
            $period = Period::where('period', $data['period'])->get();
            if($period->count() == 0){
                $error[] = 'Error on row '.$line.' : Invalid Period.';
            }else{
                return $period[0]->id;
            }
        }
    }
}