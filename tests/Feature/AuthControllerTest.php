<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password'
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

    public function test_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $tokenData = [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'TestToken',
        ];
        $this->assertDatabaseHas('personal_access_tokens', $tokenData);
        $response = $this->post('api/logout', [], ['Authorization' => 'Bearer ' . $token]);
        $this->assertDatabaseMissing('personal_access_tokens', $tokenData);
        $response->assertStatus(200);
    }

    public function test_logout_when_unauthenticated(): void
    {
        $response = $this->post('api/logout', [], ['Authorization' => 'Bearer invalid_token']);
        $response->assertStatus(401);
    }
}
