<?php
declare(strict_types=1);

namespace Database\Seeders\Tests;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;


class RecipesControllerSeeder extends Seeder
{
    const RECIPES_TO_CREATE = 20;

    public function run(): void
    {
        $user1 = User::factory(1)->create([
            'email' => 'test1@example.com',
        ]);

        Recipe::factory(self::RECIPES_TO_CREATE)->create([
            'user_id' => $user1[0]->id
        ]);
    }
}
