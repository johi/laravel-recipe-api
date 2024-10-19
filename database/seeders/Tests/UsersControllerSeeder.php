<?php
declare(strict_types=1);

namespace Database\Seeders\Tests;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersControllerSeeder extends Seeder
{
    const USERS_TO_CREATE = 25;
    const RECIPES_TO_CREATE = 5;

    public function run()
    {
        User::factory(self::USERS_TO_CREATE)->create();
        Recipe::factory(self::RECIPES_TO_CREATE)->create([
            'user_id' => 1
        ]);
    }
}
