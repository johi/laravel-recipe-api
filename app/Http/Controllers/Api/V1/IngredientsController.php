<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ReplaceIngredientRequest;
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

    /**
     * Get all ingredients for a recipe
     *
     * @group Recipe management
     */
    public function index(int $recipeId)
    {
        return IngredientResource::collection(Ingredient::where('recipe_id', $recipeId)->get());
    }

    /**
     * Add ingredient to recipe
     *
     * @group Recipe management
     */
    public function store(StoreIngredientRequest $request, Recipe $recipe)
    {
        Gate::authorize('storeRelated', $recipe);
        $attributes = $request->mappedAttributes();
        return new IngredientResource($recipe->ingredients()->create($attributes));
    }

    /**
     * Get a single ingredient
     *
     * @group Recipe management
     */
    public function show(int $recipeId, int $ingredientId)
    {
        $ingredient = Ingredient::where('recipe_id', $recipeId)->where('id', $ingredientId)->firstOrFail();
        return new IngredientResource($ingredient);
    }

    /**
     * Update an ingredient
     *
     * @group Recipe management
     */
    public function update(UpdateIngredientRequest $request, Recipe $recipe, Ingredient $ingredient)
    {
        Gate::authorize('update', $recipe);
        $attributes = $request->mappedAttributes();
        $attributes['recipe_id'] = $recipe->id;
        $ingredient->update($attributes);
        return new IngredientResource($ingredient);
    }

    /**
     * Replace an ingredient
     *
     * @group Recipe management
     */
    public function replace(ReplaceIngredientRequest $request, Recipe $recipe, Ingredient $ingredient)
    {
        Gate::authorize('replace', $recipe);
        $attributes = $request->mappedAttributes();
        $attributes['recipe_id'] = $recipe->id;
        $ingredient->update($attributes);
        return new IngredientResource($ingredient);
    }

    /**
     * Delete an ingredient
     *
     * @group Recipe management
     */
    public function destroy(Recipe $recipe, Ingredient $ingredient)
    {
        Gate::authorize('delete', $recipe);
        $ingredient->delete();
        return $this->ok('Ingredient successfully deleted');
    }
}
