<?php

namespace Tests\Feature\V1;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

#@todo email already taken + validation
class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    // RETRIEVE A LIST OF ALL USERS
    public function test_as_anonymous_i_dont_get_a_list_of_all_users(): void
    {
        $users = User::factory(10)->create();
        $response = $this->getJson(route('users.index'));
        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'status'
            ])
            ->assertJsonPath('status', 401);
    }

    public function test_as_user_i_get_a_list_of_all_users()
    {
        $userList = User::factory(3)->create();
        $response = $this->getAuthenticatedUserJsonGet(User::factory()->create(), route('users.index'));
        $response->assertStatus(200)
            ->assertJsonStructure($this->getUsersListJsonStructure())
            ->assertJsonPath('meta.total', $userList->count() + 1);
    }

    public function test_as_admin_i_get_a_list_of_all_users(): void
    {
        $userList = User::factory(3)->create();
        $response = $this->getAuthenticatedUserJsonGet(
            User::factory()->create(['is_admin' => true]),
            route('users.index')
        );
        $response->assertStatus(200)
            ->assertJsonStructure($this->getUsersListJsonStructure())
            ->assertJsonPath('meta.total', $userList->count() + 1);
    }

    public function test_i_can_include_recipes_for_all_users(): void
    {
        $user = User::factory()->create();
        $recipesList = Recipe::factory(3)->create(['user_id' => $user->id]);
        $response = $this->getAuthenticatedUserJsonGet(
            User::factory()->create(['is_admin' => true]),
            route('users.index', ['include' => 'recipes'])
        );
        $response->assertStatus(200)
            ->assertJsonCount($recipesList->count(), 'data.0.attributes.included.recipes');
    }

    // RETRIEVE A USER
    public function test_as_anonymous_i_dont_get_a_specific_user(): void
    {
        $user = User::factory()->create();
        $response = $this->getJson(route('users.show', ['user' => $user->uuid]));
        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'status'
            ])
            ->assertJsonPath('status', 401);
    }

    public function test_as_user_i_get_a_specific_user(): void
    {
        $userList = User::factory(3)->create();
        $response = $this->getAuthenticatedUserJsonGet(
            User::factory()->create(),
            route('users.show', ['user' => $userList->first()->uuid]));
        $response->assertStatus(200);
    }

    public function test_as_admin_i_get_a_specific_user(): void
    {
        $userList = User::factory(3)->create();
        $response = $this->getAuthenticatedUserJsonGet(
            User::factory()->create(['is_admin' => true]),
            route('users.show', ['user' => $userList->first()->uuid]));
        $response->assertStatus(200)
            ->assertJsonStructure($this->getUserJsonStructure());
    }

    #@todo something is wrong here, need to use authenticated requests first of all,
    # but this is not thought through at all it seams, who should and should not have access and why
    public function test_trying_to_show_a_non_existing_user_gives_404(): void
    {
        $userList = User::factory(3)->create();
        $response = $this->getAuthenticatedUserJsonGet(
            User::factory()->create(['is_admin' => true]),
            route('users.show', ['user' => Str::uuid()]));
        $response->assertStatus(404);
    }

    public function test_i_can_include_recipes_for_a_specific_user(): void
    {
        $userList = User::factory(3)->create();
        $recipeList = Recipe::factory(3)->create(['user_id' => $userList->first()->id]);
        $response = $this->getAuthenticatedUserJsonGet(
            User::factory()->create(['is_admin' => true]),
            route('users.show', [
                'user' => $userList->first()->uuid,
                'include' => 'recipes'
            ]));
        $response->assertStatus(200)
            ->assertJsonCount($recipeList->count(), 'data.attributes.included.recipes');
    }

    // CREATE A USER
    public function test_as_anonymous_i_cannot_create_a_user(): void
    {
        $response = $this->postJson(route('users.store'), $this->getUserPayload());
        $response->assertStatus(401);
    }

    public function test_as_user_i_cannot_create_a_user(): void
    {
        $response = $this->getAuthenticatedJsonPost(
            User::factory()->create(),
            route('users.store'),
            $this->getUserPayload()
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_create_a_user(): void
    {
        $response = $this->getAuthenticatedJsonPost(
            User::factory()->create(['is_admin' => true]),
            route('users.store'),
            $this->getUserPayload()
        );
        $response->assertStatus(201)
            ->assertJsonStructure($this->getUserJsonStructure());
    }

    // REPLACE A USER
    public function test_as_anonymous_i_cannot_replace_a_user(): void
    {
        $userList = User::factory(3)->create();
        $response = $this->putJson(
            route('users.update', ['user' => $userList->first()->uuid]),
            $this->getUserPayload()
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_cannot_replace_a_user(): void
    {
        $user = User::factory()->create();
        $response = $this->getAuthenticatedJsonPut(
            $user,
            route('users.replace', ['user' => $user->uuid]),
            $this->getUserPayload(['email' => 'test2@example.com'])
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_replace_a_user(): void
    {
        // @todo I actually think one reveals a flaw, should it be possible to replace ones own user?
        // I would vote for nay as it could be potentially dangerous, instead one should allow for patch only
        $user = User::factory()->create(['is_admin' => true]);
        $response = $this->getAuthenticatedJsonPut(
            $user,
            route('users.replace', ['user' => $user->uuid]),
            $this->getUserPayload(['email' => 'test2@example.com'])
        );
        $response->assertStatus(200)
            ->assertJsonStructure($this->getUserJsonStructure())
            ->assertJsonPath('data.attributes.email', 'test2@example.com');
    }

    public function test_trying_to_replace_a_non_existing_user_gives_404()
    {
        $response = $this->getAuthenticatedJsonPut(
            User::factory()->create(['is_admin' => true]),
            route('users.replace', ['user' => Str::uuid()]),
            $this->getUserPayload(['email' => 'test2@example.com'])
        );
        $response->assertStatus(404);
    }

    // UPDATE A USER
    public function test_as_anonymous_i_cannot_update_a_user(): void
    {
        $userList = User::factory(3)->create();
        $response = $this->patchJson(
            route('users.update', ['user' => $userList->first()->uuid]),
            ['data' => [ 'attributes' => ['email' => 'test2@example.com']]]
        );
        $response->assertStatus(401);
    }

    public function test_as_user_i_cannot_update_a_user(): void
    {
        $userList = User::factory(3)->create();
        $response = $this->getAuthenticatedJsonPatch(
            User::factory()->create(),
            route('users.update', ['user' => $userList->first()->uuid]),
            ['data' => [ 'attributes' => ['email' => 'test2@example.com']]]
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_update_a_user(): void
    {
        $userList = User::factory(3)->create();
        $response = $this->getAuthenticatedJsonPatch(
            User::factory()->create(['is_admin' => true]),
            route('users.update', ['user' => $userList->first()->uuid]),
            ['data' => [ 'attributes' => ['email' => 'test2@example.com']]]
        );
        $response->assertStatus(200)
            ->assertJsonStructure($this->getUserJsonStructure())
            ->assertJsonPath('data.attributes.email', 'test2@example.com');
    }

    public function test_trying_to_update_a_non_existing_user_gives_404(): void
    {
        $response = $this->getAuthenticatedJsonPatch(
            User::factory()->create(['is_admin' => true]),
            route('users.update', ['user' => Str::uuid()]),
            ['data' => [ 'attributes' => ['email' => 'test2@example.com']]]
        );
        $response->assertStatus(404);
    }

    // DELETE A USER
    public function test_as_anonymous_i_cannot_delete_a_user(): void
    {
        $user = User::factory()->create();
        $response = $this->deleteJson(route('users.destroy', ['user' => $user->uuid]));
        $response->assertStatus(401);
    }

    public function test_as_user_i_cannot_delete_a_user(): void
    {
        $user = User::factory()->create();
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(),
            route('users.destroy', ['user' => $user->uuid])
        );
        $response->assertStatus(403);
    }

    public function test_as_admin_i_can_delete_a_user(): void
    {
        $user = User::factory()->create();
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(['is_admin' => true]),
            route('users.destroy', ['user' => $user->uuid])
        );
        $response->assertStatus(200);
    }

    public function test_as_admin_i_cannot_delete_a_user_with_attached_recipes(): void
    {
        $user = User::factory()->create();
        $recipeList = Recipe::factory(3)->create(['user_id' => $user->id]);
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(['is_admin' => true]),
            route('users.destroy', ['user' => $user->uuid])
        );
        $response->assertStatus(400);
    }

    public function test_as_admin_i_can_for_delete_a_user_with_attached_recipes(): void
    {
        $user = User::factory()->create();
        $recipeList = Recipe::factory(3)->create(['user_id' => $user->id]);
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(['is_admin' => true]),
            route('users.destroy', ['user' =>  $user->uuid, 'strategy' => 'force'])
        );
        $response->assertStatus(200);
    }

    public function test_trying_to_delete_a_non_existent_user_gives_404(): void
    {
        $response = $this->getAuthenticatedJsonDelete(
            User::factory()->create(['is_admin' => true]),
            route('users.destroy', ['user' =>  Str::uuid()])
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

    private function getUsersListJsonStructure(): array
    {
        return [
            'data' => [
                0 => $this->getUserJsonStructure()['data']
            ],
            'links',
            'meta',
        ];
    }
}
