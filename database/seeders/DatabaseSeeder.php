<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Instruction;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(10)->create();
        $categories = collect(['Starters', 'Main dishes', 'Side dishes', 'Dessert', 'Bakery', 'Drinks']);
        $createdCategories = collect([]);

        foreach ($categories as $category) {
            $createdCategory = Category::factory(1)
                ->create([
                    'title' => $category,
                ]);
            $createdCategories->push($createdCategory);
        }

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
                        'order' => $i
                    ]);
            }
        }
    }
}
