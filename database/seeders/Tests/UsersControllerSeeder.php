<?php
declare(strict_types=1);

namespace Database\Seeders\Tests;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersControllerSeeder extends Seeder
{
    const USERS_TO_CREATE = 25;

    public function run()
    {
        User::factory(self::USERS_TO_CREATE)->create();
    }
}
