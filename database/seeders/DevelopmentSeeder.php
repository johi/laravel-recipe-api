<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Instruction;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(1)->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'is_admin' => true
        ]);

        $users = User::factory(10)->create();
        $createdCategories = Category::all();
        $recipes = Recipe::factory(100)
            ->recycle($users)
            ->recycle($createdCategories)
            ->create();

        foreach ($recipes as $recipe) {
            Ingredient::factory(rand(2, 10))
                ->create([
                    'recipe_id' => $recipe->id,
                ]);
            $amount = rand(1, 10);
            for ($i = 0; $i < $amount; $i++) {
                Instruction::factory(1)
                    ->create([
                        'recipe_id' => $recipe->id,
                        'order' => $i+1
                    ]);
            }
        }
    }
}
