<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\APIController;
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
use App\Http\Controllers\ECDCCompetencyController;
use App\Http\Controllers\ECDCController;
use App\Http\Controllers\ECDCDomainController;
use App\Http\Controllers\GradeLevelController;
use App\Http\Controllers\ItemBankController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
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
use App\Http\Controllers\TermExamController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\TrailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'welcome']);
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/authenticate', [AuthController::class, 'authenticate']);
Route::get('/forbidden', [AuthController::class, 'forbidden']);

Route::middleware(['auth'])->group(function () {

    // Auth
    Route::get('/logout', [AuthController::class, 'destroy']);
    Route::get('/account', [AuthController::class, 'account']);
    Route::get('/account/qr', [AuthController::class, 'accountQr'])->name('account.qr');
    Route::post('/account/update_password', [AuthController::class, 'updatePassword'])->name('account.update_password');

    // Roles
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    });

    // Every user management tab is served from one endpoint. Spatie's
    // permission middleware treats its final argument as a guard name, so it
    // cannot express "any of these permissions" on its own; RoleController
    // authorises the specific tab against UserManagementTab instead.
    Route::get('/user-management/data', [RoleController::class, 'data'])->name('user-management.data');
    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Permissions
    Route::middleware('permission:permissions.view')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
    });
    Route::middleware('permission:permissions.manage')->group(function () {
        Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });

    // Per user role assignment
    Route::middleware('permission:roles.assign')->group(function () {
        Route::get('/users/{user}/roles', [UserRoleController::class, 'show'])->name('users.roles.show');
        Route::put('/users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
        Route::delete('/users/{user}/roles', [UserRoleController::class, 'destroy'])->name('users.roles.destroy');
    });

    // Dashboard
    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/records', [DashboardController::class, 'records']);
        Route::get('/download_template/{code}', [DashboardController::class, 'download_template']);
    });

    // Dashboard : Database Management
    Route::middleware('permission:dashboard.manage')->group(function () {
        Route::get('/database', [DashboardController::class, 'database']);
        Route::get('/truncate/{key}', [DashboardController::class, 'truncate']);
    });

    // Division
    Route::middleware('permission:divisions.view')->group(function () {
        Route::get('/divisions', [DivisionController::class, 'index']);
    });
    Route::middleware('permission:divisions.manage')->group(function () {
        Route::post('/divisions/store', [DivisionController::class, 'store']);
        Route::post('/divisions/update', [DivisionController::class, 'update']);
        Route::post('/divisions/destroy', [DivisionController::class, 'destroy']);
    });

    // Trails
    Route::middleware('permission:trails.view')->group(function () {
        Route::get('/trails', [TrailController::class, 'index']);
        Route::get('/trails/{model}', [TrailController::class, 'getTrails']);
    });

    // Department Head
    Route::middleware('permission:department_heads.view')->group(function () {
        Route::get('/department_heads/', [DepartmentHeadController::class, 'index']);
    });
    Route::middleware('permission:department_heads.manage')->group(function () {
        Route::get('/department_heads/create', [DepartmentHeadController::class, 'create']);
        Route::get('/department_heads/{department_head}/edit', [DepartmentHeadController::class, 'edit']);
        Route::post('/department_heads/store', [DepartmentHeadController::class, 'store']);
        Route::post('/department_heads/update', [DepartmentHeadController::class, 'update']);
        Route::post('/department_heads/destroy', [DepartmentHeadController::class, 'destroy']);
    });
    Route::middleware('permission:department_heads.upload')->group(function () {
        Route::post('/department_heads/upload', [DepartmentHeadController::class, 'upload']);
    });

    // Teachers
    Route::middleware('permission:teachers.view')->group(function () {
        Route::get('/teachers', [TeacherController::class, 'index']);
        Route::get('/teachers/data', [TeacherController::class, 'data']);
    });
    Route::middleware('permission:teachers.manage')->group(function () {
        Route::get('/teachers/create', [TeacherController::class, 'create']);
        Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit']);
        Route::post('/teachers/store', [TeacherController::class, 'store']);
        Route::post('/teachers/update', [TeacherController::class, 'update']);
        Route::post('/teachers/destroy', [TeacherController::class, 'destroy']);
    });
    Route::middleware('permission:teachers.upload')->group(function () {
        Route::post('/teachers/upload', [TeacherController::class, 'upload']);
    });

    // Competencies
    Route::middleware('permission:competencies.view')->group(function () {
        Route::get('/competencies', [CompetencyController::class, 'index']);
    });
    Route::middleware('permission:competencies.manage')->group(function () {
        Route::post('/competencies/store', [CompetencyController::class, 'store']);
        Route::post('/competencies/update', [CompetencyController::class, 'update']);
        Route::post('/competencies/destroy', [CompetencyController::class, 'destroy']);
        Route::get('/competencies/create', [CompetencyController::class, 'create']);
        Route::get('/competencies/{competency}/edit', [CompetencyController::class, 'edit']);
    });
    Route::middleware('permission:competencies.upload')->group(function () {
        Route::post('/competencies/upload', [CompetencyController::class, 'upload']);
    });

    // ECDC Domains
    Route::middleware('permission:ecdc_domains.view')->group(function () {
        Route::get('/ecdc_domains', [ECDCDomainController::class, 'index']);
        Route::get('/ecdc_domains/{ecdc_domain}', [ECDCDomainController::class, 'show'])->whereNumber('ecdc_domain');
    });
    Route::middleware('permission:ecdc_domains.manage')->group(function () {
        Route::post('/ecdc_domains/store', [ECDCDomainController::class, 'store']);
        Route::post('/ecdc_domains/update', [ECDCDomainController::class, 'update']);
        Route::post('/ecdc_domains/destroy', [ECDCDomainController::class, 'destroy']);

        Route::post('/ecdc_domains/{ecdc_domain}/competencies/store', [ECDCCompetencyController::class, 'store'])->whereNumber('ecdc_domain');
        Route::post('/ecdc_domains/{ecdc_domain}/competencies/update', [ECDCCompetencyController::class, 'update'])->whereNumber('ecdc_domain');
        Route::post('/ecdc_domains/{ecdc_domain}/competencies/destroy', [ECDCCompetencyController::class, 'destroy'])->whereNumber('ecdc_domain');
    });

    // Academic Year
    Route::middleware('permission:academic_years.view')->group(function () {
        Route::get('/academic_years', [AcademicYearController::class, 'index']);
    });
    Route::middleware('permission:academic_years.manage')->group(function () {
        Route::post('/academic_years/store', [AcademicYearController::class, 'store']);
        Route::post('/academic_years/update', [AcademicYearController::class, 'update']);
        Route::post('/academic_years/destroy', [AcademicYearController::class, 'destroy']);
    });

    // District
    Route::middleware('permission:districts.view')->group(function () {
        Route::get('/districts', [DistrictController::class, 'index']);
    });
    Route::middleware('permission:districts.manage')->group(function () {
        Route::post('/districts/store', [DistrictController::class, 'store']);
        Route::post('/districts/update', [DistrictController::class, 'update']);
        Route::post('/districts/destroy', [DistrictController::class, 'destroy']);
    });

    // School
    Route::middleware('permission:schools.view')->group(function () {
        Route::get('/schools', [SchoolController::class, 'index']);
        Route::get('/schools/edit', [SchoolController::class, 'edit']);
    });
    Route::middleware('permission:schools.manage')->group(function () {
        Route::get('/schools/create', [SchoolController::class, 'create']);
        Route::get('/schools/{school}/edit', [SchoolController::class, 'edit']);
        Route::post('/schools/store', [SchoolController::class, 'store']);
        Route::post('/schools/update', [SchoolController::class, 'update']);
        Route::post('/schools/destroy', [SchoolController::class, 'destroy']);
    });

    // Grade Level
    Route::middleware('permission:grade_levels.view')->group(function () {
        Route::get('/grade_levels', [GradeLevelController::class, 'index']);
    });
    Route::middleware('permission:grade_levels.manage')->group(function () {
        Route::post('/grade_levels/store', [GradeLevelController::class, 'store']);
        Route::post('/grade_levels/update', [GradeLevelController::class, 'update']);
        Route::post('/grade_levels/destroy', [GradeLevelController::class, 'destroy']);
    });

    // Subject
    Route::middleware('permission:subjects.view')->group(function () {
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::get('/subjects/{subject}', [SubjectController::class, 'show']);
    });
    Route::middleware('permission:subjects.manage')->group(function () {
        Route::post('/subjects/store', [SubjectController::class, 'store']);
        Route::post('/subjects/update', [SubjectController::class, 'update']);
        Route::post('/subjects/destroy', [SubjectController::class, 'destroy']);
    });
    Route::middleware('permission:subject_components.manage')->group(function () {
        Route::post('/subject_components/store', [SubjectComponentController::class, 'store']);
        Route::post('/subject_components/update', [SubjectComponentController::class, 'update']);
        Route::post('/subject_components/destroy', [SubjectComponentController::class, 'destroy']);
    });

    // Semester
    Route::middleware('permission:semesters.view')->group(function () {
        Route::get('/semesters', [SemesterController::class, 'index']);
    });
    Route::middleware('permission:semesters.manage')->group(function () {
        Route::post('/semesters/store', [SemesterController::class, 'store']);
        Route::post('/semesters/update', [SemesterController::class, 'update']);
        Route::post('/semesters/destroy', [SemesterController::class, 'destroy']);
    });

    // Track
    Route::middleware('permission:tracks.view')->group(function () {
        Route::get('/tracks', [TrackController::class, 'index']);
    });
    Route::middleware('permission:tracks.manage')->group(function () {
        Route::post('/tracks/store', [TrackController::class, 'store']);
        Route::post('/tracks/update', [TrackController::class, 'update']);
        Route::post('/tracks/destroy', [TrackController::class, 'destroy']);
    });

    // Strand
    Route::middleware('permission:strands.view')->group(function () {
        Route::get('/strands', [StrandController::class, 'index']);
    });
    Route::middleware('permission:strands.manage')->group(function () {
        Route::post('/strands/store', [StrandController::class, 'store']);
        Route::post('/strands/update', [StrandController::class, 'update']);
        Route::post('/strands/destroy', [StrandController::class, 'destroy']);
    });

    // Course
    Route::middleware('permission:courses.view')->group(function () {
        Route::get('/courses', [CourseController::class, 'index']);
    });
    Route::middleware('permission:courses.manage')->group(function () {
        Route::post('/courses/store', [CourseController::class, 'store']);
        Route::post('/courses/update', [CourseController::class, 'update']);
        Route::post('/courses/destroy', [CourseController::class, 'destroy']);
    });

    // User
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create']);
        Route::get('/users/{user}/edit', [UserController::class, 'edit']);
        Route::get('/users/{user}', [UserController::class, 'show']);
    });
    Route::middleware('permission:users.manage')->group(function () {
        Route::post('/users/store', [UserController::class, 'store']);
        Route::post('/users/update', [UserController::class, 'update']);
        Route::post('/users/destroy', [UserController::class, 'destroy']);
    });
    Route::middleware('permission:users.reset')->group(function () {
        Route::post('/users/reset', [UserController::class, 'reset']);
    });
    Route::middleware('permission:users.classification')->group(function () {
        Route::post('/users/classifications/{user}/update', [ClassificationHistoryController::class, 'update']);
    });

    // Supervisors : Edit
    Route::middleware('permission:users.manage')->group(function () {
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
    });
    Route::middleware('permission:users.upload')->group(function () {
        Route::post('/school_supervisors/upload', [SchoolSupervisorController::class, 'upload']);
    });

    // API : Lookups
    Route::middleware('permission:api.lookups')->group(function () {
        Route::post('/getArea', [APIController::class, 'getArea']);
        Route::post('/getStrands', [APIController::class, 'getStrands']);
        Route::post('/getCourses', [APIController::class, 'getCourses']);
        Route::post('/getTeachers', [APIController::class, 'getTeachers']);
    });

    // Sections
    Route::middleware('permission:sections.view')->group(function () {
        Route::get('/sections', [SectionController::class, 'index']);
    });
    Route::middleware('permission:sections.manage')->group(function () {
        Route::post('/sections/store', [SectionController::class, 'store']);
        Route::post('/sections/update', [SectionController::class, 'update']);
        Route::post('/sections/destroy', [SectionController::class, 'destroy']);
    });
    Route::middleware('permission:sections.upload')->group(function () {
        Route::post('/sections/upload', [SectionController::class, 'upload']);
    });

    // Classroom
    Route::middleware('permission:classrooms.view')->group(function () {
        Route::get('/classrooms', [ClassroomController::class, 'index']);
        // Constrained to a numeric id on purpose. An unconstrained
        // `/classrooms/{classroom}` also matches `/classrooms/create`, and
        // because this group is registered first that literal route lost to
        // the parameter and resolved the string "create" as a Classroom key,
        // answering 404 instead of rendering the form.
        Route::get('/classrooms/{classroom}', [ClassroomController::class, 'show'])
            ->whereNumber('classroom');
    });
    Route::middleware('permission:classrooms.manage')->group(function () {
        Route::get('/classrooms/create', [ClassroomController::class, 'create']);
        Route::post('/classrooms/store', [ClassroomController::class, 'store']);
        Route::post('/classrooms/update', [ClassroomController::class, 'update']);
        Route::post('/classrooms/destroy', [ClassroomController::class, 'destroy']);
    });
    Route::middleware('permission:classrooms.upload')->group(function () {
        Route::post('/classrooms/upload', [ClassroomController::class, 'upload']);
    });

    // Teacher Classes
    Route::middleware('permission:teacher_classes.view')->group(function () {
        Route::get('/teacher_classes/{teacher_class}', [TeacherClassController::class, 'show']);
    });
    Route::middleware('permission:teacher_classes.manage')->group(function () {
        Route::post('/teacher_classes/store', [TeacherClassController::class, 'store']);
        Route::post('/teacher_classes/add_student', [TeacherClassController::class, 'add_student']);
        Route::post('/teacher_classes/destroy', [TeacherClassController::class, 'destroy']);
        Route::post('/teacher_classes/update_student_status', [TeacherClassController::class, 'update_student_status']);
        Route::post('/student_classes/sync', [StudentClassController::class, 'sync']);
    });
    Route::middleware('permission:teacher_classes.upload')->group(function () {
        Route::post('/teacher_classes/upload', [TeacherClassController::class, 'upload']);
    });

    // Students
    Route::middleware('permission:students.view')->group(function () {
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/data', [StudentController::class, 'data']);
    });
    Route::middleware('permission:students.manage')->group(function () {
        Route::get('/students/create', [StudentController::class, 'create']);
        Route::get('/students/{student}/edit', [StudentController::class, 'edit']);
        Route::post('/students/store', [StudentController::class, 'store']);
        Route::post('/students/update', [StudentController::class, 'update']);
    });
    Route::middleware('permission:students.upload')->group(function () {
        Route::post('/students/upload', [StudentController::class, 'upload']);
    });

    // Assessment answers. The Assessment module index screen was removed with
    // its controller methods; these endpoints remain live and are still reached
    // from the class assessment and term exam upload screens.
    Route::middleware('permission:assessments.manage')->group(function () {
        Route::put('/questions/{id}/update-answer-key', [QuestionController::class, 'updateAnswerKey']);
    });
    Route::middleware('permission:assessments.upload')->group(function () {
        Route::post('/student_answers/upload', [StudentAnswerController::class, 'upload']);
        Route::post('/student_answers/batch_update', [StudentAnswerController::class, 'batch_update']);
    });

    // Term Exams
    Route::middleware('permission:term_exams.view')->group(function () {
        Route::get('/term-exams/', [TermExamController::class, 'index']);
        Route::get('/term-exams/{assessment}', [TermExamController::class, 'show']);
    });
    Route::middleware('permission:term_exams.upload')->group(function () {
        Route::post('/term-exams/upload', [TermExamController::class, 'upload']);
    });

    // Diagnostics
    Route::middleware('permission:diagnostics.view')->group(function () {
        Route::get('/diagnostics/', [DiagnosticController::class, 'index']);
        Route::get('/diagnostics/{assessment}', [DiagnosticController::class, 'show']);
    });
    Route::middleware('permission:diagnostics.upload')->group(function () {
        Route::post('/diagnostics/upload', [DiagnosticController::class, 'upload']);
    });

    // Class Assessments
    Route::middleware('permission:class_assessments.view')->group(function () {
        // A student's own results screen. It is guarded by class_assessments
        // rather than students.view because the Student role only holds the
        // former, and it reads the signed in student's own record.
        Route::get('/students/class_assessments', [StudentController::class, 'studentsClassAssessment']);
        Route::get('/students/class_assessments/{class_assessment}',
            [StudentController::class, 'studentsClassAssessmentShow']);
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
    });

    // ECDC
    Route::middleware('permission:ecdcs.view')->group(function () {
        Route::get('/ecdcs', [ECDCController::class, 'index']);
        Route::get('/ecdcs/print_result', [ECDCController::class, 'print']);
        Route::get('/ecdcs/classroom/{classroom}', [ECDCController::class, 'show']);
        Route::get('/ecdcs/classroom/{classroom}/card/{student}', [ECDCController::class, 'card']);
        Route::get('/ecdcs/classroom/{classroom}/card/{student}/print', [ECDCController::class, 'print']);
    });
    Route::middleware('permission:ecdcs.manage')->group(function () {
        Route::get('/ecdcs/classroom/{classroom}/create', [ECDCController::class, 'create']);
        Route::post('/ecdcs/store', [ECDCController::class, 'store']);
        Route::post('/ecdcs/update', [ECDCController::class, 'update']);
    });
    Route::middleware('permission:ecdcs.upload')->group(function () {
        Route::post('/ecdcs/upload', [ECDCController::class, 'upload']);
    });
    Route::middleware('permission:ecdcs.download')->group(function () {
        Route::get('/ecdcs/classroom/{classroom}/report/download', [ECDCController::class, 'download_classroom_report'])
            ->name('ecdcs.classroom.report.download');
        Route::get('/ecdcs/{ecdc}/students/{student_id}/download', [ECDCController::class, 'download_student_result']);
        Route::get('/ecdcs/{classroom}/download_template', [ECDCController::class, 'download_template']);
    });

    // Reports
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index']);
    });
    Route::middleware('permission:reports.generate')->group(function () {
        Route::post('/reports/generate', [ReportController::class, 'generate']);
    });

    // Item Bank
    Route::middleware('permission:item_banks.view')->group(function () {
        Route::get('/item_banks', [ItemBankController::class, 'index']);
    });

    // Summatives
    Route::middleware('permission:summatives.view')->group(function () {
        Route::get('/summatives/', [SummativeController::class, 'index']);
        Route::get('/summatives/{assessment}', [SummativeController::class, 'show']);
    });
    Route::middleware('permission:summatives.upload')->group(function () {
        Route::post('/summatives/upload', [SummativeController::class, 'upload']);
    });

    // API : Lookups (report, assessment and student filters)
    //
    // The dropdowns across those screens post to these endpoints, so they share
    // the api.lookups gate with the lookup group above. Every seeded role that
    // holds a module view permission also holds api.lookups, so no curated role
    // loses access; a custom role granted a module view permission must also be
    // granted api.lookups before its filters will load.
    Route::middleware('permission:api.lookups')->group(function () {
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
    });
});
