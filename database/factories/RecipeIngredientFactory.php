<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeIngredient>
 */
class RecipeIngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'title' => $this->faker->word(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'unit' => array_rand(array_flip(['g', 'kg', 'dl', 'tsp', 'cl'])),
        ];
    }
}
