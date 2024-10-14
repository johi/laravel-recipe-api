<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\Tests\TestSeeder;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
        $this->seed(TestSeeder::class);
    }

    public function test_apply_test_seeder()
    {
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com', // Change to whatever email you expect from the seeder
            'is_admin' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com', // Change to whatever email you expect from the seeder
            'is_admin' => false,
        ]);

        $this->assertDatabaseCount('users', 2); // Example: check if two users have been created
    }

    public function test_login_as_admin(): void
    {
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com', // Change to whatever email you expect from the seeder
            'is_admin' => true,
        ]);
        $response = $this->post('api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token'],
                'message',
                'status',
            ])
            ->assertJsonPath('message', 'Authenticated')
            ->assertJsonPath('status', 200);
    }


    public function test_login_as_user(): void
    {
        $response = $this->postJson('api/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token'],
                'message',
                'status',
            ])
            ->assertJsonPath('message', 'Authenticated')
            ->assertJsonPath('status', 200);
    }
}
