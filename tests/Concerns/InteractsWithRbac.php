<?php

namespace Tests\Concerns;

use App\Enums\Role;
use App\Models\Person;
use App\Models\User;
use App\Services\Rbac\RbacService;

trait InteractsWithRbac
{
    /**
     * Creates every permission/role declared in the role permission matrix.
     */
    protected function provisionRbac(): void
    {
        app(RbacService::class)->provision();
    }

    /**
     * Provisions RBAC and returns a user holding the given role.
     */
    protected function userWithRole(Role $role, array $attributes = []): User
    {
        $this->provisionRbac();

        return $this->createUser($attributes + [
            'username' => $attributes['username'] ?? str($role->value)->slug().'-'.uniqid().'@deped.gov.ph',
            'classification' => $role->value,
        ]);
    }

    /**
     * Creates a persisted user without a recognised classification, so role
     * assignment can be exercised in isolation from the legacy column.
     */
    protected function createUser(array $attributes = []): User
    {
        $user = new User;
        $user->username = $attributes['username'] ?? 'user-'.uniqid().'@deped.gov.ph';
        $user->password = bcrypt($attributes['password'] ?? 'password123');
        // The column is still NOT NULL until the staged removal, so use a value
        // that deliberately maps to no role rather than null.
        $user->classification = $attributes['classification'] ?? 'Unassigned';
        $user->status = $attributes['status'] ?? true;
        $user->person_id = $attributes['person_id'] ?? $this->createPerson()->id;
        $user->save();

        return $user->refresh();
    }

    private function createPerson(): Person
    {
        $person = new Person;
        $person->first_name = 'Test';
        $person->middle_name = '';
        $person->last_name = 'User';
        $person->suffix = '';
        $person->gender = 'M';
        $person->birth_date = '1990-01-01';
        $person->save();

        return $person;
    }
}
