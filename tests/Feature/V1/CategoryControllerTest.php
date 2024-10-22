<?php

namespace Tests\Feature\V1;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    const ENDPOINT_PREFIX = 'api/v1';
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * A basic feature test example.
     */
    public function test_as_anonymous_i_get_a_list_of_all_categories(): void
    {
        $response = $this->get(self::ENDPOINT_PREFIX . '/categories');
        $response->assertStatus(200)
            ->assertJsonStructure(['data',])
            ->assertJsonCount(count(Category::getCategories()), 'data');
    }
}
