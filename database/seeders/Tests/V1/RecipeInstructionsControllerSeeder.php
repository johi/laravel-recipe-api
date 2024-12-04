<?php

namespace Database\Seeders\Tests\V1;

use App\Models\RecipeInstruction;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecipeInstructionsControllerSeeder extends Seeder
{
    const INSTRUCTIONS_TO_CREATE = 10;

    public function run(): void
    {
        $user1 = User::factory(1)->create([
            'email' => 'test1@example.com',
        ]);

        $recipe = Recipe::factory(1)->create([
            'user_id' => $user1[0]->id
        ]);

        RecipeInstruction::factory(self::INSTRUCTIONS_TO_CREATE)->create([
            'recipe_id' => $recipe[0]->id
        ]);
    }
}