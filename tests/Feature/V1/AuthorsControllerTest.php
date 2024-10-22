<?php

namespace Tests\Feature\V1;

use Database\Seeders\Tests\AuthorsControllerSeeder;
use Database\Seeders\Tests\UsersControllerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthorsControllerTest extends TestCase
{
    use RefreshDatabase;

    const ENDPOINT_PREFIX = 'api/v1';
    public function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthorsControllerSeeder::class);
    }

    public function test_as_anonymous_i_get_a_list_of_all_authors(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/authors');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    0 => [
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
                    ]
                ],
                'links',
                'meta',
            ])
            ->assertJsonPath('meta.total', AuthorsControllerSeeder::USERS_TO_CREATE)
            ->assertJsonCount(0, 'data.0.attributes.included');
    }

    public function test_i_can_include_recipes_for_list_of_all_authors(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/authors?include=recipes');
        $response->assertStatus(200)
            ->assertJsonCount(AuthorsControllerSeeder::RECIPES_TO_CREATE, 'data.0.attributes.included.recipes');
    }

}
