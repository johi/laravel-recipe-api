<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ReplaceIngredientRequest;
use App\Http\Requests\Api\V1\StoreIngredientRequest;
use App\Http\Requests\Api\V1\UpdateIngredientRequest;
use App\Http\Resources\V1\RecipeIngredientResource;
use App\Models\RecipeIngredient;
use App\Models\Recipe;
use App\Policies\V1\RecipePolicy;
use Illuminate\Support\Facades\Gate;

class RecipeIngredientsController extends ApiController
{
    protected string $policyClass = RecipePolicy::class;

    /**
     * Get all ingredients for a recipe
     *
     * @group RecipeIngredients
     * @response {"data":[{"type":"ingredient","id":"55f0ed05-71ad-4d84-8146-8a19c19cacad","attributes":{"title":"ut","quantity":70,"unit":"g"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/ingredients"}},{"type":"ingredient","id":"a22ec154-d381-4f8a-b48e-60abbd9fd1c4","attributes":{"title":"numquam","quantity":49,"unit":"cl"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/ingredients"}},{"type":"ingredient","id":"cec4d8ac-f9d3-4ebc-9a65-1472673d075a","attributes":{"title":"mollitia","quantity":6,"unit":"cl"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/ingredients"}},{"type":"ingredient","id":"09fae2ad-baed-4160-8cc2-c24ea991cbb5","attributes":{"title":"distinctio","quantity":65,"unit":"tsp"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/ingredients"}}]}
     */
    public function index(Recipe $recipe)
    {
        return RecipeIngredientResource::collection($recipe->ingredients);
    }

    /**
     * Add ingredient to recipe
     *
     * @group RecipeIngredients
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.title string required
     * @bodyParam data.attributes.quantity integer required
     * @bodyParam data.attributes.unit integer required
     * @response {"data":{"type":"ingredient","id":"09fae2ad-baed-4160-8cc2-c24ea991cbb5","attributes":{"title":"distinctio","quantity":65,"unit":"tsp"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/ingredients"}}}
     */
    public function store(StoreIngredientRequest $request, Recipe $recipe)
    {
        Gate::authorize('storeRelated', $recipe);
        $attributes = $request->mappedAttributes();
        return new RecipeIngredientResource($recipe->ingredients()->create($attributes));
    }

    /**
     * Get a single ingredient
     *
     * @group RecipeIngredients
     * @response {"data":{"type":"ingredient","id":"09fae2ad-baed-4160-8cc2-c24ea991cbb5","attributes":{"title":"distinctio","quantity":65,"unit":"tsp"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/ingredients"}}}
     */
    public function show(Recipe $recipe, RecipeIngredient $ingredient)
    {
        return new RecipeIngredientResource($ingredient);
    }

    /**
     * Update an ingredient
     *
     * @group RecipeIngredients
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.title string optional
     * @bodyParam data.attributes.quantity integer optional
     * @bodyParam data.attributes.unit integer optional
     * @response {"data":{"type":"ingredient","id":"09fae2ad-baed-4160-8cc2-c24ea991cbb5","attributes":{"title":"distinctio","quantity":65,"unit":"tsp"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/ingredients"}}}
     */
    public function update(UpdateIngredientRequest $request, Recipe $recipe, RecipeIngredient $ingredient)
    {
        Gate::authorize('update', $recipe);
        $attributes = $request->mappedAttributes();
        $attributes['recipe_id'] = $recipe->id;
        $ingredient->update($attributes);
        return new RecipeIngredientResource($ingredient);
    }

    /**
     * Replace an ingredient
     *
     * @group RecipeIngredients
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.title string required
     * @bodyParam data.attributes.quantity integer required
     * @bodyParam data.attributes.unit integer required
     * @response {"data":{"type":"ingredient","id":"09fae2ad-baed-4160-8cc2-c24ea991cbb5","attributes":{"title":"distinctio","quantity":65,"unit":"tsp"},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/ingredients"}}}
     */
    public function replace(ReplaceIngredientRequest $request, Recipe $recipe, RecipeIngredient $ingredient)
    {
        Gate::authorize('replace', $recipe);
        $attributes = $request->mappedAttributes();
        $attributes['recipe_id'] = $recipe->id;
        $ingredient->update($attributes);
        return new RecipeIngredientResource($ingredient);
    }

    /**
     * Delete an ingredient
     *
     * @group RecipeIngredients
     * @response {"data":[],"message":"Ingredient successfully deleted","status":200}
     */
    public function destroy(Recipe $recipe, RecipeIngredient $ingredient)
    {
        Gate::authorize('delete', $recipe);
        $ingredient->delete();
        return $this->ok('Ingredient successfully deleted');
    }
}
