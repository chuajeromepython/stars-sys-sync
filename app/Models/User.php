<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\Rbac\RbacService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public $timestamps = false;

    public $remember_token = false;

    protected $table = 'tbl_users';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Seeds the spatie role that mirrors tbl_users.classification.
     *
     * The grant is additive, so a user may hold several roles at once and roles
     * assigned through the role management screens survive any later save. The
     * hook only runs on creation and on an actual classification change so it
     * never rewrites the role set of an untouched user.
     */
    protected static function booted(): void
    {
        static::saved(function (User $user): void {
            if (! $user->wasRecentlyCreated && ! $user->wasChanged('classification')) {
                return;
            }

            app(RbacService::class)->syncUser($user);
        });
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id', 'id');
    }

    public function divisionadministrator()
    {
        return $this->belongsTo(DivisionAdministrator::class, 'id', 'user_id');
    }

    public function getDetails($user_id) {}
}
