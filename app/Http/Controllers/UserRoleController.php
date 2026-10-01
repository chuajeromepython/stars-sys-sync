<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignUserRolesRequest;
use App\Models\User;
use App\Services\Rbac\RbacService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function __construct(private RbacService $rbac) {}

    /**
     * Currently assigned roles for a user, as JSON for the assignment modal.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => trim(implode(' ', array_filter([
                    $user->person?->first_name,
                    $user->person?->middle_name,
                    $user->person?->last_name,
                ]))),
            ],
            'roles' => Role::orderBy('name')->get(['name'])->pluck('name'),
            'assigned' => $user->roles->pluck('name'),
        ]);
    }

    public function update(AssignUserRolesRequest $request, User $user): RedirectResponse
    {
        $this->rbac->assignRoles($user, (array) $request->validated('roles'));

        return redirect()->route('users.index')
            ->with('success', 'Roles for '.$user->username.' have been updated successfully.');
    }

    /**
     * Detach every role from the given user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->rbac->assignRoles($user, []);

        return redirect()->route('users.index')
            ->with('success', 'All roles for '.$user->username.' have been removed.');
    }
}
