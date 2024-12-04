<?php
declare(strict_types=1);

namespace Database\Seeders\Tests\V1;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersControllerSeeder extends Seeder
{
    const USERS_TO_CREATE = 1;
    const RECIPES_TO_CREATE = 1;

    public function run()
    {
        User::factory(self::USERS_TO_CREATE)->create();
        Recipe::factory(self::RECIPES_TO_CREATE)->create([
            'user_id' => 1
        ]);
    }
}
