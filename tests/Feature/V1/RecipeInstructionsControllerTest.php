<?php

namespace Tests\Feature\V1;

use App\Models\RecipeInstruction;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeInstructionsControllerTest extends TestCase
{
    use RefreshDatabase;

    // GET ALL
    public function test_as_anonymous_i_get_a_list_of_all_instructions(): void
    {
        $recipe = Recipe::factory()->create();
        $instructionsList = RecipeInstruction::factory(5)->create(['recipe_id' => $recipe->id]);
        $response = $this->getJson(route('recipes.instructions.index', $recipe));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    0 => $this->getInstructionStructure()
                ]
            ]);
    }

    public function test_list_of_all_instructions_yields_correct_order()
    {
        $recipe = Recipe::factory()->create();
        RecipeInstruction::factory()->create([
            'recipe_id' => $recipe->id,
            'order' => 2,
            'description' => 'Instruction 2',
        ]);
        RecipeInstruction::factory()->create([
            'recipe_id' => $recipe->id,
            'order' => 1,
            'description' => 'Instruction 1',
        ]);
        RecipeInstruction::factory()->create([
            'recipe_id' => $recipe->id,
            'order' => 3,
            'description' => 'Instruction 3',
        ]);
        $response = $this->getJson(route('recipes.instructions.index', $recipe));
        $response->assertStatus(200);
        $instructions = $response->json()['data'];

        $this->assertEquals(1, $instructions[0]['attributes']['order']);
        $this->assertEquals(2, $instructions[1]['attributes']['order']);
        $this->assertEquals(3, $instructions[2]['attributes']['order']);
    }

    // GET SINGLE
    public function test_as_anonymous_i_get_a_single_instruction(): void
    {
        $recipe = Recipe::factory()->create();
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getJson(route('recipes.instructions.show', [$recipe, $instruction]));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->getInstructionStructure()
            ]);
    }

    public function test_trying_to_show_non_existent_ingredient_gives_404(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getJson(route('recipes.instructions.show',
            [
                'recipe' => $recipe,
                'instruction' => 999
            ]
        ));
        $response->assertStatus(404);
    }

    // CREATE
    public function test_as_anonymous_i_cannot_create_an_instructions(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->postJson(
            route('recipes.instructions.store', $recipe),
            $this->getInstructionPayload()
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_create_my_own_instruction(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.instructions.store', $recipe),
            $this->getInstructionPayload($recipe->id)
        );
        $response->assertStatus(201);
    }

    public function test_creating_an_instruction_assigns_correct_order_to_each_new_instruction()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.instructions.store', $recipe),
            $this->getInstructionPayload($recipe->id)
        );
        $this->assertEquals(1, RecipeInstruction::where('recipe_id', $recipe->id)->first()->order);
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.instructions.store', $recipe),
            $this->getInstructionPayload($recipe->id)
        );
        $this->assertEquals(2, RecipeInstruction::where('recipe_id', $recipe->id)->latest('id')->first()->order);
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.instructions.store', $recipe),
            $this->getInstructionPayload($recipe->id)
        );
        $this->assertEquals(3, RecipeInstruction::where('recipe_id', $recipe->id)->latest('id')->first()->order);
    }

    public function test_as_user_i_cannot_create_someone_else_instruction(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonPost(
            User::factory()->create(),
            route('recipes.instructions.store', $recipe),
            $this->getInstructionPayload($recipe->id)
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_create_someone_else_instruction(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonPost(
            User::factory()->create(['is_admin' => true]),
            route('recipes.instructions.store', $recipe),
            $this->getInstructionPayload($recipe->id)
        );
        $response->assertStatus(201);
    }

    // REPLACE
    public function test_as_anonymous_i_cannot_replace_an_instruction(): void
    {
        $recipe = Recipe::factory()->create();
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->putJson(
            route('recipes.instructions.replace', [$recipe, $instruction]),
            $this->getInstructionPayload($recipe->id)
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_replace_my_own_instruction(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonPut(
            $user,
            route('recipes.instructions.replace', [$recipe, $instruction]),
            $this->getInstructionPayload($recipe->id)
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getInstructionStructure()])
            ->assertJsonPath('data.attributes.description', 'Test Instruction');
    }

    public function test_as_user_i_cannot_replace_someone_else_instruction(): void
    {
        $recipe = Recipe::factory()->create();
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonPut(
            User::factory()->create(),
            route('recipes.instructions.replace', [$recipe, $instruction]),
            $this->getInstructionPayload($recipe->id)
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_replace_someone_else_instruction(): void
    {
        $recipe = Recipe::factory()->create();
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonPut(
            User::factory()->create(['is_admin' => true]),
            route('recipes.instructions.replace',[$recipe,$instruction]),
            $this->getInstructionPayload($recipe->id)
        );
        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.description', 'Test Instruction');
    }

    public function test_trying_to_replace_a_nonexistent_instruction_gives_404(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonPut(
            User::factory()->create(['is_admin' => true]),
            route('recipes.instructions.replace', ['recipe' => $recipe, 'instruction' => 999]),
            $this->getInstructionPayload($recipe->id)
        );
        $response->assertStatus(404);
    }

    // UPDATE
    public function test_as_anonymous_i_cannot_update_an_instruction(): void
    {
        $recipe = Recipe::factory()->create();
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->patchJson(
            route('recipes.instructions.update',[$recipe, $instruction]),
            ['data' => ['attributes' => ['description' => 'PATCHED Instruction']]]
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_update_my_own_instructions(): void
    {
        $changedDescription = 'PATCHED Instruction';
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonPatch(
            $user,
            route('recipes.instructions.update', [$recipe, $instruction]),
            ['data' => ['attributes' => ['description' => 'PATCHED Instruction']]]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getInstructionStructure()])
            ->assertJsonPath('data.attributes.description', $changedDescription);
    }

    public function test_as_user_i_cannot_update_someone_else_instruction(): void
    {
        $changedDescription = 'PATCHED Instruction';
        $recipe = Recipe::factory()->create();
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonPatch(
            User::factory()->create(),
            route('recipes.instructions.update', [$recipe, $instruction->id]),
            ['data' => ['attributes' => ['description' => 'PATCHED Instruction']]]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_update_some_else_instruction(): void
    {
        $changedDescription = 'PATCHED Instruction';
        $recipe = Recipe::factory()->create();
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonPatch(
            User::factory()->create(['is_admin' => true]),
            route('recipes.instructions.update', [$recipe, $instruction]),
            ['data' => ['attributes' => ['description' => 'PATCHED Instruction']]]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getInstructionStructure()])
            ->assertJsonPath('data.attributes.description', $changedDescription);
    }

    public function test_trying_to_update_a_non_existing_instruction_gives_404(): void
    {
        $changedDescription = 'PATCHED Instruction';
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonPatch(
            User::factory()->create(['is_admin' => true]),
            route('recipes.instructions.update', ['recipe' => $recipe->id, 'instruction' => 999]),
            ['data' => ['attributes' => ['description' => 'PATCHED Instruction']]]
        );
        $response->assertStatus(404);
    }

    // Update-Order
    public function test_update_order_successfully()
    {
        $user = User::factory()->create(['is_admin' => true]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instructions = RecipeInstruction::factory()->count(3)->create([
            'recipe_id' => $recipe->id,
        ]);
        $payload = [
            'data' => [
                ['id' => $instructions[0]->id, 'attributes' => ['order' => 2]],
                ['id' => $instructions[1]->id, 'attributes' => ['order' => 1]],
                ['id' => $instructions[2]->id, 'attributes' => ['order' => 3]],
            ]
        ];
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.instructions.update.order', $recipe),
            $payload
        );
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    0 => $this->getInstructionStructure()
                ]
            ]);
        $this->assertDatabaseHas('recipe_instructions', ['id' => $instructions[0]->id, 'order' => 2]);
        $this->assertDatabaseHas('recipe_instructions', ['id' => $instructions[1]->id, 'order' => 1]);
        $this->assertDatabaseHas('recipe_instructions', ['id' => $instructions[2]->id, 'order' => 3]);
    }

    public function test_update_order_returns_an_error_if_any_instructions_are_missing_from_request()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instructions = RecipeInstruction::factory()->count(3)->create([
            'recipe_id' => $recipe->id,
        ]);
        $payload = [
            'data' => [
                ['id' => $instructions[0]->id, 'attributes' => ['order' => 2]],
                ['id' => $instructions[1]->id, 'attributes' => ['order' => 1]],
                // Missing the third instruction
            ]
        ];
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.instructions.update.order', $recipe),
            $payload
        );
        $response->assertStatus(400);
    }

    public function test_update_order_out_of_range(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instructions = RecipeInstruction::factory()->count(3)->create(['recipe_id' => $recipe->id]);
        $payload = [
            'data' => [
                ['id' => $instructions[0]->id, 'attributes' => ['order' => 2]],
                ['id' => $instructions[1]->id, 'attributes' => ['order' => 1]],
                ['id' => $instructions[2]->id, 'attributes' => ['order' => 5]], // out of bounds
            ]
        ];
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.instructions.update.order', $recipe),
            $payload
        );
        $response->assertStatus(400)
            ->assertJson(['message' => 'Order values must be within the valid range.']);
    }

    // Assign Order
    public function test_assign_order_reorders_instructions_correctly_when_moving_up()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instructions = RecipeInstruction::factory()->count(3)->create([
            'recipe_id' => $recipe->id,
        ])->each(function ($instruction, $index) {
            $instruction->update(['order' => $index + 1]);
        });
        $instructionToMove = $instructions->first();
        $newOrder = 3;
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.instructions.assign.order', [
                'recipe' => $recipe->id,
                'instruction' => $instructionToMove->id
            ]),
            ['data' => ['attributes' => ['order' => $newOrder]]]
        );
        $response->assertStatus(200);
        $updatedInstructions = RecipeInstruction::where('recipe_id', $recipe->id)->get();
        $this->assertEquals(3, $updatedInstructions->where('id', $instructions[0]->id)->first()->order);
        $this->assertEquals(1, $updatedInstructions->where('id', $instructions[1]->id)->first()->order);
        $this->assertEquals(2, $updatedInstructions->where('id', $instructions[2]->id)->first()->order);
    }

    public function test_assign_order_reorders_instructions_correctly_when_moving_down()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instructions = RecipeInstruction::factory()->count(3)->create([
            'recipe_id' => $recipe->id,
        ])->each(function ($instruction, $index) {
            $instruction->update(['order' => $index + 1]);
        });
        $instructionToMove = $instructions->last();
        $newOrder = 1;
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.instructions.assign.order', [
                'recipe' => $recipe->id,
                'instruction' => $instructionToMove->id
            ]),
            ['data' => ['attributes' => ['order' => $newOrder]]]
        );
        $response->assertStatus(200);
        $updatedInstructions = RecipeInstruction::where('recipe_id', $recipe->id)->get();
        $this->assertEquals(2, $updatedInstructions->where('id', $instructions[0]->id)->first()->order);
        $this->assertEquals(3, $updatedInstructions->where('id', $instructions[1]->id)->first()->order);
        $this->assertEquals(1, $updatedInstructions->where('id', $instructions[2]->id)->first()->order);
    }

    public function test_assign_order_out_of_range(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instructions = RecipeInstruction::factory(3)->create(['recipe_id' => $recipe->id]);
        $newOrder = 5; // out of bounds
        $response = $this->getAuthenticatedJsonPost(
            $user,
            route('recipes.instructions.assign.order', [
                'recipe' => $recipe->id,
                'instruction' => $instructions[2]->id
            ]),
            ['data' => ['attributes' => ['order' => $newOrder]]]
        );
        $response->assertStatus(400)
            ->assertJson(['message' => 'Order must be between 1 and 3.']);
    }

    // DELETE
    public function test_as_anonymous_i_cannot_delete_an_instruction(): void
    {
        $recipe = Recipe::factory()->create();
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->deleteJson(route('recipes.instructions.destroy', [$recipe, $instruction]));
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_delete_my_own_instruction(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonDelete(
            $user,
            route('recipes.instructions.destroy', [$recipe, $instruction])
        );
        $response->assertStatus(200);
        $this->assertDatabaseMissing('recipe_instructions', ['id' => $instruction->id]);
    }

    public function test_as_user_i_cannot_delete_someone_else_instruction(): void
    {
        $recipe = Recipe::factory()->create();
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(),
            route('recipes.instructions.destroy', [$recipe, $instruction])
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_delete_someone_else_instruction(): void
    {
        $recipe = Recipe::factory()->create();
        $instruction = RecipeInstruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(['is_admin' => true]),
            route('recipes.instructions.destroy', [$recipe, $instruction])
        );
        $response->assertStatus(200);
    }

    public function test_trying_to_delete_a_non_existing_instruction_gives_404(): void
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(['is_admin' => true]),
            route('recipes.instructions.destroy', ['recipe' => $recipe, 'instruction' => 999])
        );
        $response->assertStatus(404);
    }

    private function getInstructionPayload(int $recipeId = 1): array
    {
        return [
            'data' => [
                'attributes' => [
                    'description' => 'Test Instruction'
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

    private function getInstructionStructure()
    {
        return [
            'type',
            'id',
            'attributes' => [
                'description',
                'order'
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
