<?php

namespace App\Http\Controllers;

use App\Support\RoleDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleScopedLookupController extends Controller
{
    public function districtsForDivision(Request $request, RoleDataScope $roleDataScope): JsonResponse
    {
        $validated = $request->validate([
            'division_id' => ['required', 'integer', 'exists:tbl_divisions,id'],
        ]);
        $divisionId = (int) $validated['division_id'];

        abort_unless($roleDataScope->divisionsFor($request->user())->whereKey($divisionId)->exists(), 404);

        $districts = $roleDataScope->districtsFor($request->user())
            ->where('division_id', $divisionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($districts);
    }
}
