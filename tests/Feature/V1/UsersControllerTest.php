<?php

namespace Tests\Feature\V1;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Database\Seeders\Tests\UsersControllerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

#@todo email already taken + validation
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

    public function test_i_can_include_recipes_for_all_users(): void
    {
        $user = User::factory()->create([
            'is_admin' => false
        ]);
        $response = $this->get(
            self::ENDPOINT_PREFIX . '/users?include=recipes',
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        // This might be a bit hacky :-P
        $response->assertStatus(200)
            ->assertJsonCount(UsersControllerSeeder::RECIPES_TO_CREATE, 'data.0.attributes.included.recipes');
    }

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

    public function test_trying_to_show_a_non_existing_user_gives_404(): void
    {
        $user = User::factory()->create([
            'is_admin' => false
        ]);
        $response = $this->get(
            self::ENDPOINT_PREFIX . '/users/100',
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
    }
    public function test_i_can_include_recipes_for_a_specific_user(): void
    {
        $user = User::factory()->create([
            'is_admin' => false
        ]);
        $response = $this->get(
            self::ENDPOINT_PREFIX . '/users/1?include=recipes',
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonCount(UsersControllerSeeder::RECIPES_TO_CREATE, 'data.attributes.included.recipes');
    }

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

    public function test_trying_to_replace_a_non_existing_user_gives_404()
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/users/100',
            $this->getUserPayload(['email' => 'test2@example.com']),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
    }

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

    public function test_trying_to_update_a_non_existing_user_gives_404(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/users/100',
            ['data' => [ 'attributes' => ['email' => 'test2@example.com']]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
    }

    // DELETE A USER
    public function test_as_anonymous_i_cannot_delete_a_user(): void
    {
        $response = $this->delete(self::ENDPOINT_PREFIX . '/users/2');
        $response->assertStatus(401);
    }

    public function test_as_user_i_cannot_delete_a_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/users/2',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_delete_a_user(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/users/2',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200);
    }

    public function test_as_admin_i_cannot_delete_a_user_with_attached_recipes(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/users/1',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(400);
    }

    public function test_trying_to_delete_a_non_existent_user_gives_404(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/users/100',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
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
