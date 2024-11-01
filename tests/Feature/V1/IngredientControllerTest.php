<?php

namespace Tests\Feature\V1;

use App\Http\Controllers\Api\AuthController;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Database\Seeders\Tests\V1\IngredientControllerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientControllerTest extends TestCase
{
    use RefreshDatabase;

    const ENDPOINT_PREFIX = 'api/v1';

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(IngredientControllerSeeder::class);
    }

    // GET ALL
    public function test_as_anonymous_i_get_a_list_of_all_ingredients(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes/1/ingredients');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    0 => $this->getIngredientsStructure()
                ]
            ]);
    }

    // GET SINGLE
    public function test_as_anonymous_i_get_a_single_ingredient(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes/1/ingredients/1');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->getIngredientsStructure()
            ]);
    }

    public function test_trying_to_show_non_existent_ingredient_gives_404(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes/1/ingredients/9999');
        $response->assertStatus(404);
    }

    // CREATE
    public function test_as_anonymous_i_cannot_create_an_ingredient(): void
    {
        $response = $this->post(self::ENDPOINT_PREFIX . '/recipes/1/ingredients', $this->getIngredientPayload());
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_create_my_own_ingredient(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/ingredients',
            $this->getIngredientPayload($recipe->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(201);
    }

    public function test_as_user_i_cannot_create_someone_else_ingredient(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes/1/ingredients',
            $this->getIngredientPayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_create_someone_else_ingredient(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes/1/ingredients',
            $this->getIngredientPayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(201);
    }

    // REPLACE
    public function test_as_anonymous_i_cannot_replace_an_ingredient(): void
    {
        $response = $this->put(self::ENDPOINT_PREFIX . '/recipes/1/ingredients/1', $this->getIngredientPayload(1));
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_replace_my_own_ingredient(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $ingredient = Ingredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/ingredients/' . $ingredient->id,
            $this->getIngredientPayload($recipe->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getIngredientsStructure()])
            ->assertJsonPath('data.attributes.title', 'Test Ingredient');
    }

    public function test_as_user_i_cannot_replace_someone_else_ingredient(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/1/ingredients/1',
            $this->getIngredientPayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_replace_someone_else_ingredient(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/1/ingredients/1',
            $this->getIngredientPayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.title', 'Test Ingredient');
    }

    public function test_trying_to_replace_a_nonexisten_ingredient_gives_404(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/1/ingredients/99',
            $this->getIngredientPayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
    }

    // UPDATE
    public function test_as_anonymous_i_cannot_update_an_ingredient(): void
    {
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/1/ingredients/1',
            ['data' => ['attributes' => ['title' => 'PATCHED Ingredient']]]
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_update_my_own_ingredient(): void
    {
        $changedTitle = 'PATCHED Ingredient';
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $ingredient = Ingredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/ingredients/' . $ingredient->id,
            ['data' => ['attributes' => ['title' => $changedTitle]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getIngredientsStructure()])
            ->assertJsonPath('data.attributes.title', $changedTitle);
    }

    public function test_as_user_i_cannot_update_someone_else_ingredient(): void
    {
        $changedTitle = 'PATCHED Ingredient';
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/1' . '/ingredients/1',
            ['data' => ['attributes' => ['title' => $changedTitle]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_update_some_else_ingredient(): void
    {
        $changedTitle = 'PATCHED Ingredient';
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/1' . '/ingredients/1',
            ['data' => ['attributes' => ['title' => $changedTitle]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getIngredientsStructure()])
            ->assertJsonPath('data.attributes.title', $changedTitle);
    }

    public function test_trying_to_update_a_non_existing_ingredient_gives_404(): void
    {
        $changedTitle = 'PATCHED Ingredient';
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/1' . '/ingredients/99',
            ['data' => ['attributes' => ['title' => $changedTitle]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
    }

    // DELETE
    public function test_as_anonymous_i_cannot_delete_an_ingredient(): void
    {
        $response = $this->delete(self::ENDPOINT_PREFIX . '/recipes/1/ingredients/1');
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_delete_my_own_ingredient(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $ingredient = Ingredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/ingredients/' . $ingredient->id,
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200);
        $this->assertDatabaseMissing('ingredients', ['id' => $ingredient->id]);
    }

    public function test_as_user_i_cannot_delete_someone_else_ingredient(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/1/ingredients/1',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_delete_someone_else_ingredient(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/1/ingredients/1',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200);
    }

    public function test_trying_to_delete_a_non_existing_ingredient_gives_404(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/1/ingredients/99',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
    }

    // private methods

    private function getIngredientPayload(int $recipeId = 1): array
    {
        return [
            'data' => [
                'attributes' => [
                    'title' => 'Test Ingredient',
                    'quantity' => 100,
                    'unit'  => 'g'
                ],
                'relationships' => [
                    'recipe' => [
                        'data' => [
                            'id' => $recipeId
                        ]
                    ]
                ]
            ],
        ];
    }
    private function getIngredientsStructure()
    {
        return [
            'type',
            'id',
            'attributes' => [
                'title',
                'quantity',
                'unit'
            ],
            'relationships' => [
                'recipe' => [
                    'data' => [
                        'type',
                        'id'
                    ],
                    'links'
                ]
            ],
            'links'
        ];
    }
}
