<?php

namespace App\Http\Controllers;

use App\Models\DepartmentHead;
use App\Models\District;
use App\Models\Division;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\Student;
use App\Models\Teacher;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'School',
            'title' => 'School Management',
            'crumb' => ['School' => '/schools'],
        ];

        $schools = School::select(
            'tbl_schools.*', 'category', 'type',
            'tbl_schools.name as school',
            'tbl_districts.name as district',
            'tbl_schools.id as id'
        )->join('tbl_districts', 'tbl_schools.district_id', 'tbl_districts.id')
            ->join('tbl_school_categories', 'tbl_schools.school_category_id', 'tbl_school_categories.id')
            ->join('tbl_school_types', 'tbl_schools.school_type_id', 'tbl_school_types.id')
            ->where('tbl_districts.division_id', Auth::user()->divisionadministrator->division_id)
            ->get();

        return view('schools.index', compact(
            'page',
            'schools'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $page = [
            'name' => 'School',
            'title' => 'School Management',
            'crumb' => ['School' => '/schools', 'Add School' => '/schools/create'],
        ];

        $districts = District::all();
        $types = SchoolType::all();

        return view('schools.create', compact(
            'page', 'districts', 'types'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'code' => 'required',
            'category_id' => 'required|numeric',
            'type_id' => 'required|numeric',
            'district_id' => 'required|numeric',
        ]);

        // verify if school is existing
        $result = School::where([
            ['name', '=', $request->name],
            ['code', '=', $request->code],
            ['district_id', '=', $request->district_id],
        ])->get();

        // if school is not existing
        if (! count($result)) {

            $school = new School;
            $school->name = $request->name;
            $school->code = $request->code;
            $school->address = $request->address;
            $school->school_category_id = $request->category_id;
            $school->school_type_id = $request->type_id;
            $school->district_id = $request->district_id;
            $school->save();

            return redirect('/schools')->with('success', 'New school has been added successfully.');

        } else {
            return back()->withErrors('School already exists!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(School $school)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(School $school)
    {
        $page = [
            'name' => 'School',
            'title' => 'Edit School',
            'crumb' => ['School' => '/schools', 'Edit School' => ''],
        ];

        $types = SchoolType::all();
        $current_division = Division::find(District::where('id', $school->district_id)->value('division_id'));
        $districts = District::where('division_id', $current_division->id)->get();
        $divisions = Division::all();

        return view('schools.edit', compact(
            'page', 'districts', 'types',
            'school', 'current_division', 'divisions'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  School  $school
     * @return Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'code' => 'required',
            'category_id' => 'required|numeric',
            'type_id' => 'required|numeric',
            'district_id' => 'required|numeric',
        ]);

        // verify if school is existing
        $result = School::where([
            ['name', '=', $request->name],
            ['code', '=', $request->code],
            ['district_id', '=', $request->district_id],
            ['id', '<>', $request->id],
        ])->get();

        // if school is not existing
        if (! count($result)) {

            $school = School::find($request->id);
            $school->name = $request->name;
            $school->code = $request->code;
            $school->address = $request->address;
            $school->school_category_id = $request->category_id;
            $school->school_type_id = $request->type_id;
            $school->district_id = $request->district_id;
            $school->save();

            return redirect('/schools')->with('success', 'School has been updated successfully.');

        } else {
            return back()->withErrors('School already exists!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  School  $school
     * @return Response
     */
    public function destroy(Request $request)
    {
        $errors = [];
        $school = School::find($request->id);
        $teachers = Teacher::where('school_id', $request->id)->get();
        $students = Student::where('school_id', $request->id)->get();
        $department_heads = DepartmentHead::where('school_id', $request->id)->get();

        if (
            $teachers->count() == 0 &&
            $students->count() == 0 &&
            $department_heads->count() == 0
        ) {
            $school->delete();

            return back()->with('success', 'School has been deleted successfully.');
        } else {
            $errors[] = 'There are data found under '.$school->name.' :';

            if ($teachers->count() > 0) {
                $errors[] = ($teachers->count() == 1)
                ? $teachers->count().' active Teacher found.'
                : $teachers->count().' active Teachers found.';
            }
            if ($students->count() > 0) {
                $errors[] = ($students->count() == 1)
                ? $students->count().' active Student found.'
                : $students->count().' active Students found.';
            }
            if ($department_heads->count() > 0) {
                $errors[] = ($department_heads->count() == 1)
                ? $department_heads->count().' active Department Head found.'
                : $department_heads->count().' active Department Heads found.';
            }

            return back()->withErrors($errors);
        }
    }
}
