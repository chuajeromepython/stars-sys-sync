<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use Auth;


class Assessment extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'tbl_assessments';


    public static function saveAnswerKeys($answer_keys){

        $teacher_id = Teacher::where('user_id', Auth::user()->id)->value('id');

        $assessment = new Assessment;
        $assessment->title = $answer_keys['title'];;
        $assessment->date = $answer_keys['date'];;
        $assessment->assessment_type_id = $answer_keys['type'];
        $assessment->number_of_items = $answer_keys['items'];
        $assessment->period_id = $answer_keys['period'];
        $assessment->grade_level_id = $answer_keys['grade'];
        $assessment->subject_id = $answer_keys['subject'];
        $assessment->teacher_id = $teacher_id;
        $assessment->academic_year_id = AcademicYear::active()->id;
        $assessment->save();


        foreach ($answer_keys['keys'] as $key => $items) {
            foreach ($items['question'] as $identifier => $value) {
                if ($identifier == "Q") {

                    $question = new Question;
                    $question->question = $value;
                    $question->competency_id = $items['competency'];
                    $question->save();

                    $assessment_key = new AssessmentKey;
                    $assessment_key->item_number = $items['item_no'];
                    $assessment_key->assessment_id = $assessment->id;
                    $assessment_key->question_id = $question->id;
                    $assessment_key->save();

                }else{

                    $assessment_key = AssessmentKey::whereRaw(
                        'id = (select max(`id`) from tbl_assessment_keys)'
                    )->first();

                    $is_correct = (strtolower($identifier) == strtolower($items['answer'])) ? true : false;
                        
                    $option = new Option;
                    $option->option = $value;
                    $option->is_correct = $is_correct;
                    $option->question_id = $assessment_key->question_id;
                    $option->save();

                    $assessment_option = new AssessmentOption;
                    $assessment_option->assignment = $identifier;
                    $assessment_option->option_id =  $option->id;
                    $assessment_option->assessment_key_id = $assessment_key->id;
                    $assessment_option->save();

                }
            }
        }

        return $assessment;

    }
}
