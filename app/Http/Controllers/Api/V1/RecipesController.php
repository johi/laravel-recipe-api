<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\RecipeFilter;
use App\Http\Requests\Api\V1\StoreRecipeRequest;
use App\Http\Requests\Api\V1\UpdateRecipeRequest;
use App\Http\Resources\V1\RecipeResource;
use App\Models\Recipe;

class RecipesController extends ApiController
{
    private $possibleIncludes = ['category', 'ingredients', 'instructions'];

    /**
     * Display a listing of the resource.
     */
    public function index(RecipeFilter $filters)
    {
        return RecipeResource::collection(
            Recipe::filter($filters)
                ->with($this->includes($this->possibleIncludes))
                ->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecipeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $recipe_id)
    {
        return new RecipeResource(Recipe::with($this->possibleIncludes)->find($recipe_id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        //
    }
}
