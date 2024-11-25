<?php
declare(strict_types=1);

namespace Tests\Feature\V1;

use App\Http\Controllers\Api\AuthController;
use App\Models\Recipe;
use App\Models\User;
use Database\Seeders\Tests\V1\RecipesControllerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipesControllerTest extends TestCase
{
    use RefreshDatabase;

    const ENDPOINT_PREFIX = 'api/v1';
    public function setUp(): void
    {
        parent::setUp();
        $this->seed(RecipesControllerSeeder::class);
    }

    // RETRIEVE A LIST OF ALL RECIPES
    public function test_as_anonymous_i_get_a_list_of_all_recipes(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    0 => $this->getRecipeStructure()
                ],
                'links',
                'meta',
            ]);
    }

    public function test_as_anonymous_i_get_a_single_recipe(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes/1');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->getRecipeStructure()
            ]);
    }

    public function test_trying_to_show_non_existent_recipe_gives_404(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes/9999');
        $response->assertStatus(404);
    }

    public function test_as_anonymous_i_cannot_create_a_recipe(): void
    {
        $response = $this->post(self::ENDPOINT_PREFIX . '/recipes', $this->getRecipePayload());
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_create_my_own_recipe(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes',
            $this->getRecipePayload($user->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(201);
    }

    public function test_as_user_i_cannot_create_someone_else_recipe(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes',
            $this->getRecipePayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(400);
    }

    public function test_as_admin_i_can_create_someone_else_recipe(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes',
            $this->getRecipePayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(201);
    }

    public function test_as_anonymous_i_cannot_replace_a_recipe(): void
    {
        $response = $this->put(self::ENDPOINT_PREFIX . '/recipes/1', $this->getRecipePayload());
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_replace_my_own_recipe(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id,
            $this->getRecipePayload($user->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getRecipeStructure()])
            ->assertJsonPath('data.attributes.title', 'Test Recipe');
    }

    public function test_as_user_i_can_only_replace_my_own_recipe_with_myself_as_author(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id,
            $this->getRecipePayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(400);
    }

    public function test_as_user_i_cannot_replace_someone_else_recipe(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/1',
            $this->getRecipePayload($user->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_replace_someone_else_recipe(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/1',
            $this->getRecipePayload($user->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.title', 'Test Recipe');
    }

    public function test_trying_to_replace_non_existing_recipe_gives_404(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/999',
            $this->getRecipePayload($user->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
    }

    public function test_as_anonymous_i_cannot_update_a_recipe(): void
    {
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/1',
            ['data' => ['attributes' => ['title' => 'PATCHED Recipe']]]
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_update_my_own_recipe(): void
    {
        $changedTitle = 'PATCHED Recipe';
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id,
            ['data' => ['attributes' => ['title' => $changedTitle]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getRecipeStructure()])
            ->assertJsonPath('data.attributes.title', $changedTitle);
    }

    public function test_as_user_i_cannot_update_someone_else_recipe(): void
    {
        $changedTitle = 'PATCHED Recipe';
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/1',
            ['data' => ['attributes' => ['title' => $changedTitle]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_user_i_can_only_update_my_own_recipe_with_myself_as_author(): void
    {
        $changedTitle = 'PATCHED Recipe';
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id,
            [
                'data' => [
                    'attributes' => ['title' => $changedTitle],
                    'relationships' => ['author' => ['data' => ['id' => 1]]]
                ]
            ],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(400);
    }

    public function test_as_admin_i_can_update_some_else_recipe(): void
    {
        $changedTitle = 'PATCHED Recipe';
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/1',
            ['data' => ['attributes' => ['title' => $changedTitle]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getRecipeStructure()])
            ->assertJsonPath('data.attributes.title', $changedTitle);
    }
    public function test_trying_to_update_non_existing_recipe_gives_404(): void
    {
        $changedTitle = 'PATCHED Recipe';
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/999',
            ['data' => ['attributes' => ['title' => $changedTitle]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
    }

    public function test_as_anonymous_i_cannot_delete_a_recipe(): void
    {
        $response = $this->delete(self::ENDPOINT_PREFIX . '/recipes/1');
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_delete_my_own_recipe(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id,
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200);
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }

    public function test_as_user_i_cannot_delete_someone_else_recipe(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/1',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_delete_someone_else_recipe(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/1',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200);
    }

    public function test_trying_to_delete_a_non_existing_recipe_gives_404(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/999',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
    }

    private function getRecipePayload(int $authorId = 1, int $categoryId = 1): array
    {
        return [
            'data' => [
                'attributes' => [
                    'title' => 'Test Recipe',
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                    'preparationTimeMinutes' => 30,
                    'servings' => 4
                ],
                'relationships' => [
                    'author' => [
                        'data' => [
                            'id' => $authorId
                        ]
                    ],
                    'category' => [
                        'data' => [
                            'id' => $categoryId
                        ]
                    ]
                ]
            ],
        ];
    }

    private function getRecipeStructure(): array
    {
        return [
            'type',
            'id',
            'attributes' => [
                'title',
//                'description',
                'preparationTimeMinutes',
                'servings',
                'createdAt',
                'updatedAt',
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'type',
                        'id'
                    ],
                    'links'
                ],
                'category' => [
                    'data' => [
                        'type',
                        'id'
                    ],
                    'links'
                ]
            ],
            'included' => [
                'author' => [
                    'type',
                    'id',
                    'attributes' => [
                        'name',
                        'email',
                        'isAdmin'
                    ],
                    'links'
                ]
            ],
            'links'
        ];
    }
}
