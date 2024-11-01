<?php
declare(strict_types=1);

namespace Database\Seeders\Tests\V1;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuthorsControllerSeeder extends Seeder
{
    const USERS_TO_CREATE = 25;
    const RECIPES_TO_CREATE = 5;

    public function run()
    {

        for ($i = 0; $i < self::USERS_TO_CREATE; $i++) {
            $user = User::factory(1)->create();
            Recipe::factory(self::RECIPES_TO_CREATE)->create([
                'user_id' => $user[0]->id,
            ]);
        }

        // without recipes, will not be regarded as an author
        User::factory(1)->create();


    }
}
