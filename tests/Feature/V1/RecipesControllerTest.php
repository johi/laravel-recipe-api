<?php
declare(strict_types=1);

namespace Tests\Feature\V1;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Database\Seeders\Tests\RecipesControllerSeeder;
use Database\Seeders\Tests\UsersControllerSeeder;
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

    

    private function getRecipePayload(int $categoryId = 1, int $authorId = 1): array
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
                'imageUrl',
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
