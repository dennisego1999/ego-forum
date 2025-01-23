<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create default roles and permissions
        $roles = $this->createDefaultRoles();
        $permissions = $this->createDefaultModelPermissions();

        // Give (new) roles (new) permissions
        $this->giveDefaultPermissions($roles, $permissions);
    }

    private function createDefaultRoles(): Collection
    {
        $roles = RoleEnum::cases();
        $createdRoles = collect();

        foreach ($roles as $role) {
            $createdRole = Role::updateOrCreate(
                ['name' => $role->value],
                ['guard_name' => 'web']
            );

            $createdRoles->push($createdRole);
        }

        return $createdRoles;
    }

    private function createDefaultModelPermissions(): Collection
    {
        // Get the models having permissions
        $models = Permission::getModels();
        $createdPermissions = collect();

        // Ensure the model permissions exist
        foreach ($models as $model) {
            // Retrieve the list of permissions for the model
            $instance = app($model);
            $permissions = Permission::getModelPermissions($instance);

            // Decide the model permissions name
            $modelName = Permission::getModelName($model);

            // Map the available permissions
            $createdPermissions = collect([
                ...$createdPermissions,
                ...$this->ensurePermissionsExist(
                    collect($permissions)->map(static function (string $permission) use ($modelName) {
                        return "{$permission}_$modelName";
                    })
                ),
            ]);
        }

        return $createdPermissions;
    }

    private function ensurePermissionsExist(iterable $permissions): Collection
    {
        $createdPermissions = collect();

        foreach ($permissions as $permission) {
            $createdPermission = Permission::firstOrCreate(['name' => $permission]);
            $createdPermissions->push($createdPermission);
        }

        return $createdPermissions;
    }

    private function giveDefaultPermissions(Collection $availableRoles, Collection $availablePermissions): void
    {
        // Get the permissions per role to seed when either are new
        $defaultPermissionRoles = collect([
            'admin' => [
                // Permission::for('view_any', User::class),
                // Permission::for('create', User::class),
                // Permission::for('update', User::class),
                // Permission::for('delete', User::class),
            ],
        ]);

        // Give permissions to roles
        $defaultPermissionRoles
            ->each(function (array $permissions) use (&$availablePermissions) {
                $availablePermissions = collect([
                    ...$availablePermissions,
                    ...$this->ensurePermissionsExist($permissions),
                ]);
            })
            ->map(function (array $permissions, $role) use ($availableRoles, $availablePermissions) {
                return array_filter($permissions, static function ($permission) use ($availableRoles, $availablePermissions, $role) {
                    $availableRole = $availableRoles->firstWhere('name', $role);

                    // Sync all permissions when the role was newly created
                    if ($availableRole->wasRecentlyCreated) {
                        return true;
                    }

                    // Keep the permission if it was newly created
                    return $availablePermissions->firstWhere('name', $permission)->wasRecentlyCreated;
                });
            })
            ->each(function (array $permissions, string $role) use ($availableRoles) {
                // Abort without permissions
                if (blank($permissions)) {
                    return;
                }

                // Sync new permissions
                $availableRoles->firstWhere('name', $role)
                    ?->permissions()
                    ->syncWithoutDetaching(
                        Permission::query()
                            ->whereIn('name', $permissions)
                            ->pluck('id')
                    );
            });
    }
}
