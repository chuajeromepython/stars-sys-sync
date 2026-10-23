<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\School;
use App\Support\RoleDataScope;
use App\Support\UserHierarchy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleScopedDistrictController extends Controller
{
    public function index(Request $request, RoleDataScope $roleDataScope, UserHierarchy $userHierarchy): View
    {
        $user = $request->user();
        $hierarchy = $userHierarchy->forUser($user);
        $page = [
            'name' => 'District',
            'title' => 'District Management',
            'crumb' => ['District' => '/districts'],
        ];
        $districts = $roleDataScope->districtsFor($user)->orderBy('name')->get();
        $divisions = $roleDataScope->divisionsFor($user)->orderBy('name')->get();
        $division_id = $hierarchy['division_id'];

        return view('districts.index', compact('page', 'districts', 'divisions', 'division_id'));
    }

    public function store(Request $request, RoleDataScope $roleDataScope): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'division_id' => ['required', 'integer', 'exists:tbl_divisions,id'],
        ]);
        $divisionId = (int) $validated['division_id'];

        abort_unless($roleDataScope->divisionsFor($request->user())->whereKey($divisionId)->exists(), 404);

        $districtExists = District::query()
            ->where('name', $validated['name'])
            ->where('division_id', $divisionId)
            ->exists();

        if ($districtExists) {
            return back()->withErrors(['error' => 'District already exists!']);
        }

        $district = new District;
        $district->name = $validated['name'];
        $district->division_id = $divisionId;
        $district->save();

        return redirect('/districts')->with('success', 'New district has been added successfully.');
    }

    public function update(Request $request, RoleDataScope $roleDataScope): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'division_id' => ['required', 'integer', 'exists:tbl_divisions,id'],
        ]);
        $district = $roleDataScope->districtsFor($request->user())
            ->whereKey((int) $validated['id'])
            ->firstOrFail();
        $divisionId = (int) $validated['division_id'];

        abort_unless($roleDataScope->divisionsFor($request->user())->whereKey($divisionId)->exists(), 404);

        $duplicateExists = District::query()
            ->where('name', $validated['name'])
            ->where('division_id', $divisionId)
            ->where('id', '<>', $district->getKey())
            ->exists();

        if ($duplicateExists) {
            return back()->withErrors(['error' => 'District already exists!']);
        }

        $district->name = $validated['name'];
        $district->division_id = $divisionId;
        $district->save();

        return back()->with('success', 'District has been updated successfully.');
    }

    public function destroy(Request $request, RoleDataScope $roleDataScope): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);
        $district = $roleDataScope->districtsFor($request->user())
            ->whereKey((int) $validated['id'])
            ->firstOrFail();
        $schoolCount = School::query()->where('district_id', $district->getKey())->count();

        if ($schoolCount > 0) {
            $noun = $schoolCount === 1 ? 'school' : 'schools';

            return back()->withErrors([
                'error' => $schoolCount.' '.$noun.' found under '.$district->name.' district.',
            ]);
        }

        $district->delete();

        return back()->with('success', 'District has been deleted successfully.');
    }
}
