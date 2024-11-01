<?php
declare(strict_types=1);

namespace Database\Seeders\Tests;

use App\Models\User;
use Illuminate\Database\Seeder;

class AuthControllerSeeder extends Seeder
{
    public function run()
    {
        User::factory(1)->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'is_admin' => true
        ]);
        User::factory(1)->create([
            'name' => 'Normal',
            'email' => 'user@example.com',
            'is_admin' => false,
        ]);
    }
}
