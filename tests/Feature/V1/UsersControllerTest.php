<?php

namespace Tests\Feature\V1;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Database\Seeders\Tests\UsersControllerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    const ENDPOINT_PREFIX = 'api/v1';
    public function setUp(): void
    {
        parent::setUp();
        $this->seed(UsersControllerSeeder::class);
    }

    // RETRIEVE A LIST OF ALL USERS
    public function test_as_anonymous_i_dont_get_a_list_of_all_users(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/users');
        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'status'
            ])
            ->assertJsonPath('status', 401);
    }

    public function test_as_admin_i_get_a_list_of_all_users(): void
    {
        $user = User::factory()->create([
            'is_admin' => true
        ]);
        $response = $this->get(self::ENDPOINT_PREFIX . '/users', ['Authorization' => 'Bearer ' . AuthController::createToken($user)]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ])
            ->assertJsonPath('meta.total', UsersControllerSeeder::USERS_TO_CREATE + 1);
    }

    public function test_as_user_i_get_a_list_of_all_users()
    {
        $user = User::factory()->create([
            'is_admin' => false
        ]);
        $response = $this->get(
            self::ENDPOINT_PREFIX . '/users',
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ])
            ->assertJsonPath('meta.total', UsersControllerSeeder::USERS_TO_CREATE + 1);
    }

    #todo test includes

    // RETRIEVE A USER
    public function test_as_anonymous_i_dont_get_a_specific_user(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/users/1');
        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'status'
            ])
            ->assertJsonPath('status', 401);
    }

    public function test_as_admin_i_get_a_specific_user(): void
    {
        $user = User::factory()->create([
            'is_admin' => true
        ]);
        $response = $this->get(
            self::ENDPOINT_PREFIX . '/users/1',
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure($this->getUserJsonStructure());
    }

    public function test_as_user_i_get_a_specific_user(): void
    {
        $user = User::factory()->create([
            'is_admin' => false
        ]);
        $response = $this->get(
            self::ENDPOINT_PREFIX . '/users/1',
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200);
    }

    #todo test includes

    // CREATE A USER
    public function test_as_anonymous_i_cannot_create_a_user(): void
    {
        $response = $this->post(self::ENDPOINT_PREFIX . '/users', $this->getUserPayload());
        $response->assertStatus(401);
    }

    public function test_as_user_i_cannot_create_a_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/users',
            $this->getUserPayload(),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_create_a_user(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/users',
            $this->getUserPayload(),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(201)
            ->assertJsonStructure($this->getUserJsonStructure());
    }

    #@todo email already taken + validation

    // REPLACE A USER
    public function test_as_anonymous_i_cannot_replace_a_user(): void
    {
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/users/1',
            $this->getUserPayload(['email' => 'test2@example.com'])
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_cannot_replace_a_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/users/1',
            $this->getUserPayload(['email' => 'test2@example.com']),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_replace_a_user(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/users/1',
            $this->getUserPayload(['email' => 'test2@example.com']),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure($this->getUserJsonStructure())
            ->assertJsonPath('data.attributes.email', 'test2@example.com');
    }

    #@todo email already taken + validation

    // UPDATE A USER
    public function test_as_anonymous_i_cannot_update_a_user(): void
    {
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/users/1',
            ['data' => [ 'attributes' => ['email' => 'test2@example.com']]]
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_cannot_update_a_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/users/1',
            ['data' => [ 'attributes' => ['email' => 'test2@example.com']]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_update_a_user(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/users/1',
            ['data' => [ 'attributes' => ['email' => 'test2@example.com']]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure($this->getUserJsonStructure())
            ->assertJsonPath('data.attributes.email', 'test2@example.com');
    }

    #@todo email already taken + validation

    // DELETE A USER
    public function test_as_anonymous_i_cannot_delete_a_user(): void
    {
        $response = $this->delete(self::ENDPOINT_PREFIX . '/users/1');
        $response->assertStatus(401);
    }

    public function test_as_user_i_cannot_delete_a_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/users/1',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_delete_a_user(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/users/1',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200);
    }

    private function getUserPayload($extra = []): array
    {
        $flatStructure = array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'isAdmin' => false,
            'password' => 'password'
        ], $extra);
        return ['data' => ['attributes' => $flatStructure]];
    }

    private function getUserJsonStructure(): array
    {
        return [
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'name',
                    'email',
                    'isAdmin',
                    'included'
                ],
                'links'
            ]
        ];
    }
}
