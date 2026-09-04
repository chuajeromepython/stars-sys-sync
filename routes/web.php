<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssistantDivisionSuperIntendentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChiefCIDController;
use App\Http\Controllers\ChiefSGODController;
use App\Http\Controllers\ClassAssessmentController;
use App\Http\Controllers\ClassificationHistoryController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CompetencyController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentHeadController;
use App\Http\Controllers\DiagnosticController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\DistrictSupervisorController;
use App\Http\Controllers\DivisionAdministratorController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\DivisionSuperIntendentController;
use App\Http\Controllers\DivisionSupervisorController;
use App\Http\Controllers\ECDCController;
use App\Http\Controllers\ECDCDomainController;
use App\Http\Controllers\GradeLevelController;
use App\Http\Controllers\ItemBankController;
use App\Http\Controllers\PeriodicalController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolSupervisorController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StrandController;
use App\Http\Controllers\StudentAnswerController;
use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectComponentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SummativeController;
use App\Http\Controllers\TeacherClassController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\TrailController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'welcome']);
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/authenticate', [AuthController::class, 'authenticate']);
Route::get('/forbidden', [AuthController::class, 'forbidden']);
Route::get('/truncate/{key}', [DashboardController::class, 'truncate']);

Route::middleware(['auth'])->group(function () {

    // Auth
    Route::get('/logout', [AuthController::class, 'destroy']);
    Route::get('/account', [AuthController::class, 'account']);
    Route::get('/account/qr', [AuthController::class, 'accountQr'])->name('account.qr');
    Route::post('/account/update_password', [AuthController::class, 'updatePassword'])->name('account.update_password');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/database', [DashboardController::class, 'database']);
    Route::get('/download_template/{code}', [DashboardController::class, 'download_template']);

    // DepartmentHead
    Route::get('/department_heads/', [DepartmentHeadController::class, 'index']);
    Route::get('/department_heads/create', [DepartmentHeadController::class, 'create']);
    Route::get('/department_heads/{department_head}/edit', [DepartmentHeadController::class, 'edit']);
    Route::post('/department_heads/store', [DepartmentHeadController::class, 'store']);
    Route::post('/department_heads/update', [DepartmentHeadController::class, 'update']);
    Route::post('/department_heads/destroy', [DepartmentHeadController::class, 'destroy']);
    Route::post('/department_heads/upload', [DepartmentHeadController::class, 'upload']);
    // Teachers
    Route::get('/teachers', [TeacherController::class, 'index']);
    Route::get('/teachers/create', [TeacherController::class, 'create']);
    Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit']);
    Route::post('/teachers/store', [TeacherController::class, 'store']);
    Route::post('/teachers/update', [TeacherController::class, 'update']);
    Route::post('/teachers/destroy', [TeacherController::class, 'destroy']);
    Route::post('/teachers/upload', [TeacherController::class, 'upload']);

    // PERMISSION : System Administrator
    Route::middleware(['role:System Administrator'])
        ->group(function () {
            // Division
            Route::get('/divisions', [DivisionController::class, 'index']);
            Route::post('/divisions/store', [DivisionController::class, 'store']);
            Route::post('/divisions/update', [DivisionController::class, 'update']);
            Route::post('/divisions/destroy', [DivisionController::class, 'destroy']);

        // Trails
        Route::get('/trails', [TrailController::class, 'index']);
        Route::get('/trails/{model}', [TrailController::class, 'getTrails']);
        });

    // PERMISSION : Division Administrator
    Route::get('/competencies', [CompetencyController::class, 'index']);
    Route::middleware(['role:System Administrator,Division Administrator'])->group(function () {

        // Academic Year
        Route::get('/academic_years', [AcademicYearController::class, 'index']);
        Route::post('/academic_years/store', [AcademicYearController::class, 'store']);
        Route::post('/academic_years/update', [AcademicYearController::class, 'update']);
        Route::post('/academic_years/destroy', [AcademicYearController::class, 'destroy']);
        // District
        Route::get('/districts', [DistrictController::class, 'index']);
        Route::post('/districts/store', [DistrictController::class, 'store']);
        Route::post('/districts/update', [DistrictController::class, 'update']);
        Route::post('/districts/destroy', [DistrictController::class, 'destroy']);
        // School
        Route::get('/schools', [SchoolController::class, 'index']);
        Route::get('/schools/{school}/edit', [SchoolController::class, 'edit']);
        Route::get('/schools/create', [SchoolController::class, 'create']);
        Route::post('/schools/store', [SchoolController::class, 'store']);
        Route::post('/schools/update', [SchoolController::class, 'update']);
        Route::post('/schools/destroy', [SchoolController::class, 'destroy']);
        // Grade Level
        Route::get('/grade_levels', [GradeLevelController::class, 'index']);
        Route::post('/grade_levels/store', [GradeLevelController::class, 'store']);
        Route::post('/grade_levels/update', [GradeLevelController::class, 'update']);
        Route::post('/grade_levels/destroy', [GradeLevelController::class, 'destroy']);
        // Subject
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::post('/subjects/store', [SubjectController::class, 'store']);
        Route::post('/subjects/update', [SubjectController::class, 'update']);
        Route::post('/subjects/destroy', [SubjectController::class, 'destroy']);
        Route::get('/subjects/{subject}', [SubjectController::class, 'show']);
        // Subject Component
        Route::post('/subject_components/store', [SubjectComponentController::class, 'store']);
        Route::post('/subject_components/update', [SubjectComponentController::class, 'update']);
        Route::post('/subject_components/destroy', [SubjectComponentController::class, 'destroy']);
        // Semester
        Route::get('/semesters', [SemesterController::class, 'index']);
        Route::post('/semesters/store', [SemesterController::class, 'store']);
        Route::post('/semesters/update', [SemesterController::class, 'update']);
        Route::post('/semesters/destroy', [SemesterController::class, 'destroy']);
        // Track
        Route::get('/tracks', [TrackController::class, 'index']);
        Route::post('/tracks/store', [TrackController::class, 'store']);
        Route::post('/tracks/update', [TrackController::class, 'update']);
        Route::post('/tracks/destroy', [TrackController::class, 'destroy']);
        // Strand
        Route::get('/strands', [StrandController::class, 'index']);
        Route::post('/strands/store', [StrandController::class, 'store']);
        Route::post('/strands/update', [StrandController::class, 'update']);
        Route::post('/strands/destroy', [StrandController::class, 'destroy']);
        // Course
        Route::get('/courses', [CourseController::class, 'index']);
        Route::post('/courses/store', [CourseController::class, 'store']);
        Route::post('/courses/update', [CourseController::class, 'update']);
        Route::post('/courses/destroy', [CourseController::class, 'destroy']);
        // Competency
        Route::post('/competencies/store', [CompetencyController::class, 'store']);
        Route::post('/competencies/update', [CompetencyController::class, 'update']);
        Route::post('/competencies/destroy', [CompetencyController::class, 'destroy']);
        Route::post('/competencies/upload', [CompetencyController::class, 'upload']);
        
        Route::get('/competencies/create', [CompetencyController::class, 'create']);
        Route::get('/competencies/{competency}/edit', [CompetencyController::class, 'edit']);
        // ECDC Domains
        Route::get('/ecdc_domains', [ECDCDomainController::class, 'index']);
        // School
        Route::get('/schools', [SchoolController::class, 'index']);
        Route::get('/schools/create', [SchoolController::class, 'create']);
        Route::post('/schools/store', [SchoolController::class, 'store']);
        Route::get('/schools/edit', [SchoolController::class, 'edit']);

    });

    // PERMISSION : System and Division Administrator
    Route::middleware(['role:System Administrator,Division Administrator'])
        ->group(function () {

        // User
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/create', [UserController::class, 'create']);
        Route::get('/users/{user}/edit', [UserController::class, 'edit']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::post('/users/reset', [UserController::class, 'reset']);
        Route::post('/users/store', [UserController::class, 'store']);
        Route::post('/users/update', [UserController::class, 'update']);
        Route::post('/users/destroy', [UserController::class, 'destroy']);

        // USERS : EDIT
        Route::get('/division_supervisors/{division_supervisor}/edit',
            [DivisionSupervisorController::class, 'edit']);
        Route::post('/division_supervisors/update',
            [DivisionSupervisorController::class, 'update']);

        Route::get('/division_administrators/{division_administrator}/edit',
            [DivisionAdministratorController::class, 'edit']);
        Route::post('/division_administrators/update',
            [DivisionAdministratorController::class, 'update']);

        Route::get('/district_supervisors/{district_supervisor}/edit',
            [DistrictSupervisorController::class, 'edit']);
        Route::post('/district_supervisors/update',
            [DistrictSupervisorController::class, 'update']);

        Route::get('/division_superintendents/{division_superintendent}/edit',
            [DivisionSuperIntendentController::class, 'edit']);
        Route::post('/division_superintendents/update',
            [DivisionSuperIntendentController::class, 'update']);

        Route::get('/asst_division_superintendents/{asst_division_superintendent}/edit',
            [AssistantDivisionSuperIntendentController::class, 'edit']);
        Route::post('/asst_division_superintendents/update',
            [AssistantDivisionSuperIntendentController::class, 'update']);

        Route::get('/chief_cids/{chief_cid}/edit', [ChiefCIDController::class, 'edit']);
        Route::post('/chief_cids/update', [ChiefCIDController::class, 'update']);

        Route::get('/chief_sgods/{chief_sgod}/edit', [ChiefSGODController::class, 'edit']);
        Route::post('/chief_sgods/update', [ChiefSGODController::class, 'update']);

        Route::get('/school_supervisors/{school_supervisor}/edit', [SchoolSupervisorController::class, 'edit']);
        Route::post('/school_supervisors/update', [SchoolSupervisorController::class, 'update']);
        Route::post('/school_supervisors/upload', [SchoolSupervisorController::class, 'upload']);

        Route::post('/users/classifications/{user}/update', [ClassificationHistoryController::class, 'update']);

    });

    // API
    Route::post('/getArea', [APIController::class, 'getArea']);
    Route::post('/getStrands', [APIController::class, 'getStrands']);
    Route::post('/getCourses', [APIController::class, 'getCourses']);
    Route::post('/getTeachers', [APIController::class, 'getTeachers']);
    Route::post('/getStudentAnswers', [APIController::class, 'getStudentAnswers']);
    Route::post('/getDistricts', [APIController::class, 'getDistricts']);
    Route::post('/getDistrictsPerDivision', [APIController::class, 'getDistrictsPerDivision']);
    Route::post('/getSchools', [APIController::class, 'getSchools']);
    Route::post('/searchLRN', [APIController::class, 'searchLRN']);
    Route::post('/getGradeLevelPerEducationLevel', [APIController::class, 'getGradeLevelPerEducationLevel']);
    Route::post('/getAssessmentTypePerGradeLevel', [APIController::class, 'getAssessmentTypePerGradeLevel']);
    Route::post('/getSubjectClassPerGradeLevel', [APIController::class, 'getSubjectClassPerGradeLevel']);
    Route::post('/getGradeLevelPerAcademicYear', [APIController::class, 'getGradeLevelPerAcademicYear']);
    Route::post('/getSubjectPerGradeLevel', [APIController::class, 'getSubjectPerGradeLevel']);
    Route::post('/getItems', [APIController::class, 'getItems']);
    Route::post('/getTeachersPerSchool', [APIController::class, 'getTeachersPerSchool']);
    Route::post('/getSectionsPerSchool', [APIController::class, 'getSectionsPerSchool']);
    Route::post('/getECDCResult', [APIController::class, 'getECDCResult']);

    // Sections
    Route::get('/sections', [SectionController::class, 'index']);
    Route::post('/sections/store', [SectionController::class, 'store']);
    Route::post('/sections/update', [SectionController::class, 'update']);
    Route::post('/sections/destroy', [SectionController::class, 'destroy']);
    Route::post('/sections/upload', [SectionController::class, 'upload']);

    // Classroom
    Route::post('/classrooms/store', [ClassroomController::class, 'store']);
    Route::post('/classrooms/update', [ClassroomController::class, 'update']);
    Route::post('/classrooms/destroy', [ClassroomController::class, 'destroy']);
    Route::post('/classrooms/upload', [ClassroomController::class, 'upload']);
    Route::get('/classrooms', [ClassroomController::class, 'index']);
    Route::get('/classrooms/create', [ClassroomController::class, 'create']);
    Route::get('/classrooms/{classroom}', [ClassroomController::class, 'show']);

    // teacher Classes
    Route::post('/teacher_classes/upload', [TeacherClassController::class, 'upload']);
    Route::post('/teacher_classes/store', [TeacherClassController::class, 'store']);
    Route::post('/teacher_classes/add_student', [TeacherClassController::class, 'add_student']);
    Route::post('/teacher_classes/destroy', [TeacherClassController::class, 'destroy']);
    Route::get('/teacher_classes/{teacher_class}', [TeacherClassController::class, 'show']);
    Route::post('/teacher_classes/update_student_status', [TeacherClassController::class, 'update_student_status']);

    // students
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/create', [StudentController::class, 'create']);
    Route::post('/students/upload', [StudentController::class, 'upload']);
    Route::post('/students/store', [StudentController::class, 'store']);
    Route::post('/students/update', [StudentController::class, 'update']);
    Route::get('/students/{student}/edit', [StudentController::class, 'edit']);

    // students class assessments
    Route::get('/students/class_assessments', [StudentController::class, 'studentsClassAssessment']);
    Route::get('/students/class_assessments/{class_assessment}',
        [StudentController::class, 'studentsClassAssessmentShow']);

    // student Classes
    Route::post('/student_classes/sync', [StudentClassController::class, 'sync']);

    // Assessments
    Route::get('/assessments/', [AssessmentController::class, 'index']);
    Route::post('/assessments/store', [AssessmentController::class, 'store']);
    Route::post('/assessments/update', [AssessmentController::class, 'update']);
    Route::post('/assessments/destroy', [AssessmentController::class, 'destroy']);
    Route::post('/assessments/upload', [AssessmentController::class, 'upload']);
    // Periodicals
    Route::get('/periodicals/', [PeriodicalController::class, 'index']);
    Route::post('/periodicals/upload', [PeriodicalController::class, 'upload']);
    Route::get('/periodicals/{assessment}', [PeriodicalController::class, 'show']);
    // Diagnostics
    Route::get('/diagnostics/', [DiagnosticController::class, 'index']);
    Route::post('/diagnostics/upload', [DiagnosticController::class, 'upload']);
    Route::get('/diagnostics/{assessment}', [DiagnosticController::class, 'show']);
    // Questions
    Route::put('/questions/{id}/update-answer-key', [QuestionController::class, 'updateAnswerKey']);
    // StudentAnswers
    Route::post('/student_answers/upload', [StudentAnswerController::class, 'upload']);
    Route::post('/student_answers/batch_update', [StudentAnswerController::class, 'batch_update']);
    // class_assessments
    Route::get('/class_assessments/{class_assessment}', [ClassAssessmentController::class, 'show']);
    Route::get('/class_assessments/{class_assessment}/results',
        [ClassAssessmentController::class, 'results']);
    Route::get('/class_assessments/{class_assessment}/item_analysis',
        [ClassAssessmentController::class, 'item_analysis']);
    Route::get('/class_assessments/{class_assessment}/score_analysis',
        [ClassAssessmentController::class, 'score_analysis']);
    Route::get('/class_assessments/{class_assessment}/discrimination_index',
        [ClassAssessmentController::class, 'discrimination_index']);
    Route::get('/class_assessments/{class_assessment}/results/download',
        [ClassAssessmentController::class, 'download_results']);
    Route::get('/class_assessments/{class_assessment}/item_analysis/download',
        [ClassAssessmentController::class, 'download_item_analysis']);
    Route::get('/class_assessments/{class_assessment}/score_analysis/download',
        [ClassAssessmentController::class, 'download_score_analysis']);
    Route::get('/class_assessments/{class_assessment}/discrimination_index/download',
        [ClassAssessmentController::class, 'download_discrimination_index']);

    // Summatives
    Route::get('/summatives/', [SummativeController::class, 'index']);
    Route::post('/summatives/upload', [SummativeController::class, 'upload']);
    Route::get('/summatives/{assessment}', [SummativeController::class, 'show']);

    // ECDC

    Route::post('/ecdcs/store', [ECDCController::class, 'store']);
    Route::post('/ecdcs/upload', [ECDCController::class, 'upload']);
    Route::post('/ecdcs/update', [ECDCController::class, 'update']);
    Route::get('/ecdcs', [ECDCController::class, 'index']);
    Route::get('/ecdcs/print_result', [ECDCController::class, 'print']);
    Route::get('/ecdcs/classroom/{classroom}', [ECDCController::class, 'show']);
    Route::get('/ecdcs/classroom/{classroom}/create', [ECDCController::class, 'create']);
    Route::get('/ecdcs/classroom/{classroom}/card/{student}', [ECDCController::class, 'card']);
    Route::get('/ecdcs/classroom/{classroom}/card/{student}/print', [ECDCController::class, 'print']);

    Route::get('/ecdcs/{ecdc}/students/{student_id}/download', [ECDCController::class, 'download_student_result']);
    Route::get('/ecdcs/{classroom}/download_template', [ECDCController::class, 'download_template']);

    // Summatives
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports/generate', [ReportController::class, 'generate']);

    // Item Bank
    Route::get('/item_banks', [ItemBankController::class, 'index']);

});
