<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

use function Laravel\Prompts\warning;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Seed users
        $this->seedInternalUsers();
        $this->seedLocalEditorsWithPosts();
    }

    private function seedInternalUsers(): void
    {
        $superAdminEmail = config('auth.super_admin.email');

        if (! $superAdminEmail) {
            warning('Could not seed users - Is your environment file filled in?');

            return;
        }

        // Retrieve passwords
        $adminPassword = config('auth.super_admin.password');
        $demoPassword = config('auth.demo_user.password') ?: $adminPassword;

        // Seed internal users
        Role::query()->each(function (Role $role) use ($superAdminEmail, $adminPassword) {
            $this->seedUser($role->label, 'Artcore', $role, $superAdminEmail, $adminPassword);
        });

        // Seed demo users
        if ($demoEmail = config('auth.demo_user.email')) {
            Role::query()->each(function (Role $role) use ($demoEmail, $demoPassword) {
                $this->seedUser($role->label, 'Demo', $role, $demoEmail, $demoPassword);
            });
        }
    }

    private function seedLocalEditorsWithPosts(): void
    {
        if (! App::isLocal()) {
            // Only for local development
            return;
        }

        // Create editors
        $editors = User::factory(10)
            ->create()
            ->each(function ($user) {
                $user->assignRole(RoleEnum::EDITOR->value);
            });

        // Create posts and use the created editors to assign the posts to
        $posts = Post::factory(200)->recycle($editors)->create();

        // Create comments and use the created editors and posts to assign the comments to
        $comments = Comment::factory(100)->recycle($editors)->recycle($posts)->create();
    }

    private function seedUser(string $firstName, string $lastName, Role $role, string $baseEmail, ?string $basePassword = null): void
    {
        // Data for the user
        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
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
