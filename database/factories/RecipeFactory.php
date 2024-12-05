<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'preparation_time_minutes' => range(5, 120, 5)[array_rand(range(5, 120, 5), 1)],
            'servings' => 4,
        ];
    }
}
