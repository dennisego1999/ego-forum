<?php

namespace App\Traits;

use App\Enums\RoleEnum;

trait HasRoles
{
    use \Spatie\Permission\Traits\HasRoles {
        hasPermissionTo as baseHasPermissionTo;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole([RoleEnum::SUPER_ADMIN]);
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole([RoleEnum::ADMIN, RoleEnum::SUPER_ADMIN]);
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->baseHasPermissionTo($permission, $guardName);
    }
}
