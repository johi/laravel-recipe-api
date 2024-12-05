<?php

namespace Tests\Feature\V1;

use App\Models\RecipeIngredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeIngredientsControllerTest extends TestCase
{
    use RefreshDatabase;

    // GET ALL
    public function test_as_anonymous_i_get_a_list_of_all_ingredients(): void
    {
        $recipe = Recipe::factory()->create();
        $ingredientsList = RecipeIngredient::factory(5)->create(['recipe_id' => $recipe->id]);
        $response = $this->getJson(route('recipes.ingredients.index', [$recipe]));
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
        $recipe = Recipe::factory()->create();
        $ingredient = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getJson(route('recipes.ingredients.show', [$recipe, $ingredient]), []);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->getIngredientsStructure()
            ]);
    }

    public function test_trying_to_show_non_existent_ingredient_gives_404(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getJson(route('recipes.ingredients.show',
            [
                'recipe' => $recipe,
                'ingredient' => 999
            ]), []);
        $response->assertStatus(404);
    }

    // CREATE
    public function test_as_anonymous_i_cannot_create_an_ingredient(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->postJson(
            route('recipes.ingredients.store', $recipe),
            $this->getIngredientPayload()
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_create_my_own_ingredient(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.ingredients.store', $recipe),
            $this->getIngredientPayload($recipe->id)
        );
        $response->assertStatus(201);
    }

    public function test_as_user_i_cannot_create_someone_else_ingredient(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonPost(
            User::factory()->create(),
            route('recipes.ingredients.store', $recipe),
            $this->getIngredientPayload($recipe->id)
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_create_someone_else_ingredient(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonPost(
            User::factory()->create(['is_admin' => true]),
            route('recipes.ingredients.store', $recipe),
            $this->getIngredientPayload($recipe->id)
        );
        $response->assertStatus(201);
    }

    // REPLACE
    public function test_as_anonymous_i_cannot_replace_an_ingredient(): void
    {
        $recipe = Recipe::factory()->create();
        $ingredient = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->putJson(
            route('recipes.ingredients.replace', [$recipe, $ingredient]),
            $this->getIngredientPayload($recipe->id)
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_replace_my_own_ingredient(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $ingredient = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonPut(
            $user,
            route('recipes.ingredients.replace', [$recipe, $ingredient]),
            $this->getIngredientPayload($recipe->id)
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getIngredientsStructure()])
            ->assertJsonPath('data.attributes.title', 'Test Ingredient');
    }

    public function test_as_user_i_cannot_replace_someone_else_ingredient(): void
    {
        $recipe = Recipe::factory()->create();
        $ingredientSubject = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonPut(
            User::factory()->create(),
            route('recipes.ingredients.replace', [$recipe, $ingredientSubject]),
            $this->getIngredientPayload($recipe->id)
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_replace_someone_else_ingredient(): void
    {
        $recipe = Recipe::factory()->create();
        $ingredient = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonPut(
            User::factory()->create(['is_admin' => true]),
            route('recipes.ingredients.replace', [$recipe, $ingredient]),
            $this->getIngredientPayload($recipe->id)
        );
        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.title', 'Test Ingredient');
    }

    public function test_trying_to_replace_a_nonexistent_ingredient_gives_404(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonPut(
            User::factory()->create(['is_admin' => true]),
            route('recipes.ingredients.replace', ['recipe' => $recipe, 'ingredient' => 999]),
            $this->getIngredientPayload($recipe->id)
        );
        $response->assertStatus(404);
    }

    // UPDATE
    public function test_as_anonymous_i_cannot_update_an_ingredient(): void
    {
        $recipe = Recipe::factory()->create();
        $ingredient = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->patchJson(
            route('recipes.ingredients.update', [$recipe, $ingredient]),
            ['data' => ['attributes' => ['title' => 'PATCHED Ingredient']]]
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_update_my_own_ingredient(): void
    {
        $changedTitle = 'PATCHED Ingredient';
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $ingredient = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonPatch(
            $user,
            route('recipes.ingredients.update', [$recipe, $ingredient]),
            ['data' => ['attributes' => ['title' => $changedTitle]]]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getIngredientsStructure()])
            ->assertJsonPath('data.attributes.title', $changedTitle);
    }

    public function test_as_user_i_cannot_update_someone_else_ingredient(): void
    {
        $changedTitle = 'PATCHED Ingredient';
        $recipe = Recipe::factory()->create();
        $ingredient = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonPatch(
            User::factory()->create(),
            route('recipes.ingredients.update', [$recipe, $ingredient]),
            ['data' => ['attributes' => ['title' => $changedTitle]]]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_update_some_else_ingredient(): void
    {
        $changedTitle = 'PATCHED Ingredient';
        $userSubject = User::factory()->create();
        $recipeSubject = Recipe::factory()->create(['user_id' => $userSubject->id]);
        $ingredientSubject = RecipeIngredient::factory()->create(['recipe_id' => $recipeSubject->id]);
        $response = $this->getAuthenticatedJsonPatch(
            User::factory()->create(['is_admin' => true]),
            route('recipes.ingredients.update', ['recipe' => $recipeSubject, 'ingredient' => $ingredientSubject]),
            ['data' => ['attributes' => ['title' => $changedTitle]]]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getIngredientsStructure()])
            ->assertJsonPath('data.attributes.title', $changedTitle);
    }

    public function test_trying_to_update_a_non_existing_ingredient_gives_404(): void
    {
        $changedTitle = 'PATCHED Ingredient';
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonPatch(
            User::factory()->create(['is_admin' => true]),
            route('recipes.ingredients.update', ['recipe' => $recipe, 'ingredient' => 999]),
            ['data' => ['attributes' => ['title' => $changedTitle]]]
        );
        $response->assertStatus(404);
    }

    // DELETE
    public function test_as_anonymous_i_cannot_delete_an_ingredient(): void
    {
        $recipe = Recipe::factory()->create();
        $ingredient = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->deleteJson(route('recipes.ingredients.destroy', [$recipe, $ingredient]));
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_delete_my_own_ingredient(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $ingredient = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonDelete(
            $user,
            route('recipes.ingredients.destroy', ['recipe' => $recipe->id, 'ingredient' => $ingredient]),
        );
        $response->assertStatus(200);
        $this->assertDatabaseMissing('recipe_ingredients', ['id' => $ingredient->id]);
    }

    public function test_as_user_i_cannot_delete_someone_else_ingredient(): void
    {
        $recipe = Recipe::factory()->create();
        $ingredient = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(),
            route('recipes.ingredients.destroy', [$recipe, $ingredient]),
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_delete_someone_else_ingredient(): void
    {
        $recipe = Recipe::factory()->create();
        $ingredient = RecipeIngredient::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(['is_admin' => true]),
            route('recipes.ingredients.destroy', [$recipe, $ingredient]),
        );
        $response->assertStatus(200);
    }

    public function test_trying_to_delete_a_non_existing_ingredient_gives_404(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(['is_admin' => true]),
            route('recipes.ingredients.destroy', ['recipe' => $recipe, 'ingredient' => 999]),
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
