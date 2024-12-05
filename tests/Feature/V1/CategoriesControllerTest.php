<?php

namespace Tests\Feature\V1;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_as_anonymous_i_get_a_list_of_all_categories(): void
    {
        $response = $this->getJson(route('categories.index'));
        $response->assertStatus(200)
            ->assertJsonStructure(['data',])
            ->assertJsonCount(count(Category::getCategories()), 'data');
    }
}
