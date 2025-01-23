<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user): bool
    {
        return $user->can('viewAny', Role::class);
    }

    public function create(): bool
    {
        return false;
    }

    public function update(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function manage(User $user, Role $role): bool
    {
        // Never update properties of system roles
        if (in_array($role->name, RoleEnum::values(), true)) {
            return false;
        }

        return true;
    }

    public function updateProperties(User $user, Role $role): bool
    {
        // Bail if we cannot manage the role
        if ($user->cannot('manage', $role)) {
            return false;
        }

        // Allow if we can update the base role
        return $user->can('update', $role);
    }

    public function delete(User $user, Role $role): bool
    {
        // Bail if we cannot manage the role
        if ($user->cannot('manage', $role)) {
            return false;
        }

        // Prevent deleting roles with linked users
        if ($role->users()->exists()) {
            return false;
        }

        return $user->can('update', $role);
    }

    public function restore(User $user): bool
    {
        return $user->can('create', Role::class);
    }

    public function forceDelete(): bool
    {
        return false;
    }
}
