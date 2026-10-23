<?php

namespace App\Http\Controllers;

use App\Models\DepartmentHead;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\SchoolType;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\RoleDataScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleScopedSchoolController extends Controller
{
    public function index(Request $request, RoleDataScope $roleDataScope): View
    {
        $page = [
            'name' => 'School',
            'title' => 'School Management',
            'crumb' => ['School' => '/schools'],
        ];
        $schools = $roleDataScope->schoolsFor($request->user())
            ->join('tbl_districts', 'tbl_schools.district_id', '=', 'tbl_districts.id')
            ->join('tbl_school_categories', 'tbl_schools.school_category_id', '=', 'tbl_school_categories.id')
            ->join('tbl_school_types', 'tbl_schools.school_type_id', '=', 'tbl_school_types.id')
            ->select(
                'tbl_schools.*',
                'tbl_schools.name as school',
                'tbl_districts.name as district',
                'tbl_school_categories.category',
                'tbl_school_types.type'
            )
            ->orderBy('tbl_schools.name')
            ->get();

        return view('schools.index', compact('page', 'schools'));
    }

    public function create(Request $request, RoleDataScope $roleDataScope): View
    {
        $page = [
            'name' => 'School',
            'title' => 'School Management',
            'crumb' => ['School' => '/schools', 'Add School' => '/schools/create'],
        ];
        $districts = $roleDataScope->districtsFor($request->user())->orderBy('name')->get();
        $categories = SchoolCategory::query()->orderBy('category')->get();
        $types = SchoolType::query()->orderBy('type')->get();

        return view('schools.create', compact('page', 'districts', 'categories', 'types'));
    }

    public function store(Request $request, RoleDataScope $roleDataScope): RedirectResponse
    {
        $validated = $request->validate($this->schoolRules());
        $district = $roleDataScope->districtsFor($request->user())
            ->whereKey((int) $validated['district_id'])
            ->firstOrFail();

        if ($this->duplicateExists($validated)) {
            return back()->withErrors(['error' => 'School already exists!']);
        }

        $school = new School;
        $this->fillSchool($school, $validated, $district->getKey());
        $school->save();

        return redirect('/schools')->with('success', 'New school has been added successfully.');
    }

    public function edit(Request $request, int $school, RoleDataScope $roleDataScope): View
    {
        $school = $roleDataScope->schoolsFor($request->user())->whereKey($school)->firstOrFail();
        $districts = $roleDataScope->districtsFor($request->user())->orderBy('name')->get();
        $divisions = $roleDataScope->divisionsFor($request->user())->orderBy('name')->get();
        $currentDivision = $divisions->firstWhere(
            'id',
            $districts->firstWhere('id', $school->district_id)?->division_id
        );
        $page = [
            'name' => 'School',
            'title' => 'Edit School',
            'crumb' => ['School' => '/schools', 'Edit School' => ''],
        ];
        $categories = SchoolCategory::query()->orderBy('category')->get();
        $types = SchoolType::query()->orderBy('type')->get();

        return view('schools.edit', compact(
            'page',
            'districts',
            'divisions',
            'categories',
            'types',
            'school',
            'currentDivision'
        ));
    }

    public function update(Request $request, RoleDataScope $roleDataScope): RedirectResponse
    {
        $validated = $request->validate($this->schoolRules(true));
        $school = $roleDataScope->schoolsFor($request->user())
            ->whereKey((int) $validated['id'])
            ->firstOrFail();
        $district = $roleDataScope->districtsFor($request->user())
            ->whereKey((int) $validated['district_id'])
            ->firstOrFail();

        if ($this->duplicateExists($validated, $school->getKey())) {
            return back()->withErrors(['error' => 'School already exists!']);
        }

        $this->fillSchool($school, $validated, $district->getKey());
        $school->save();

        return redirect('/schools')->with('success', 'School has been updated successfully.');
    }

    public function destroy(Request $request, RoleDataScope $roleDataScope): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);
        $school = $roleDataScope->schoolsFor($request->user())
            ->whereKey((int) $validated['id'])
            ->firstOrFail();
        $teacherCount = Teacher::query()->where('school_id', $school->getKey())->count();
        $studentCount = Student::query()->where('school_id', $school->getKey())->count();
        $departmentHeadCount = DepartmentHead::query()->where('school_id', $school->getKey())->count();

        if ($teacherCount > 0 || $studentCount > 0 || $departmentHeadCount > 0) {
            $errors = ['There are data found under '.$school->name.' :'];

            if ($teacherCount > 0) {
                $errors[] = $teacherCount.' active '.($teacherCount === 1 ? 'Teacher' : 'Teachers').' found.';
            }
            if ($studentCount > 0) {
                $errors[] = $studentCount.' active '.($studentCount === 1 ? 'Student' : 'Students').' found.';
            }
            if ($departmentHeadCount > 0) {
                $errors[] = $departmentHeadCount.' active '
                    .($departmentHeadCount === 1 ? 'Department Head' : 'Department Heads').' found.';
            }

            return back()->withErrors(['error' => $errors]);
        }

        $school->delete();

        return back()->with('success', 'School has been deleted successfully.');
    }

    public function legacyEdit(): RedirectResponse
    {
        return redirect('/schools');
    }

    /**
     * @return array<string, mixed>
     */
    private function schoolRules(bool $includeId = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'code' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('tbl_school_categories', 'id')->whereNull('deleted_at'),
            ],
            'type_id' => [
                'required',
                'integer',
                Rule::exists('tbl_school_types', 'id')->whereNull('deleted_at'),
            ],
            'district_id' => ['required', 'integer', 'exists:tbl_districts,id'],
        ];

        if ($includeId) {
            $rules['id'] = ['required', 'integer'];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function duplicateExists(array $validated, ?int $exceptId = null): bool
    {
        $query = School::query()
            ->where('name', $validated['name'])
            ->where('code', $validated['code'])
            ->where('district_id', $validated['district_id']);

        if ($exceptId !== null) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->exists();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function fillSchool(School $school, array $validated, int|string $districtId): void
    {
        $school->name = $validated['name'];
        $school->address = $validated['address'];
        $school->code = $validated['code'];
        $school->school_category_id = $validated['category_id'];
        $school->school_type_id = $validated['type_id'];
        $school->district_id = $districtId;
    }
}
