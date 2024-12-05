<?php
declare(strict_types=1);

namespace Tests\Feature\V1;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RecipesControllerTest extends TestCase
{
    use RefreshDatabase;

    // RETRIEVE A LIST OF ALL RECIPES
    public function test_as_anonymous_i_get_a_list_of_all_recipes(): void
    {
        $recipes = Recipe::factory(10)->create();
        $response = $this->getJson(route('recipes.index'));
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
        $recipes = Recipe::factory()->create();
        $response = $this->getJson(route('recipes.show', ['recipe' => $recipes->uuid]));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->getRecipeStructure()
            ]);
    }

    public function test_trying_to_show_non_existent_recipe_gives_404(): void
    {
        $response = $this->getJson(route('recipes.show', ['recipe' => Str::uuid()]));
        $response->assertStatus(404);
    }

    public function test_as_anonymous_i_cannot_create_a_recipe(): void
    {
        $response = $this->postJson(route('recipes.store'), $this->getRecipePayload(User::factory()->create()));
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_create_my_own_recipe(): void
    {
        $user = User::factory()->create();
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.store'),
            $this->getRecipePayload($user)
        );
        $response->assertStatus(201);
    }

    public function test_as_user_i_cannot_create_someone_else_recipe(): void
    {
        $userList = User::factory(2)->create();
        $response = $this->getAuthenticatedJsonPost(
            $userList->first(),
            route('recipes.store'),
            $this->getRecipePayload($userList->last())
        );
        $response->assertStatus(400);
    }

    public function test_as_admin_i_can_create_someone_else_recipe(): void
    {
        $user = User::factory()->create();
        $response = $this->getAuthenticatedJsonPost(
            User::factory()->create(['is_admin' => true]),
            route('recipes.store'),
            $this->getRecipePayload($user)
        );
        $response->assertStatus(201);
    }

    public function test_as_anonymous_i_cannot_replace_a_recipe(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->putJson(
            route('recipes.replace', ['recipe' => $recipe->uuid]),
            $this->getRecipePayload(User::factory()->create())
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_replace_my_own_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->getAuthenticatedJsonPut(
            $user,
            route('recipes.replace', ['recipe' => $recipe->uuid]),
            $this->getRecipePayload($user),
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getRecipeStructure()])
            ->assertJsonPath('data.attributes.title', 'Test Recipe');
    }

    public function test_as_user_i_can_only_replace_my_own_recipe_with_myself_as_author(): void
    {
        $usersList = User::factory(2)->create();
        $recipe = Recipe::factory()->create(['user_id' => $usersList->first()->id]);
        $response = $this->getAuthenticatedJsonPut(
            $usersList->first(),
            route('recipes.replace', ['recipe' => $recipe->uuid]),
            $this->getRecipePayload($usersList->last()),
        );
        $response->assertStatus(400);
    }

    public function test_as_user_i_cannot_replace_someone_else_recipe(): void
    {
        $usersList = User::factory(2)->create();
        $recipe = Recipe::factory()->create(['user_id' => $usersList->first()->id]);
        $response = $this->getAuthenticatedJsonPut(
            $usersList->last(),
            route('recipes.replace', ['recipe' => $recipe->uuid]),
            $this->getRecipePayload($usersList->last()),
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_replace_someone_else_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->getAuthenticatedJsonPut(
            User::factory()->create(['is_admin' => true]),
            route('recipes.replace', ['recipe' => $recipe->uuid]),
            $this->getRecipePayload($user),
        );
        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.title', 'Test Recipe');
    }

    public function test_trying_to_replace_non_existing_recipe_gives_404(): void
    {
        $user = User::factory()->create();
        $response = $this->getAuthenticatedJsonPut(
            User::factory()->create(['is_admin' => true]),
            route('recipes.replace', ['recipe' => Str::uuid()]),
            $this->getRecipePayload($user),
        );
        $response->assertStatus(404);
    }

    public function test_as_anonymous_i_cannot_update_a_recipe(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->patchJson(
            route('recipes.update', ['recipe' => $recipe->uuid]),
            ['data' => ['attributes' => ['title' => 'PATCHED Recipe']]]
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_update_my_own_recipe(): void
    {
        $changedTitle = 'PATCHED Recipe';
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->getAuthenticatedJsonPatch(
            $user,
            route('recipes.update', ['recipe' => $recipe->uuid]),
            ['data' => ['attributes' => ['title' => $changedTitle]]]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getRecipeStructure()])
            ->assertJsonPath('data.attributes.title', $changedTitle);
    }

    public function test_as_user_i_cannot_update_someone_else_recipe(): void
    {
        $changedTitle = 'PATCHED Recipe';
        $usersList = User::factory(2)->create();
        $recipe = Recipe::factory()->create(['user_id' => $usersList->first()->id]);
        $response = $this->getAuthenticatedJsonPatch(
            $usersList->last(),
            route('recipes.update', ['recipe' => $recipe->uuid]),
            ['data' => ['attributes' => ['title' => $changedTitle]]]
        );
        $response->assertStatus(403);
    }

    public function test_as_user_i_can_only_update_my_own_recipe_with_myself_as_author(): void
    {
        $changedTitle = 'PATCHED Recipe';
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonPatch(
            $user,
            route('recipes.update', ['recipe' => $recipe->uuid]),
            [
                'data' => [
                    'attributes' => ['title' => $changedTitle],
                    'relationships' => ['author' => ['data' => ['id' => $user->id]]] // needs to be changed to uuid
                ]
            ]
        );
        $response->assertStatus(400);
    }

    public function test_as_admin_i_can_update_some_else_recipe(): void
    {
        $changedTitle = 'PATCHED Recipe';
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonPatch(
            User::factory()->create(['is_admin' => true]),
            route('recipes.update', ['recipe' => $recipe->uuid]),
            ['data' => ['attributes' => ['title' => $changedTitle]]]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getRecipeStructure()])
            ->assertJsonPath('data.attributes.title', $changedTitle);
    }

    public function test_trying_to_update_non_existing_recipe_gives_404(): void
    {
        $changedTitle = 'PATCHED Recipe';
        $response = $this->getAuthenticatedJsonPatch(
            User::factory()->create(['is_admin' => true]),
            route('recipes.update', ['recipe' => Str::uuid()]),
            ['data' => ['attributes' => ['title' => $changedTitle]]]
        );
        $response->assertStatus(404);
    }

    public function test_as_anonymous_i_cannot_delete_a_recipe(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->deleteJson(route('recipes.destroy', ['recipe' => $recipe->uuid]));
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_delete_my_own_recipe(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->getAuthenticatedJsonDelete(
            $user,
            route('recipes.destroy', ['recipe' => $recipe->uuid])
        );
        $response->assertStatus(200);
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }

    public function test_as_user_i_cannot_delete_someone_else_recipe(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(),
            route('recipes.destroy', ['recipe' => $recipe->uuid])
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_delete_someone_else_recipe(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(['is_admin' => true]),
            route('recipes.destroy', ['recipe' => $recipe->uuid])
        );
        $response->assertStatus(200);
    }

    public function test_trying_to_delete_a_non_existing_recipe_gives_404(): void
    {
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(['is_admin' => true]),
            route('recipes.destroy', ['recipe' => Str::uuid()])
        );
        $response->assertStatus(404);
    }

    private function getRecipePayload(User $author, int $categoryId = 1): array
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
                            'id' => $author->id
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
