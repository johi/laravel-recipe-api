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
     * @group Recipe/Ingredient management
     * @urlParam recipe int required
     * @response {"data":[{"type":"ingredient","id":11,"attributes":{"title":"et","quantity":20,"unit":"dl"},"relationships":{"recipe":{"data":{"type":"recipe","id":"2"},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/2/ingredients"}},{"type":"ingredient","id":12,"attributes":{"title":"laudantium","quantity":1,"unit":"tsp"},"relationships":{"recipe":{"data":{"type":"recipe","id":"2"},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/2/ingredients"}},{"type":"ingredient","id":13,"attributes":{"title":"laboriosam","quantity":34,"unit":"cl"},"relationships":{"recipe":{"data":{"type":"recipe","id":"2"},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/2/ingredients"}},{"type":"ingredient","id":14,"attributes":{"title":"expedita","quantity":13,"unit":"cl"},"relationships":{"recipe":{"data":{"type":"recipe","id":"2"},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/2/ingredients"}},{"type":"ingredient","id":15,"attributes":{"title":"non","quantity":85,"unit":"dl"},"relationships":{"recipe":{"data":{"type":"recipe","id":"2"},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/2/ingredients"}},{"type":"ingredient","id":16,"attributes":{"title":"ipsa","quantity":93,"unit":"tsp"},"relationships":{"recipe":{"data":{"type":"recipe","id":"2"},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/2/ingredients"}}]}
     */
    public function index(Recipe $recipe)
    {
        return IngredientResource::collection($recipe->ingredients);
    }

    /**
     * Add ingredient to recipe
     *
     * @group Recipe/Ingredient management
     * @urlParam recipe int required
     * @bodyParam data array required
     * @bodyParam data.attributes array required
     * @bodyParam data.attributes.title string required
     * @bodyParam data.attributes.quantity int required
     * @bodyParam data.attributes.unit int required
     * @response {"data":{"type":"ingredient","id":584,"attributes":{"title":"Test Ingredient","quantity":5,"unit":"g"},"relationships":{"recipe":{"data":{"type":"recipe","id":10},"links":{"self":"http://localhost:3001/api/v1/recipes/10"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/10/ingredients"}}}
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
     * @group Recipe/Ingredient management
     * @urlParam recipeId int required
     * @urlParam ingredientId int required
     * @response {"data":{"type":"ingredient","id":56,"attributes":{"title":"PATCH Ingredient","quantity":50,"unit":"g"},"relationships":{"recipe":{"data":{"type":"recipe","id":"11"},"links":{"self":"http://localhost:3001/api/v1/recipes/11"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/11/ingredients"}}}
     */
    public function show(Recipe $recipe, Ingredient $ingredient)
    {
        return new IngredientResource($ingredient);
    }

    /**
     * Update an ingredient
     *
     * @group Recipe/Ingredient management
     * @urlParam recipe int required
     * @bodyParam data array required
     * @bodyParam data.attributes array required
     * @bodyParam data.attributes.title string optional
     * @bodyParam data.attributes.quantity int optional
     * @bodyParam data.attributes.unit int optional
     * @response {"data":{"type":"ingredient","id":584,"attributes":{"title":"Test Ingredient","quantity":5,"unit":"g"},"relationships":{"recipe":{"data":{"type":"recipe","id":10},"links":{"self":"http://localhost:3001/api/v1/recipes/10"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/10/ingredients"}}}
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
     * @group Recipe/Ingredient management
     * @urlParam recipe int required
     * @bodyParam data array required
     * @bodyParam data.attributes array required
     * @bodyParam data.attributes.title string required
     * @bodyParam data.attributes.quantity int required
     * @bodyParam data.attributes.unit int required
     * @response {"data":{"type":"ingredient","id":584,"attributes":{"title":"Test Ingredient","quantity":5,"unit":"g"},"relationships":{"recipe":{"data":{"type":"recipe","id":10},"links":{"self":"http://localhost:3001/api/v1/recipes/10"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/10/ingredients"}}}
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
     * @group Recipe/Ingredient management
     * @urlParam recipe int required
     * @response {"data":[],"message":"Ingredient successfully deleted","status":200}
     */
    public function destroy(Recipe $recipe, Ingredient $ingredient)
    {
        Gate::authorize('delete', $recipe);
        $ingredient->delete();
        return $this->ok('Ingredient successfully deleted');
    }
}
