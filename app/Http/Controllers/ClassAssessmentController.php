<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Auth;
use App\Models\Assessment;
use App\Models\AssessmentKey;
use App\Models\AssessmentOption;
use App\Models\AssessmentClass;
use App\Models\ClassAssessment;
use App\Models\CustomFunction;
use App\Models\Question;
use App\Models\Teacher;
use App\Models\TeacherClass;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\StudentClass;
use App\Models\StudentAnswer;

class ClassAssessmentController extends Controller
{
    
    public function show(ClassAssessment $class_assessment)
    {   

        $assessment = Assessment::find($class_assessment->assessment_id);
        $assessment_details = CustomFunction::getAssessmentDetails($assessment->id);

        if($assessment_details->type == "Periodical"){
            $key = "Periodical Exams";
            $link = "/periodicals";
        }else{
            $key = "Summative";
            $link = "/summatives";
        }
        $page = [
            'name'      =>  'Assessments',
            'title'     =>  'Class Assessment',
            'crumb'     =>  array(
                "Assessments" => "",
                $key => $link,
                "Class Assessment" => "/class_assessments/".$class_assessment->id
            )
        ];

        $assessment_keys = CustomFunction::getAssessmentKeys($class_assessment->assessment_id);
        $result = CustomFunction::updateAssessmentResult($class_assessment->id);
        $students = StudentScore::select(
                'student_id', 'score', 'first_name', 'last_name', 'middle_name', 'lrn'
            )->join('tbl_students', 'tbl_student_scores.student_id', 'tbl_students.id' )
            ->join('tbl_users', 'tbl_students.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('class_assessment_id',$class_assessment->id)
            ->get();
            
        return view('class_assessments.index', compact(
            'page', 'assessment_keys', 'students', 'class_assessment'
        ));
    }
    
    public function results(ClassAssessment $class_assessment){   

        $results = ClassAssessment::getResults($class_assessment->id);
        $assessment = CustomFunction::getAssessmentDetails($class_assessment->assessment_id);
        $class = CustomFunction::getClassDetails($class_assessment->class_id);

        return view('class_assessments.reports.result',
            compact('results', 'assessment', 'class', 'class_assessment')
        );
    }




    public function score_analysis(ClassAssessment $class_assessment){

        $results = ClassAssessment::getScoreAnalysis($class_assessment->id);
        $assessment = CustomFunction::getAssessmentDetails($class_assessment->assessment_id);
        $class = CustomFunction::getClassDetails($class_assessment->class_id);
        
        return view('class_assessments.reports.score_analysis',
            compact('results', 'assessment', 'class', 'class_assessment')
        );
        


    }

    public function item_analysis(ClassAssessment $class_assessment){

        $results = ClassAssessment::getItemAnalysis($class_assessment->id);
        $assessment = CustomFunction::getAssessmentDetails($class_assessment->assessment_id);
        $class = CustomFunction::getClassDetails($class_assessment->class_id);
        

        return view('class_assessments.reports.item_analysis',
            compact('results', 'assessment', 'class', 'class_assessment')
        );


    }

    public function discrimination_index(ClassAssessment $class_assessment){

        $results = ClassAssessment::getDisriminationIndex($class_assessment->id);
        $assessment = CustomFunction::getAssessmentDetails($class_assessment->assessment_id);
        $class = CustomFunction::getClassDetails($class_assessment->class_id);
        

        return view('class_assessments.reports.discrimination_index',
            compact('results', 'assessment', 'class', 'class_assessment')
        );

    }



    public function download_results(ClassAssessment $class_assessment){   

        $results = ClassAssessment::getResults($class_assessment->id);
        $assessment = CustomFunction::getAssessmentDetails($class_assessment->assessment_id);
        $class = CustomFunction::getClassDetails($class_assessment->class_id);
        
        $reader = new Xlsx();
        $spreadsheet = $reader->load( public_path('/templates/Template-Assessment-Result.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Bahnschrift');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $row = 9;
        $count = 1;

        $teacher = $class->first_name.' '.$class->middle_name.' '.$class->last_name;
        $sheet->setCellValue('C1' ,$assessment->assessment);
        $sheet->setCellValue('C2' ,$teacher);
        $sheet->setCellValue('C4' ,$assessment->level);
        $sheet->setCellValue('L2' ,$assessment->date);
        $sheet->setCellValue('L3' ,$assessment->number_of_items);
        $sheet->setCellValue('L4' ,$assessment->subject);
        $sheet->setCellValue('G3' ,$assessment->period);
        $sheet->setCellValue('G4' ,$class->section);

        $col = "O";

        for ($i=1; $i <= $assessment->number_of_items ; $i++) { 
            $sheet->setCellValue($col.'8', $i);
            $col++;
        }

        foreach ($results as $key => $student) {
            $sheet->setCellValue('A'.$row, $count);

            $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode("0");
            $sheet->mergeCells("B".$row.":C".$row."");
            $sheet->mergeCells("D".$row.":H".$row."");

            $sheet->setCellValue('B'.$row, $student['lrn']);
            $sheet->setCellValue('D'.$row, $student['name']);
            $sheet->setCellValue('I'.$row, $student['gender']);
            $sheet->setCellValue('J'.$row, $student['score']);
            $sheet->setCellValue('K'.$row, $student['percentage']);
            $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode("0.00");
            $sheet->setCellValue('L'.$row, $student['proficiency']);
            $sheet->setCellValue('M'.$row, $student['achievement']);

            $col = "O";

            foreach ($student['answers'] as $item_number => $answer) {
                $sheet->setCellValue($col.$row, $answer['is_correct']);
                $col++;
            }
            $row++;
            $count++;
        }

        $file_name = $assessment->assessment.' - Result';

        $spreadsheet->getProperties()
            ->setTitle('STARS: SYSTEM GENERATED REPORT')
            ->setSubject('STARS: SYSTEM GENERATED REPORT')
            ->setKeywords('PAYROLL') //Tags
            ->setCategory('STARS Report')
            ->setDescription('This is a system generated report.') //Comment
            ->setCreator('Students Test Analysis Records System') //Author
            ->setLastModifiedBy('Developer - Glenn Nerrie A. Afurong');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$file_name.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); 
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public'); 
         
        /* Download/Export as xlsx */
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');

        
    }


    public function download_score_analysis(ClassAssessment $class_assessment){   

        $results = ClassAssessment::getScoreAnalysis($class_assessment->id);
        $assessment = CustomFunction::getAssessmentDetails($class_assessment->assessment_id);
        $class = CustomFunction::getClassDetails($class_assessment->class_id);
        
        $reader = new Xlsx();
        $spreadsheet = $reader->load( public_path('/templates/Template-Assessment-Score-Analysis.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();
      
        $spreadsheet->getDefaultStyle()->getFont()->setName('Bahnschrift');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        
        $row = 9;
        $teacher = $class->first_name.' '.$class->middle_name.' '.$class->last_name;
        $sheet->setCellValue('C1' ,$assessment->assessment);
        $sheet->setCellValue('C2' ,$teacher);
        $sheet->setCellValue('C4' ,$assessment->level);
        $sheet->setCellValue('M2' ,$assessment->date);
        $sheet->setCellValue('M3' ,$assessment->number_of_items);
        $sheet->setCellValue('M4' ,$assessment->subject);
        $sheet->setCellValue('H3' ,$assessment->period);
        $sheet->setCellValue('H4' ,$class->section);


        $sheet->setCellValue('D8' ,$results['mastery'].' %');
        $sheet->setCellValue('D9' ,$results['mean'].' %');
        $sheet->setCellValue('D10' ,$results['mps'].' %');
        $sheet->setCellValue('D11' ,$results['sd'].' %');

        $sheet->setCellValue('L8' ,$results['proficiency'].' %');
        $sheet->setCellValue('L9' ,$results['hpg']);
        $sheet->setCellValue('L10' ,$results['apg']);
        $sheet->setCellValue('L11' ,$results['lpg']);

        $row = 14;    
        foreach($results['scores'] as $score => $data){
            $sheet->setCellValue('A'.$row, $score);
            $sheet->mergeCells("A".$row.":C".$row."");

            $sheet->setCellValue('D'.$row, $data['count']);
            $sheet->mergeCells("D".$row.":F".$row."");

            $sheet->setCellValue('G'.$row, $data['x']);
            $sheet->mergeCells("G".$row.":I".$row."");

            $sheet->setCellValue('J'.$row, $data['xbar']);
            $sheet->mergeCells("J".$row.":L".$row."");

            $sheet->setCellValue('M'.$row, $data['fxb']);
            $sheet->mergeCells("M".$row.":O".$row."");
            $row++;
        }

        $file_name = $assessment->assessment.' - Score Analysis';

        $spreadsheet->getProperties()
            ->setTitle('STARS: SYSTEM GENERATED REPORT')
            ->setSubject('STARS: SYSTEM GENERATED REPORT')
            ->setKeywords('PAYROLL') //Tags
            ->setCategory('STARS Report')
            ->setDescription('This is a system generated report.') //Comment
            ->setCreator('Students Test Analysis Records System') //Author
            ->setLastModifiedBy('Developer - Glenn Nerrie A. Afurong');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$file_name.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); 
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public'); 
         
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');


    }


    public function download_item_analysis(ClassAssessment $class_assessment){  

        $results = ClassAssessment::getItemAnalysis($class_assessment->id);
        $assessment = CustomFunction::getAssessmentDetails($class_assessment->assessment_id);
        $class = CustomFunction::getClassDetails($class_assessment->class_id);
        
        $reader = new Xlsx();
        $spreadsheet = $reader->load( public_path('/templates/Template-Assessment-Item-Analysis.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();
      
        $spreadsheet->getDefaultStyle()->getFont()->setName('Bahnschrift');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        
        $teacher = $class->first_name.' '.$class->middle_name.' '.$class->last_name;
        $sheet->setCellValue('C1' ,$assessment->assessment);
        $sheet->setCellValue('C2' ,$teacher);
        $sheet->setCellValue('C4' ,$assessment->level);
        $sheet->setCellValue('M2' ,$assessment->date);
        $sheet->setCellValue('M3' ,$assessment->number_of_items);
        $sheet->setCellValue('M4' ,$assessment->subject);
        $sheet->setCellValue('H3' ,$assessment->period);
        $sheet->setCellValue('H4' ,$class->section);

        $row = 9;  


        foreach($results as $item_number => $result){
            $sheet->setCellValue('A'.$row, $item_number);
            $sheet->setCellValue('B'.$row, $result['correct_answers']);
            $sheet->mergeCells("B".$row.":C".$row."");
            $sheet->setCellValue('D'.$row, $result['correct_option']);
            $sheet->mergeCells("D".$row.":E".$row."");
            $sheet->setCellValue('F'.$row, $result['percentage'].' %');
            $sheet->mergeCells("F".$row.":G".$row."");
            $sheet->setCellValue('H'.$row, $result['difficulty']);
            $sheet->mergeCells("H".$row.":K".$row."");
            $col = "L";
            foreach ($result['options'] as $letter => $count) {
                $sheet->setCellValue($col.$row, $count);
                $col++;
            }
            $row++;
        }

        $file_name = $assessment->assessment.' - Item Analysis';

        $spreadsheet->getProperties()
            ->setTitle('STARS: SYSTEM GENERATED REPORT')
            ->setSubject('STARS: SYSTEM GENERATED REPORT')
            ->setKeywords('PAYROLL') //Tags
            ->setCategory('STARS Report')
            ->setDescription('This is a system generated report.') //Comment
            ->setCreator('Students Test Analysis Records System') //Author
            ->setLastModifiedBy('Developer - Glenn Nerrie A. Afurong');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$file_name.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); 
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public'); 
         
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');


    }


    public function download_discrimination_index(ClassAssessment $class_assessment){  

        $results = ClassAssessment::getDisriminationIndex($class_assessment->id);
        $assessment = CustomFunction::getAssessmentDetails($class_assessment->assessment_id);
        $class = CustomFunction::getClassDetails($class_assessment->class_id);
        
        $reader = new Xlsx();
        $spreadsheet = $reader->load( public_path('/templates/Template-Assessment-Discrimination-Index.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();
      
        $spreadsheet->getDefaultStyle()->getFont()->setName('Bahnschrift');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        
        $teacher = $class->first_name.' '.$class->middle_name.' '.$class->last_name;
        $sheet->setCellValue('C1' ,$assessment->assessment);
        $sheet->setCellValue('C2' ,$teacher);
        $sheet->setCellValue('C4' ,$assessment->level);
        $sheet->setCellValue('M2' ,$assessment->date);
        $sheet->setCellValue('M3' ,$assessment->number_of_items);
        $sheet->setCellValue('M4' ,$assessment->subject);
        $sheet->setCellValue('H3' ,$assessment->period);
        $sheet->setCellValue('H4' ,$class->section);

        $row = 9;  

        foreach($results as $item_number => $result){
            
            $sheet->setCellValue('A'.$row, $item_number);
            $sheet->setCellValue('B'.$row, $result['lpg']);
            $sheet->setCellValue('C'.$row, $result['apg']);
            $sheet->setCellValue('D'.$row, $result['hpg']);
            $sheet->setCellValue('E'.$row, $result['index']);
            $sheet->setCellValue('F'.$row, $result['classification']);
            $sheet->mergeCells("F".$row.":I".$row."");
            $sheet->setCellValue('J'.$row, $result['recommendation']);
            $sheet->mergeCells("j".$row.":O".$row."");
            $row++;
        }

        $file_name = $assessment->assessment.' - Discrimination Index';

        $spreadsheet->getProperties()
            ->setTitle('STARS: SYSTEM GENERATED REPORT')
            ->setSubject('STARS: SYSTEM GENERATED REPORT')
            ->setKeywords('PAYROLL') //Tags
            ->setCategory('STARS Report')
            ->setDescription('This is a system generated report.') //Comment
            ->setCreator('Students Test Analysis Records System') //Author
            ->setLastModifiedBy('Developer - Glenn Nerrie A. Afurong');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$file_name.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); 
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public'); 
         
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');

    }
    


}
