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
        // Get credentials
        $internalUserEmail = config('auth.internal_user.email');
        $internalUserPassword = config('auth.internal_user.password');
        $demoUserEmail = config('auth.demo_user.email');
        $demoUserPassword = config('auth.demo_user.password') ?: $internalUserPassword;

        // INTERNAL - Create a user for every role and assign it. Use the internal user credentials.
        if ($internalUserEmail) {
            Role::query()->each(function (Role $role) use ($internalUserEmail, $internalUserPassword) {
                $this->seedUser($role->label, 'Artcore', $role, $internalUserEmail, $internalUserPassword);
            });
        }

        // DEMO - Create a user for every role and assign it. Use the demo user credentials.
        if ($demoUserEmail) {
            Role::query()->each(function (Role $role) use ($demoUserEmail, $demoUserPassword) {
                $this->seedUser($role->label, 'Demo', $role, $demoUserEmail, $demoUserPassword);
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
        Comment::factory(100)->recycle($editors)->recycle($posts)->create();
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
