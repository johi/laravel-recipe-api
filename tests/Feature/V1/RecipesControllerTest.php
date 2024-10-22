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
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_as_anonymous_i_get_a_single_recipe(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes/1');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'title',
                        'description',
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
                ]
            ]);
    }
}
