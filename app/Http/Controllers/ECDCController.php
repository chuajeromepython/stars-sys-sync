<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\CustomFunction;
use App\Models\ECDC;
use App\Models\ECDCCompetency;
use App\Models\ECDCDomain;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentClassroom;
use App\Models\StudentECDC;
use App\Models\Teacher;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ECDCController extends Controller
{
    public function index()
    {

        $page = [
            'name' => 'Assessment',
            'sub_name' => 'ECDC',
            'title' => 'ECDC',
            'crumb' => [
                'Assessment' => '#',
                'ECDC' => '/ecdcs',
            ],
        ];

        $domains = ECDCDomain::all();
        $competencies = [];

        foreach ($domains as $key => $domain) {
            $competencies[$domain->id] = ECDCCompetency::where('domain_id', $domain->id)->get();
        }
        $academic_year = AcademicYear::active();

        $classrooms = CustomFunction::getClassrooms();

        // ECDC Initial Query
        $ecdc = ECDC::select(
            'level', 'section', 'tbl_ecdcs.classroom_id'
        )->join('tbl_classrooms', 'tbl_ecdcs.classroom_id', 'tbl_classrooms.id')
            ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_sections', 'tbl_classrooms.section_id', 'tbl_sections.id')
            ->where('teacher_id', Teacher::where('user_id', Auth::user()->id)->value('id'))
            ->where('tbl_ecdcs.academic_year_id', $academic_year->id)
            ->where('source', 1);

        if (array_key_exists('Kinder', $classrooms)) {
            foreach ($classrooms['Kinder'] as $key => $classroom) {

                $bosy = $ecdc->where('classroom_id', $classroom['classroom_id'])
                    ->where('tbl_ecdcs.period', ECDC::BOSY)->count();
                $mosy = $ecdc->where('classroom_id', $classroom['classroom_id'])
                    ->where('tbl_ecdcs.period', ECDC::MOSY)->count();
                $eosy = $ecdc->where('classroom_id', $classroom['classroom_id'])
                    ->where('tbl_ecdcs.period', ECDC::EOSY)->count();

                $classrooms['Kinder'][$key]['details'] = [
                    'bosy' => $bosy,
                    'eosy' => $eosy,
                    'mosy' => $mosy,
                ];
            }
        } else {
            // $classrooms["Kinder"][0] = [
            //     "classroom_id" => 463,
            //     "section" => "EMILIO JACINTO",
            //     "section_id" => 14,
            //     "advisor" => "JACKLYN CLIMACO",
            //     "subject" => "Filipino",
            //     "classes" => 2,
            //     "is_advisory" => 1,
            //     "details" => array(
            //         "bosy" => 0,
            //         "eosy" => 0,
            //         "mosy" => 0,
            //     )
            // ];
            return redirect()->to(url()->previous())->withErrors(['error' => 'No classroom for kinder found!']);
        }

        return view('ecdc.index', compact(
            'page', 'classrooms', 'academic_year'
        ));

    }

    public function update(Request $request)
    {

        $error = [];
        if ($request->date == null) {
            $error[] = 'Date cannot be null.';
        }
        if ($request->period > 3) {
            $error[] = 'Invalid Period.';
        }

        if (count($error) == 0) {

            $ecdc = ECDC::find($request->id);
            $ecdc->date = $request->date;
            $ecdc->period = $request->period;
            $ecdc->save();

            return back()->with('success', 'ECDC Successfully Updated');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => $error]);
        }

    }

    public function create(Classroom $classroom)
    {

        $classroom = CustomFunction::getClassroomDetails($classroom->id);

        $page = [
            'name' => 'Assessment',
            'sub_name' => 'ECDC',
            'title' => 'ECDC : '.strtoupper($classroom->level).'-'.$classroom->section.': New',
            'crumb' => [
                'Assessment' => '#',
                'ECDC' => '/ecdcs',
                'Encode New' => '/ecdcs/'.$classroom->id.'/create',

            ],
        ];

        $students = StudentClassroom::select(
            'student_id', 'lrn',
            'first_name', 'last_name', 'middle_name'
        )
            ->join('tbl_students', 'tbl_student_classrooms.student_id', 'tbl_students.id')
            ->join('tbl_users', 'tbl_students.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('classroom_id', $classroom->id)->get();

        $get_domains = ECDCDomain::all();
        $domains = [];
        $colors = ['red', 'orange', 'yellow', 'green', 'primary', 'info', 'purple'];

        foreach ($get_domains as $key => $domain) {
            $domains[$domain->id] = [
                'domain' => $domain->domain,
                'color' => $colors[$key],
                'competencies' => ECDCCompetency::where('domain_id', $domain->id)->get(),

            ];
        }

        return view('ecdc.create', compact(
            'page', 'students', 'classroom', 'domains'
        ));
    }

    public function store(Request $request)
    {
        // dd($request);
        DB::beginTransaction();
        try {

            $academic_year = AcademicYear::active();

            $existing = StudentECDC::join(
                'tbl_ecdcs', 'tbl_student_ecdcs.ecdc_id', 'tbl_ecdcs.id'
            )->where([
                'student_id' => $request->student_id,
                'period' => $request->period,
                'academic_year_id' => $academic_year->id,
                'classroom_id' => $request->classroom_id,
            ])->count();

            if ($existing > 0) {
                return redirect()->to(url()->previous())->withErrors(['error' => 'Oops. A Student with same ECDC period already exist']);
            } //

            $ecdc = new ECDC;

            $ecdc->date = $request->date;
            $ecdc->period = $request->period;
            $ecdc->academic_year_id = $academic_year->id;
            $ecdc->classroom_id = $request->classroom_id;
            $ecdc->source = 2;
            $ecdc->teacher_id = Teacher::where('user_id', Auth::user()->id)->value('id');
            $ecdc->created_at = Carbon::now('Asia/Manila');
            $ecdc->save();

            $competencies = ECDCCompetency::all();

            foreach ($competencies as $key => $competency) {

                $score = isset($request->score[$competency->id]) ? $request->score[$competency->id] : 0;

                $student_ecdc = new StudentECDC;
                $student_ecdc->student_id = $request->student_id;
                $student_ecdc->ecdc_id = $ecdc->id;
                $student_ecdc->ecdc_competency_id = $competency->id;
                $student_ecdc->score = $score;
                $student_ecdc->save();

            }

            DB::commit();
            $result = true;
            // Write Result
            ECDC::getResults($ecdc->id);

        } catch (Exception $e) {

            DB::rollBack();
            $result = $e->getMessage();

        }

        if ($result === true) {
            return redirect('/ecdcs/classroom/'.$request->classroom_id)->with('success', 'ECDC Successfully Encoded');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => $result]);
        }

    }

    public function show(Classroom $classroom)
    {

        $classroom = CustomFunction::getClassroomDetails($classroom->id);

        $page = [
            'name' => 'Assessment',
            'sub_name' => 'ECDC',
            'title' => 'ECDC : '.$classroom->level.'-'.$classroom->section,
            'crumb' => [
                'Assessment' => '#',
                'ECDC' => '/ecdcs',
            ],
        ];

        $academic_year = AcademicYear::active();
        $ecdcs = ECDC::select(
            'tbl_ecdcs.id as id',
            'tbl_ecdcs.created_at as created_at', 'period',
            'date', 'level', 'section', 'tbl_ecdcs.classroom_id'
        )->join('tbl_classrooms', 'tbl_ecdcs.classroom_id', 'tbl_classrooms.id')
            ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_sections', 'tbl_classrooms.section_id', 'tbl_sections.id')
            ->where('teacher_id', Teacher::where('user_id', Auth::user()->id)->value('id'))
            ->where('tbl_ecdcs.academic_year_id', $academic_year->id)
            ->where('classroom_id', $classroom->id)
            ->where('source', 1) // Uploaded
            ->get();

        $get_students = StudentClassroom::select(
            'student_id', 'lrn',
            'first_name', 'last_name', 'middle_name'
        )
            ->join('tbl_students', 'tbl_student_classrooms.student_id', 'tbl_students.id')
            ->join('tbl_users', 'tbl_students.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('classroom_id', $classroom->id)->get();

        $students = [];

        foreach ($get_students as $key => $student) {

            $filter = [
                'academic_year_id' => $academic_year->id,
                'classroom_id' => $classroom->id,
                'student_id' => $student->student_id,
            ];

            $has_bosy = StudentECDC::getECD($filter)->where('period', ECDC::BOSY)->get();
            $has_mosy = StudentECDC::getECD($filter)->where('period', ECDC::MOSY)->get();
            $has_eosy = StudentECDC::getECD($filter)->where('period', ECDC::EOSY)->get();

            $students[$student->lrn] = [
                'id' => $student->student_id,
                'name' => $student->last_name.', '.$student->first_name.' '.$student->middle_name,
                'has_eosy' => ($has_eosy->count() > 0) ? 1 : 0,
                'has_mosy' => ($has_mosy->count() > 0) ? 1 : 0,
                'has_bosy' => ($has_bosy->count() > 0) ? 1 : 0,
            ];
        }

        return view('ecdc.show', compact(
            'page', 'ecdcs', 'students', 'classroom'
        ));
    }

    public function card(Classroom $classroom, Student $student)
    {
        $classroom = CustomFunction::getClassroomDetails($classroom->id);
        $page = [
            'name' => 'Assessment',
            'sub_name' => 'ECDC',
            'title' => 'ECDC : '.$classroom->level.'-'.$classroom->section.' :'.$student->lrn,
            'crumb' => [
                'Assessment' => '#',
                'ECDC' => '/ecdcs',
                'Classroom' => '/ecdcs/classroom/'.$classroom->id,

            ],
        ];

        $academic_year = AcademicYear::active();

        $filter = [
            'academic_year_id' => $academic_year->id,
            'classroom_id' => $classroom->id,
            'student_id' => $student->id,
        ];

        $bosy_id = StudentECDC::getECD($filter)->where('period', ECDC::BOSY)->groupBy('ecdc_id')->value('ecdc_id');
        $mosy_id = StudentECDC::getECD($filter)->where('period', ECDC::MOSY)->groupBy('ecdc_id')->value('ecdc_id');
        $eosy_id = StudentECDC::getECD($filter)->where('period', ECDC::EOSY)->groupBy('ecdc_id')->value('ecdc_id');

        $bosy_result = ($bosy_id) ? ECDC::getJsonResult($bosy_id)[$student->id] : 'No Record';
        $mosy_result = ($mosy_id) ? ECDC::getJsonResult($mosy_id)[$student->id] : 'No Record';
        $eosy_result = ($eosy_id) ? ECDC::getJsonResult($eosy_id)[$student->id] : 'No Record';

        $results = [
            'bosy' => $bosy_result,
            'mosy' => $mosy_result,
            'eosy' => $eosy_result,
        ];

        $domains = ECDCDomain::all();
        $domain_competencies = [];

        foreach ($domains as $key => $domain) {
            $domain_competencies[$domain->id] = [
                'domain' => $domain->domain,
                'competencies' => ECDCCompetency::where('domain_id', $domain->id)->get(),
            ];
        }
        $periods = ['bosy', 'mosy', 'eosy'];

        return view('ecdc.card', compact(
            'page', 'student', 'classroom',
            'results', 'domains', 'domain_competencies',
            'periods'
        ));
    }

    public function front(Classroom $classroom, Student $student)
    {

        $academic_year = AcademicYear::active();

        $filter = [
            'academic_year_id' => $academic_year->id,
            'classroom_id' => $classroom->id,
            'student_id' => $student->id,
        ];

        $bosy_id = StudentECDC::getECD($filter)->where('period', ECDC::BOSY)->groupBy('ecdc_id')->value('ecdc_id');
        $mosy_id = StudentECDC::getECD($filter)->where('period', ECDC::MOSY)->groupBy('ecdc_id')->value('ecdc_id');
        $eosy_id = StudentECDC::getECD($filter)->where('period', ECDC::EOSY)->groupBy('ecdc_id')->value('ecdc_id');

        $bosy_result = ($bosy_id) ? ECDC::getJsonResult($bosy_id)[$student->id] : 'No Record';
        $mosy_result = ($mosy_id) ? ECDC::getJsonResult($mosy_id)[$student->id] : 'No Record';
        $eosy_result = ($eosy_id) ? ECDC::getJsonResult($eosy_id)[$student->id] : 'No Record';

        $results = [
            'bosy' => $bosy_result,
            'mosy' => $mosy_result,
            'eosy' => $eosy_result,
        ];

        $domains = ECDCDomain::all();
        $domain_competencies = [];

        foreach ($domains as $key => $domain) {
            $domain_competencies[$domain->id] = [
                'domain' => $domain->domain,
                'competencies' => ECDCCompetency::where('domain_id', $domain->id)->get(),
            ];
        }
        // dd($results);

        $periods = ['bosy', 'mosy', 'eosy'];

        $classroom = CustomFunction::getClassroomDetails($classroom->id);

        return view('ecdc.front', compact(
            'student', 'classroom',
            'results', 'domains', 'domain_competencies',
            'periods'
        ));

    }
    // public function show_result(ECDC $ecdc){
    //     $page = [
    //         'name'      =>  'Assessment',
    //         'sub_name'  =>  'ECDC',
    //         'title'     =>  'ECDC ',
    //         'crumb'     =>  array(
    //             'ECDC Assessment' => '/ecdcs',
    //             'Classroom ECDC' => '/ecdcs/classroom/'.$ecdc->classroom_id,
    //             'ECDC View Result' => '',
    //         )
    //     ];

    //     $results = ECDC::getJsonResult($ecdc->id);
    //     return view('ecdc.show_result', compact(
    //         'page', 'ecdc', 'results'
    //     ));
    // }

    // public function show_student($ecdc_id, $student_id){

    //     $get_results = ECDC::getResults($ecdc_id);
    //     $result = $get_results[$student_id];

    //     $page = [
    //         'name'      =>  'ECDC',
    //         'title'     =>  'ECDC ',
    //         'crumb'     =>  array(
    //             'ECDC Assessment' => '/ecdcs',
    //             'Classroom ECDC' => '/ecdcs/classroom/'.ECDC::find($ecdc_id)->value('classroom_id'),
    //             'ECDC View Result' => '/ecdcs/'.$ecdc_id,
    //             $result['lrn']=> '',
    //         )
    //     ];

    //     $domains = ECDCDomain::all();
    //     $data = array();

    //     $colors = [
    //         'red',
    //         'orange',
    //         'yellow',
    //         'green',
    //         'primary',
    //         'info',
    //         'purple',
    //     ];

    //     foreach ($domains  as $domain) {
    //         $student_ecdcs = StudentECDC::select(
    //             'tbl_student_ecdcs.id as id', 'competency', 'p', 'o', 'r'
    //         )->join('tbl_ecdc_competencies', 'tbl_student_ecdcs.ecdc_competency_id', 'tbl_ecdc_competencies.id')
    //         ->where('ecdc_id', $ecdc_id)
    //         ->where('domain_id', $domain->id)
    //         ->where('student_id', $student_id)
    //         ->get();

    //         $data[$domain->id] = array(
    //             "color" => $colors[$domain->id-1],
    //             "domain" => $domain->domain,
    //             "competencies" => $student_ecdcs
    //         );
    //     }

    //     $ecdc = ECDC::find($ecdc_id);
    //     return view('ecdc.show_student', compact(
    //         'page', 'result', 'data', 'ecdc', 'domains', 'colors',
    //         'ecdc_id', 'student_id'
    //     ));

    // }

    // public function show_student_report(Classroom $classroom, Student $student){

    //     $page = [
    //         'name'      =>  'ECDC',
    //         'title'     =>  'ECDC ',
    //         'crumb'     =>  array(
    //             'ECDC Assessment' => '/ecdcs',
    //             'Classroom ECDC' => '/ecdcs/classroom/'.$classroom->id,
    //             'Report Card' => '',
    //         )
    //     ];
    //     $academic_year = AcademicYear::active();

    //     $domains = ECDCDomain::all();

    //     $colors = [
    //         'red',
    //         'orange',
    //         'yellow',
    //         'green',
    //         'primary',
    //         'info',
    //         'purple',
    //     ];

    //     $results = array();

    //     $key = "none";
    //     $results['bosy']['has_data'] = 0;
    //     $results['eosy']['has_data'] = 0;

    //     #BOSY
    //         $bosy_ecdc_id = StudentECDC::select()
    //             ->join('tbl_ecdcs', 'tbl_student_ecdcs.ecdc_id', 'tbl_ecdcs.id')
    //             ->where('tbl_ecdcs.academic_year_id', $academic_year->id)
    //             ->where('student_id', $student->id)
    //             ->where('period', 1)->groupBy('ecdc_id')->max('ecdc_id');

    //         if ($bosy_ecdc_id) {
    //             $results['bosy']['has_data'] = 1;
    //             $results['bosy']['data'] = ECDC::getJsonResult($bosy_ecdc_id)[$student->id];
    //             foreach ($domains  as $domain) {
    //                 $student_ecdcs = StudentECDC::select(
    //                     'tbl_student_ecdcs.id as id', 'competency', 'p', 'o', 'r'
    //                 )->join('tbl_ecdc_competencies', 'tbl_student_ecdcs.ecdc_competency_id', 'tbl_ecdc_competencies.id')
    //                 ->where('ecdc_id', $bosy_ecdc_id)
    //                 ->where('domain_id', $domain->id)
    //                 ->where('student_id', $student->id)
    //                 ->get();

    //                 $data[$domain->id] = array(
    //                     "color" => $colors[$domain->id-1],
    //                     "domain" => $domain->domain,
    //                     "competencies" => $student_ecdcs
    //                 );
    //             }

    //             $bosy_ecdc = ECDC::find($bosy_ecdc_id);
    //             $results['bosy']['date'] = $bosy_ecdc->date;
    //             $results['bosy']['domains'] = $data;
    //             $key = "bosy";
    //         }

    //     #EOSY
    //         $eosy_ecdc_id = StudentECDC::select()
    //             ->join('tbl_ecdcs', 'tbl_student_ecdcs.ecdc_id', 'tbl_ecdcs.id')
    //             ->where('tbl_ecdcs.academic_year_id', $academic_year->id)
    //             ->where('student_id', $student->id)
    //             ->where('period', 2)->groupBy('ecdc_id')
    //             ->max('ecdc_id');

    //         if ($eosy_ecdc_id) {
    //             $data = array();
    //             $results['eosy']['has_data'] = 1;
    //             $results['eosy']['data'] = ECDC::getJsonResult($eosy_ecdc_id)[$student->id];
    //             foreach ($domains  as $domain) {
    //                 $student_ecdcs = StudentECDC::select(
    //                     'tbl_student_ecdcs.id as id', 'competency', 'p', 'o', 'r'
    //                 )->join('tbl_ecdc_competencies', 'tbl_student_ecdcs.ecdc_competency_id', 'tbl_ecdc_competencies.id')
    //                 ->where('ecdc_id', $eosy_ecdc_id)
    //                 ->where('domain_id', $domain->id)
    //                 ->where('student_id', $student->id)
    //                 ->get();

    //                 $data[$domain->id] = array(
    //                     "color" => $colors[$domain->id-1],
    //                     "domain" => $domain->domain,
    //                     "competencies" => $student_ecdcs
    //                 );
    //             }
    //             $eosy_ecdc = ECDC::find($eosy_ecdc_id);
    //             $results['eosy']['date'] = $eosy_ecdc->date;
    //             $results['eosy']['domains'] = $data;
    //             $key = "eosy";
    //         }

    //     if ($key != "none") {
    //         $results['name'] = $results[$key]['data']['name'];
    //         $results['lrn'] = $results[$key]['data']['lrn'];
    //     }

    //     $domains = $domains->toArray();
    //     return view('ecdc.reports.student_report', compact(
    //         'page', 'results', 'domains', 'colors'
    //     ));
    // }

    public function download_student_result($ecdc_id, $student_id)
    {

        $reader = new Xlsx;
        $spreadsheet = $reader->load(public_path('/templates/Template-ECDC-Uploader.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();

    }

    public function download_template($classroom_id)
    {

        $students = CustomFunction::getStudentsPerClassroom($classroom_id);
        $classroom = Classroom::find($classroom_id);
        $section = Section::find($classroom->section_id);
        $grade_level = GradeLevel::find($classroom->grade_level_id);

        $reader = new Xlsx;
        $spreadsheet = $reader->load(public_path('/templates/Template-ECDC-Uploader.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();

        $col = 'D';

        foreach ($students as $key => $student) {

            $fullname = $student->last_name.', '.$student->first_name.' '.$student->middle_name;
            $sheet->getStyle($col.'1')->getNumberFormat()->setFormatCode('0');
            $sheet->setCellValue($col.'1', $student->lrn);
            $sheet->setCellValue($col.'2', $fullname);
            $col++;

        }

        $file_name = 'ECDC - TEMPLATE ('.$grade_level->level.'-'.$section->section.')';
        $spreadsheet->getProperties()
            ->setTitle('STARS: SYSTEM GENERATED REPORT')
            ->setSubject('STARS: SYSTEM GENERATED REPORT')
            ->setKeywords('PAYROLL') // Tags
            ->setCategory('STARS Report')
            ->setDescription('This is a system generated report.') // Comment
            ->setCreator('Students Test Analysis Records System') // Author
            ->setLastModifiedBy('Developer - Glenn Nerrie A. Afurong');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$file_name.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        /* Download/Export as xlsx */
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');

    }

    public function upload(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'Please upload a file.',
            'file.mimes' => 'The file must be an Excel file (xlsx or xls).',
            'file.max' => 'The file size must not exceed 10MB.',
        ]);

        $total_students = StudentClassroom::where('classroom_id', $request->classroom_id)->count();
        $spreadsheet = IOFactory::load($request->file('file'));
        $sheet = $spreadsheet->getSheetByName("ENCODE here")->toArray();
        $data = [];
        $errors = [];

        $period = $spreadsheet->getSheetByName("ENCODE here")->getCell('C2')->getValue();
        $date = $spreadsheet->getSheetByName("ENCODE here")->getCell('C3')->getValue();

        if ($date == null) {
            $errors[] = 'Date cannot be null.';
        }

        if ($period == null) {
            $errors[] = 'Perod cannot be null.';
        }

        $col = 'D';

        for ($i = 0; $i < $total_students; $i++) {

            $lrn = $spreadsheet->getSheetByName("ENCODE here")->getCell($col.'1')->getValue();
            $name = $spreadsheet->getSheetByName("ENCODE here")->getCell($col.'2')->getValue();

            if ($lrn != null) {

                $check_lrn = StudentClassroom::join(
                    'tbl_students',
                    'tbl_student_classrooms.student_id',
                    'tbl_students.id'
                )->where('classroom_id', $request->classroom_id)
                    ->where('lrn', $lrn)
                    ->get();

                if ($check_lrn->count() > 0) {
                    for ($row = 5; $row < 124; $row++) {

                        $competency_id = $spreadsheet->getSheetByName("ENCODE here")->getCell('A'.$row)->getValue();

                        if ($competency_id != '*') {

                            $get_score = $spreadsheet->getSheetByName("ENCODE here")->getCell($col.$row)->getValue();
                            $score = ($get_score != 1) ? 0 : 1;
                            $data[$check_lrn[0]->student_id][$competency_id] = $score;
                        }
                    }
                } else {
                    $errors[] = $name.' does not exist on this class';
                }
            }
            $col++;

        }

        // dd($data);

        if (count($errors) == 0) {

            DB::beginTransaction();
            try {

                $academic_year = AcademicYear::active();

                $ecdc = new ECDC;
                $ecdc->source = 1;
                $ecdc->date = $request->date;
                $ecdc->period = $request->period;
                $ecdc->academic_year_id = $academic_year->id;
                $ecdc->classroom_id = $request->classroom_id;
                $ecdc->teacher_id = Teacher::where('user_id', Auth::user()->id)->value('id');
                $ecdc->created_at = Carbon::now('Asia/Manila');

                $ecdc->save();

                $duplicates = 0;
                foreach ($data as $student_id => $competencies) {

                    $existing = StudentECDC::join(
                        'tbl_ecdcs', 'tbl_student_ecdcs.ecdc_id', 'tbl_ecdcs.id'
                    )->where([
                        'student_id' => $student_id,
                        'period' => $request->period,
                        'academic_year_id' => $academic_year->id,
                        'classroom_id' => $request->classroom_id,
                    ])->count();

                    if ($existing > 0) {
                        $duplicates++;
                    } else {
                        foreach ($competencies as $competency_id => $score) {
                            $student_ecdc = new StudentECDC;
                            $student_ecdc->ecdc_id = $ecdc->id;
                            $student_ecdc->student_id = $student_id;
                            $student_ecdc->ecdc_competency_id = $competency_id;
                            $student_ecdc->score = $score;
                            $student_ecdc->save();
                        }
                    }

                }

                DB::commit();
                $result = true;

                // Write Result
                ECDC::getResults($ecdc->id);

            } catch (Exception $e) {

                DB::rollBack();
                $result = $e->getMessage();

            }

            if ($result === true) {
                return back()->with('success', 'ECDC Successfully Uploaded. Total of '.$duplicates.' duplicate record(s) found.');
            } else {
                return redirect()->to(url()->previous())->withErrors(['error' => $result]);
            }

        } else {

            return redirect()->to(url()->previous())->withErrors(['error' => $errors]);

        }

    }

    public function print(Classroom $classroom, Student $student)
    {

        $user = CustomFunction::getUserDetails(Auth::user()->id);

        $academic_year = AcademicYear::active();
        $person = User::join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('tbl_users.id', $student->user_id)
            ->first();

        $filter = [
            'academic_year_id' => $academic_year->id,
            'classroom_id' => $classroom->id,
            'student_id' => $student->id,
        ];

        $bosy_id = StudentECDC::getECD($filter)->where('period', ECDC::BOSY)->groupBy('ecdc_id')->value('ecdc_id');
        $mosy_id = StudentECDC::getECD($filter)->where('period', ECDC::MOSY)->groupBy('ecdc_id')->value('ecdc_id');
        $eosy_id = StudentECDC::getECD($filter)->where('period', ECDC::EOSY)->groupBy('ecdc_id')->value('ecdc_id');

        $bosy_result = ($bosy_id) ? ECDC::getJsonResult($bosy_id)[$student->id] : 'No Record';
        $mosy_result = ($mosy_id) ? ECDC::getJsonResult($mosy_id)[$student->id] : 'No Record';
        $eosy_result = ($eosy_id) ? ECDC::getJsonResult($eosy_id)[$student->id] : 'No Record';

        $results = [
            'bosy' => $bosy_result,
            'mosy' => $mosy_result,
            'eosy' => $eosy_result,
        ];

        $domains = ECDCDomain::all();
        $domain_competencies = [];

        foreach ($domains as $key => $domain) {
            $domain_competencies[$domain->id] = [
                'domain' => $domain->domain,
                'competencies' => ECDCCompetency::where('domain_id', $domain->id)->get(),
            ];
        }

        $date = new DateTime('now');
        $birthdate = new DateTime($person->birth_date);
        $age = $date->diff($birthdate);
        $age = $age->y.'.'.$age->m;

        $periods = ['bosy', 'mosy', 'eosy'];
        // dd($user);
        $classroom = CustomFunction::getClassroomDetails($classroom->id);

        return view('ecdc.card_tabs.print', compact(
            'student', 'classroom',
            'results', 'domains', 'domain_competencies',
            'periods', 'person', 'age',
            'user', 'academic_year'
        ));

    }

    public function print_front($ecdc_id, $student_id) {}

    public function print_back($ecdc_id, $student_id)
    {
        $get_results = ECDC::getResults($ecdc_id);
        $result = $get_results[$student_id];

        $domains = ECDCDomain::all();
        $colors = [
            'red',
            'orange',
            'yellow',
            'green',
            'primary',
            'info',
            'purple',
        ];

        foreach ($domains as $domain) {
            $student_ecdcs = StudentECDC::select(
                'tbl_student_ecdcs.id as id', 'competency', 'p', 'o', 'r'
            )->join('tbl_ecdc_competencies', 'tbl_student_ecdcs.ecdc_competency_id', 'tbl_ecdc_competencies.id')
                ->where('ecdc_id', $ecdc_id)
                ->where('domain_id', $domain->id)
                ->where('student_id', $student_id)
                ->get();

            $data[$domain->id] = [
                'color' => $colors[$domain->id - 1],
                'domain' => $domain->domain,
                'competencies' => $student_ecdcs,
            ];
        }
        $ecdc = ECDC::find($ecdc_id);
        $student = Student::find($student_id);
        $user = User::join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('tbl_users.id', $student->user_id)->first();

        // dd($result);
        return view('ecdc.reports.back',
            compact('data', 'result', 'ecdc', 'student', 'user')
        );
    }
}
