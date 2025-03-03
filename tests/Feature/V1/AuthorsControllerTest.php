<?php

namespace Tests\Feature\V1;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthorsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_as_anonymous_i_get_a_list_of_all_authors(): void
    {
        $author1 = User::factory()->create();
        Recipe::factory()->create(['user_id' => $author1->id]);
        $author2 = User::factory()->create();
        Recipe::factory()->create(['user_id' => $author2->id]);
        $notAnAuthor = User::factory()->create();
        $response = $this->getJson(route('authors.index'));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    0 => $this->getAuthorStructure()
                ],
                'links',
                'meta',
            ])
            ->assertJsonPath('meta.total', 2);
    }

    public function test_i_can_include_recipes_for_list_of_all_authors(): void
    {
        $user = User::factory()->create();
        Recipe::factory(3)->create(['user_id' => $user->id]);
        $response = $this->getJson(route('authors.index',['include' => 'recipes',]));
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.0.attributes.included.recipes');
    }

    public function test_as_anonymous_i_get_a_single_author(): void
    {
        // @todo make sure an author has at least one recipe
        $user = User::factory()->create();
        $response = $this->getJson(route('authors.show', ['author' => $user->uuid]));
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getAuthorStructure()]);
    }

    public function test_trying_to_show_non_existent_author_gives_404(): void
    {
        $response = $this->getJson(route('authors.show', Str::uuid()));
        $response->assertStatus(404)
            ->assertJsonStructure($this->getErrorStructure());
    }

    public function test_i_can_include_recipes_for_single_author(): void
    {
        $user = User::factory()->create();
        Recipe::factory(3)->create(['user_id' => $user->id]);
        $response = $this->getJson(route('authors.show',['author' => $user->uuid, 'include' => 'recipes',]));
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.attributes.included.recipes');
    }

    private function getAuthorStructure(): array
    {
        return [
            'type',
            'id',
            'attributes' => [
                'name',
                'email',
                'isAdmin',
                'emailVerifiedAt',
                'createdAt',
                'updatedAt',
                'included'
            ]
        ];
    }
}
