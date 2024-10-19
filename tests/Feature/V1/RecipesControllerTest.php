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
    public function test_as_anonymous_i_dont_get_a_list_of_all_recipes(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/recipes');
        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'status'
            ])
            ->assertJsonPath('status', 401);
    }

    public function test_as_user_i_get_a_list_of_all_recipes(): void
    {
        $user = User::factory()->create([
            'is_admin' => false
        ]);
        $response = $this->get(
            self::ENDPOINT_PREFIX . '/recipes',
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_i_can_include_category_for_all_recipes(): void
    {
        $user = User::factory()->create([
            'is_admin' => false
        ]);
        $response = $this->get(
            self::ENDPOINT_PREFIX . '/recipes?include=category',
            ['Authorization' => 'Bearer ' . AuthController::createToken($user)]
        );

        $response->assertStatus(200);
    }
}
