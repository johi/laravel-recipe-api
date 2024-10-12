<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\RecipeFilter;
use App\Http\Requests\Api\V1\ReplaceRecipeRequest;
use App\Http\Requests\Api\V1\StoreRecipeRequest;
use App\Http\Requests\Api\V1\UpdateRecipeRequest;
use App\Http\Resources\V1\RecipeResource;
use App\Models\Category;
use App\Models\Recipe;
use App\Policies\V1\RecipePolicy;
use Illuminate\Support\Facades\Gate;

class RecipesController extends ApiController
{
    protected string $policyClass = RecipePolicy::class;
    private array $possibleIncludes = ['category', 'ingredients', 'instructions'];

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
        Gate::authorize('store', Recipe::class);
        $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
        return new RecipeResource(Recipe::create($request->mappedAttributes()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Recipe $recipe)
    {
        return new RecipeResource($recipe);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        Gate::authorize('update', $recipe);
        $mappedAttributes = $request->mappedAttributes();
        if (isset($mappedAttributes['category_id'])) {
            $category = Category::findOrFail($mappedAttributes['category_id']);
        }
        $recipe->update($mappedAttributes);
        return new RecipeResource($recipe);
    }

    /**
     * Update the specified resource in storage.
     */
    public function replace(ReplaceRecipeRequest $request, Recipe $recipe)
    {
        Gate::authorize('replace', $recipe);
        $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
        $recipe->update($request->mappedAttributes());
        return new RecipeResource($recipe);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        Gate::authorize('delete', $recipe);
        $recipe->delete();
        return $this->ok('Recipe successfully deleted');
    }
}
