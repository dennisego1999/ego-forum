<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'commentable_id' => Post::factory(),
            'commentable_type' => Post::class,
            'body' => json_encode('<div>'.fake()->realText(250).'</div>'),
        ];
    }
}
