<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreIngredientRequest;
use App\Http\Requests\Api\V1\UpdateIngredientRequest;
use App\Http\Resources\V1\IngredientResource;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Policies\V1\RecipePolicy;
use Illuminate\Support\Facades\Gate;

class IngredientsController extends ApiController
{
    protected string $policyClass = RecipePolicy::class;

    public function index(int $recipeId)
    {
        return IngredientResource::collection(Ingredient::where('recipe_id', $recipeId)->get());
    }

    public function store(StoreIngredientRequest $request, Recipe $recipe)
    {
        Gate::authorize('storeRelated', $recipe);
        $attributes = $request->mappedAttributes();
        $attributes['recipe_id'] = $recipe->id;
        return new IngredientResource(Ingredient::create($attributes));
    }

    public function show(int $recipeId, int $ingredientId)
    {
        $ingredient = Ingredient::where('recipe_id', $recipeId)->where('id', $ingredientId)->firstOrFail();
        return new IngredientResource($ingredient);
    }

    public function update(UpdateIngredientRequest $request, Ingredient $ingredient)
    {
        //
    }

    public function destroy(Ingredient $ingredient)
    {
        //
    }
}
