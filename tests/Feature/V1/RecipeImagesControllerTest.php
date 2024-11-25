<?php

namespace Tests\Feature\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

use App\Models\User;
use App\Models\Recipe;
use App\Models\RecipeImage;

class RecipeImagesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // possible seeding
    }

    public function test_as_anonymous_i_get_a_list_of_all_recipe_images()
    {
        $recipe = Recipe::factory()->create();
        RecipeImage::factory()->count(3)->create(['recipe_id' => $recipe->id]);
        $response = $this->getJson(route('recipes.images.index', $recipe));
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    0 => $this->getRecipeImageStructure()
                ]
            ]);
    }

    public function test_as_user_i_can_upload_image()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $file = UploadedFile::fake()->image('test-image.jpg', 1200, 900);
        $this->actingAs($user);
        $response = $this->postJson(route('recipes.images.store', $recipe), [
            'image' => $file,
        ]);
        $response->assertCreated()
            ->assertJsonStructure(['data' => $this->getRecipeImageStructure()]);
        $filePath = RecipeImage::first()->file_path;
        Storage::disk('public')->assertExists($filePath);
        Storage::disk('public')->delete($filePath);
    }

    // @todo function test_as_user_i_cannot_upload_image_to_someone_else_recipe()
    // @todo function test_as_admin_i_can_upload_image_to_any_recipe
    // @todo function test_as_anonymous_i_cannot_upload_image()

    public function test_upload_image_fails_for_invalid_file()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $file = UploadedFile::fake()->create('not-an-image.txt', 100, 'text/plain');
        $this->actingAs($user);
        $response = $this->postJson(route('recipes.images.store', $recipe), [
            'image' => $file,
        ]);
        $response->assertStatus(400)
            ->assertJsonStructure($this->getRecipeImageUploadErrorStructure());
    }

    public function test_upload_image_fails_for_too_small_image_resolution()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $file = UploadedFile::fake()->image('small-image.jpg', 400, 300);
        $this->actingAs($user);
        $response = $this->postJson(route('recipes.images.store', $recipe), [
            'image' => $file,
        ]);
        $response->assertStatus(400)
            ->assertJsonStructure($this->getRecipeImageUploadErrorStructure());
    }

    public function test_upload_image_fails_for_invalid_aspect_ratio()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $file = UploadedFile::fake()->image('wrong-aspect-ratio.jpg', 800, 800);
        $this->actingAs($user);
        $response = $this->postJson(route('recipes.images.store', $recipe), [
            'image' => $file,
        ]);
        $response->assertStatus(400)
            ->assertJsonStructure($this->getRecipeImageUploadErrorStructure());
    }

    public function test_as_anonymous_i_get_a_single_recipe_image()
    {
        $recipe = Recipe::factory()->create();
        $image = RecipeImage::factory()->create(['recipe_id' => $recipe->id]);
        $response = $this->getJson(route('recipes.images.show', [$recipe, $image]));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->getRecipeImageStructure()
            ]);
    }

    public function test_as_user_i_can_delete_and_image_and_file()
    {
        $user = User::factory()->create();
        Storage::fake('public');
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $filePath = 'recipes/images/test-image.jpg';
        Storage::disk('public')->put($filePath, 'test-content');
        $image = RecipeImage::factory()->create(['recipe_id' => $recipe->id, 'file_path' => $filePath]);
        $this->actingAs($user);
        $response = $this->deleteJson(route('recipes.images.destroy', [$recipe, $image]));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'message',
                'status'
            ]);

        Storage::disk('public')->assertMissing($filePath);
        $this->assertDatabaseMissing('recipe_images', ['id' => $image->id]);
    }

    // @todo function test_as_user_i_cannot_delete_someone_else_recipe_image()
    // @todo function test_as_admin_i_can_delete_any_recipe_image()
    // @todo function test_as_anonymous_i_cannot_delete_recipe_image()


    private function getRecipeImageUploadErrorStructure()
    {
        return [
            'errors',
            'status'
        ];
    }

    private function getRecipeImageStructure()
    {
        return [
            'type',
            'id',
            'attributes' => [
                'imageUrl'
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
