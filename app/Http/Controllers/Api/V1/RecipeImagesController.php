<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRecipeImageRequest;
use App\Http\Resources\V1\RecipeImageResource;
use App\Models\Recipe;
use App\Models\RecipeImage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class RecipeImagesController extends ApiController
{

    public function index(Recipe $recipe)
    {
        return RecipeImageResource::collection($recipe->images);
    }

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

    public function show(Recipe $recipe, RecipeImage $image)
    {
        return new RecipeImageResource($image);
    }

    public function destroy(Recipe $recipe, RecipeImage $image)
    {
        Gate::authorize('delete', $recipe);
        if (Storage::exists($image->file_path)) {
            Storage::delete($image->file_path);
        }
        $image->delete();
        return $this->ok('Recipe Image successfully deleted');
    }
}
