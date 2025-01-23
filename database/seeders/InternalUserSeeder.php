<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use function Laravel\Prompts\warning;

class InternalUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminEmail = config('auth.super_admin.email');

        if (! $superAdminEmail) {
            warning('Could not seed users - Is your environment file filled in?');

            return;
        }

        // Retrieve passwords
        $adminPassword = config('auth.super_admin.password');
        $demoPassword = config('auth.demo_user.password') ?: $adminPassword;

        // Seed admin users
        Role::query()->each(function (Role $role) use ($superAdminEmail, $adminPassword) {
            $this->seedUser($role, 'Artcore', $superAdminEmail, $adminPassword);
        });

        // Seed demo users when available
        if ($demoEmail = config('auth.demo_user.email')) {
            Role::query()->each(function (Role $role) use ($demoEmail, $demoPassword) {
                $this->seedUser($role, 'Customer', $demoEmail, $demoPassword);
            });
        }
    }

    private function seedUser(Role $role, string $lastName, string $baseEmail, ?string $basePassword = null): void
    {
        // Data for the user
        $data = [
            'name' => "$role->label $lastName",
            'password' => $basePassword ?: Str::password(),
        ];

        // Attempt to create the user
        $user = User::firstOrCreate(['email' => $this->getEmail($role, $baseEmail)], $data);

        // Give passed role
        if ($user->wasRecentlyCreated) {
            $user->assignRole($role);
        }
    }

    private function getEmail(Role $role, string $baseEmail): string
    {
        $email = Str::of($baseEmail);

        // Split the email around the "at sign" (@)
        $before = $email->before('@');
        $after = $email->after('@');

        // Suffix the email by the role
        if ($role->name !== RoleEnum::SUPER_ADMIN->value) {
            return "$before+$role->name@$after";
        }

        return "$before@$after";
    }
}
