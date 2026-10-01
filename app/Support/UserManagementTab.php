<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * The areas of user management that are presented as tabs inside the User
 * Management screen instead of as separate sidebar entries.
 */
enum UserManagementTab: string
{
    case Users = 'users.view';
    case Roles = 'roles.view';
    case Permissions = 'permissions.view';

    public function label(): string
    {
        return match ($this) {
            self::Users => 'User Accounts',
            self::Roles => 'Roles',
            self::Permissions => 'Permissions',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Users => 'fa-user',
            self::Roles => 'fa-user-tag',
            self::Permissions => 'fa-key',
        };
    }

    public function url(): string
    {
        return match ($this) {
            self::Users => '/users',
            self::Roles => '/roles',
            self::Permissions => '/permissions',
        };
    }

    /**
     * The key identifying this tab in the data endpoint.
     */
    public function key(): string
    {
        return match ($this) {
            self::Users => 'users',
            self::Roles => 'roles',
            self::Permissions => 'permissions',
        };
    }

    /**
     * The permission guarding a tab listing, for a tab key.
     *
     * Returns null for an unknown key so callers can reject it.
     */
    public static function permissionFor(string $key): ?string
    {
        foreach (self::cases() as $tab) {
            if ($tab->key() === $key) {
                return $tab->value;
            }
        }

        return null;
    }

    /**
     * The tabs the given user is allowed to open, in display order.
     *
     * @return list<self>
     */
    public static function availableFor(?Authenticatable $user): array
    {
        if (! $user instanceof User) {
            return [];
        }

        return array_values(array_filter(
            self::cases(),
            fn (self $tab): bool => $user->can($tab->value),
        ));
    }

    /**
     * The tab the current request belongs to, if any.
     */
    public static function current(): ?self
    {
        $path = '/'.ltrim(request()->path(), '/');

        foreach (self::cases() as $tab) {
            if (str_starts_with($path, $tab->url())) {
                return $tab;
            }
        }

        return null;
    }
}
