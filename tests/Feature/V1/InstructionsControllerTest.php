<?php

namespace Tests\Feature\V1;

use App\Http\Controllers\Api\AuthController;
use App\Models\Instruction;
use App\Models\Recipe;
use App\Models\User;
use Database\Seeders\Tests\V1\InstructionsControllerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructionsControllerTest extends TestCase
{
    use RefreshDatabase;

    const ENDPOINT_PREFIX = 'api/v1';

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(InstructionsControllerSeeder::class);
    }

    // GET ALL
    public function test_as_anonymous_i_get_a_list_of_all_instructions(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes/1/instructions');
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
        Instruction::factory()->create([
            'recipe_id' => $recipe->id,
            'order' => 2,
            'description' => 'Instruction 2',
        ]);
        Instruction::factory()->create([
            'recipe_id' => $recipe->id,
            'order' => 1,
            'description' => 'Instruction 1',
        ]);
        Instruction::factory()->create([
            'recipe_id' => $recipe->id,
            'order' => 3,
            'description' => 'Instruction 3',
        ]);
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/instructions');
        $response->assertStatus(200);
        $instructions = $response->json()['data'];

        $this->assertEquals(1, $instructions[0]['attributes']['order']);
        $this->assertEquals(2, $instructions[1]['attributes']['order']);
        $this->assertEquals(3, $instructions[2]['attributes']['order']);
    }

    // GET SINGLE
    public function test_as_anonymous_i_get_a_single_instruction(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes/1/instructions/1');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->getInstructionStructure()
            ]);
    }

    public function test_trying_to_show_non_existent_ingredient_gives_404(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes/1/instructions/9999');
        $response->assertStatus(404);
    }

    // CREATE
    public function test_as_anonymous_i_cannot_create_an_instructions(): void
    {
        $response = $this->post(self::ENDPOINT_PREFIX . '/recipes/1/instructions', $this->getInstructionPayload());
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_create_my_own_instruction(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/instructions',
            $this->getInstructionPayload($recipe->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(201);
    }

    public function test_creating_an_instruction_assigns_correct_order_to_each_new_instruction()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/instructions',
            $this->getInstructionPayload($recipe->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );

        $this->assertEquals(1, Instruction::where('recipe_id', $recipe->id)->first()->order);

        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/instructions',
            $this->getInstructionPayload($recipe->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $this->assertEquals(2, Instruction::where('recipe_id', $recipe->id)->latest('id')->first()->order);

        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/instructions',
            $this->getInstructionPayload($recipe->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );

        $this->assertEquals(3, Instruction::where('recipe_id', $recipe->id)->latest('id')->first()->order);
    }

    public function test_as_user_i_cannot_create_someone_else_instruction(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes/1/instructions',
            $this->getInstructionPayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_create_someone_else_instruction(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes/1/instructions',
            $this->getInstructionPayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(201);
    }

    // REPLACE
    public function test_as_anonymous_i_cannot_replace_an_instruction(): void
    {
        $response = $this->put(self::ENDPOINT_PREFIX . '/recipes/1/instructions/1', $this->getInstructionStructure(1));
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_replace_my_own_instruction(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instruction = Instruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/instructions/' . $instruction->id,
            $this->getInstructionPayload($recipe->id),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getInstructionStructure()])
            ->assertJsonPath('data.attributes.description', 'Test Instruction');
    }

    public function test_as_user_i_cannot_replace_someone_else_instruction(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/1/instructions/1',
            $this->getInstructionPayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_replace_someone_else_instruction(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/1/instructions/1',
            $this->getInstructionPayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.description', 'Test Instruction');
    }

    public function test_trying_to_replace_a_nonexisten_instruction_gives_404(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->put(
            self::ENDPOINT_PREFIX . '/recipes/1/instructions/99',
            $this->getInstructionPayload(1),
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
    }

    // UPDATE
    public function test_as_anonymous_i_cannot_update_an_instruction(): void
    {
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/1/instructions/1',
            ['data' => ['attributes' => ['description' => 'PATCHED Instruction']]]
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_update_my_own_instructions(): void
    {
        $changedDescription = 'PATCHED Instruction';
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instruction = Instruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/instructions/' . $instruction->id,
            ['data' => ['attributes' => ['description' => $changedDescription]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getInstructionStructure()])
            ->assertJsonPath('data.attributes.description', $changedDescription);
    }

    public function test_as_user_i_cannot_update_someone_else_instruction(): void
    {
        $changedDescription = 'PATCHED Instruction';
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/1' . '/instructions/1',
            ['data' => ['attributes' => ['description' => $changedDescription]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_update_some_else_instruction(): void
    {
        $changedDescription = 'PATCHED Instruction';
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/1' . '/instructions/1',
            ['data' => ['attributes' => ['description' => $changedDescription]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getInstructionStructure()])
            ->assertJsonPath('data.attributes.description', $changedDescription);
    }

    public function test_update_order_successfully()
    {
        $user = User::factory()->create(['is_admin' => true]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instructions = Instruction::factory()->count(3)->create([
            'recipe_id' => $recipe->id,
        ]);
        $payload = [
            'instructions' => [
                ['id' => $instructions[0]->id, 'order' => 2],
                ['id' => $instructions[1]->id, 'order' => 1],
                ['id' => $instructions[2]->id, 'order' => 3],
            ]
        ];
        $response = $this->post(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/instructions/update-order',
            $payload,
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    0 => $this->getInstructionStructure()
                ]
            ]);
        $this->assertDatabaseHas('instructions', ['id' => $instructions[0]->id, 'order' => 2]);
        $this->assertDatabaseHas('instructions', ['id' => $instructions[1]->id, 'order' => 1]);
        $this->assertDatabaseHas('instructions', ['id' => $instructions[2]->id, 'order' => 3]);
    }

    public function test_update_order_returns_an_error_if_any_instructions_are_missing_from_request()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instructions = Instruction::factory()->count(3)->create([
            'recipe_id' => $recipe->id,
        ]);
        $this->actingAs($user);
        $payload = [
            'instructions' => [
                ['id' => $instructions[0]->id, 'order' => 2],
                ['id' => $instructions[1]->id, 'order' => 1],
                // Missing the third instruction
            ]
        ];
        $response = $this->postJson(route('instructions.update.order', $recipe), $payload);
        $response->assertStatus(400);
    }

    public function test_trying_to_update_a_non_existing_instruction_gives_404(): void
    {
        $changedDescription = 'PATCHED Instruction';
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->patch(
            self::ENDPOINT_PREFIX . '/recipes/1' . '/instructions/99',
            ['data' => ['attributes' => ['description' => $changedDescription]]],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(404);
    }

    // DELETE
    public function test_as_anonymous_i_cannot_delete_an_instruction(): void
    {
        $response = $this->delete(self::ENDPOINT_PREFIX . '/recipes/1/instructions/1');
        $response->assertStatus(401);
    }

    public function test_as_user_i_can_delete_my_own_instruction(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $instruction = Instruction::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/' . $recipe->id . '/instructions/' . $instruction->id,
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200);
        $this->assertDatabaseMissing('instructions', ['id' => $instruction->id]);
    }

    public function test_as_user_i_cannot_delete_someone_else_instruction(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/1/instructions/1',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_delete_someone_else_instruction(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/1/instructions/1',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200);
    }

    public function test_trying_to_delete_a_non_existing_instruction_gives_404(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->delete(
            self::ENDPOINT_PREFIX . '/recipes/1/instructions/99',
            [],
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
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
