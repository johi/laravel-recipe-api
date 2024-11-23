<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRecipeImageRequest;
use App\Http\Resources\V1\RecipeImageResource;
use App\Models\Recipe;
use App\Models\RecipeImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class RecipeImagesController extends ApiController
{
    /**
     * Get all images for a recipe
     * @group RecipeImages
     * @response {"data":[{"type":"recipeImage","id":7,"attributes":{"imageUrl":"http://localhost:3001/recipes/images/5e5cb3ff-35dd-4854-812b-c70fb8ee8f27.png"},"relationships":{"recipe":{"data":{"type":"recipe","id":1},"links":{"self":"http://localhost:3001/api/v1/recipes/1"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/1/images"}},{"type":"recipeImage","id":8,"attributes":{"imageUrl":"http://localhost:3001/recipes/images/a395ca73-ed1f-43b6-af25-7610614e531b.png"},"relationships":{"recipe":{"data":{"type":"recipe","id":1},"links":{"self":"http://localhost:3001/api/v1/recipes/1"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/1/images"}}]}
     */
    public function index(Recipe $recipe)
    {
        return RecipeImageResource::collection($recipe->images);
    }

    /**
     * Upload an image for a recipe
     * @group RecipeImages
     * @bodyParam image file required The image file to upload. Must be a JPEG, PNG, or JPG format, with a max size of 2MB
     * aspect ratio is expected to be 4:3 and minimum resolution should be 800x600
     * @response {"data":{"type":"recipeImage","id":8,"attributes":{"imageUrl":"http://localhost:3001/recipes/images/a395ca73-ed1f-43b6-af25-7610614e531b.png"},"relationships":{"recipe":{"data":{"type":"recipe","id":1},"links":{"self":"http://localhost:3001/api/v1/recipes/1"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/1/images"}}}
     */
    public function store(StoreRecipeImageRequest $request, Recipe $recipe)
    {
        Gate::authorize('storeRelated', $recipe);
        $image = $request->file('image');
        $fileName = \Str::uuid() . '.' . $image->getClientOriginalExtension();
        $filePath = $image->storeAs('recipes/images', $fileName, 'public');
        return new RecipeImageResource(RecipeImage::create([
            'recipe_id' => $recipe->id,
            'file_path' => $filePath,
        ]));
    }

    /**
     * Show a single recipeImage
     * @group RecipeImages
     * @response {"data":{"type":"recipeImage","id":8,"attributes":{"imageUrl":"http://localhost:3001/recipes/images/a395ca73-ed1f-43b6-af25-7610614e531b.png"},"relationships":{"recipe":{"data":{"type":"recipe","id":1},"links":{"self":"http://localhost:3001/api/v1/recipes/1"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/1/images"}}}
     */
    public function show(Recipe $recipe, RecipeImage $image)
    {
        return new RecipeImageResource($image);
    }

    /**
     * Delete a recipeImage
     * @group RecipeImages
     * @response {"data":[],"message":"Recipe Image successfully deleted","status":200}
     */
    public function destroy(Recipe $recipe, RecipeImage $image)
    {
        Gate::authorize('delete', $recipe);
        DB::transaction(function () use ($image) {
            if (Storage::exists($image->file_path)) {
                Storage::delete($image->file_path);
            }
            $image->delete();
            return $this->ok('Recipe Image successfully deleted');
        });
        return $this->error('Could not delete recipe image');

    }
}
