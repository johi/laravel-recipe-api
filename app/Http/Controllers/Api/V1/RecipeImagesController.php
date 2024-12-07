<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreRecipeImageRequest;
use App\Http\Resources\V1\RecipeImageResource;
use App\Models\Recipe;
use App\Models\RecipeImage;
use App\Policies\V1\RecipePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class RecipeImagesController extends ApiController
{
    protected string $policyClass = RecipePolicy::class;

    /**
     * Get all images for a recipe
     * @group RecipeImages
     * @response {"data":[{"type":"image","id":"e297fa0c-6d9a-4897-bfef-1d95c3ef6489","attributes":{"imageUrl":"http://localhost:3001/recipes/images/f832cbdf-d9c0-46c9-8f50-d0809dc3d831.png"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/images/e297fa0c-6d9a-4897-bfef-1d95c3ef6489"}},{"type":"image","id":"975534e8-d559-4343-9e38-e027ab748ff1","attributes":{"imageUrl":"http://localhost:3001/recipes/images/21a386b3-1db3-4455-8942-512c821deaad.png"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/images/975534e8-d559-4343-9e38-e027ab748ff1"}}]}
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
     * @response {"data":{"type":"image","id":"975534e8-d559-4343-9e38-e027ab748ff1","attributes":{"imageUrl":"http://localhost:3001/recipes/images/21a386b3-1db3-4455-8942-512c821deaad.png"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/images/975534e8-d559-4343-9e38-e027ab748ff1"}}}
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
     * @response {"data":{"type":"image","id":"975534e8-d559-4343-9e38-e027ab748ff1","attributes":{"imageUrl":"http://localhost:3001/recipes/images/21a386b3-1db3-4455-8942-512c821deaad.png"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/images/975534e8-d559-4343-9e38-e027ab748ff1"}}}
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
        if (Storage::disk('public')->exists($image->file_path)) {
            Storage::disk('public')->delete($image->file_path);
        }
        $image->delete();
        return $this->ok('Recipe Image successfully deleted');
    }
}
