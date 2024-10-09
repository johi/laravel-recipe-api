<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\RecipeFilter;
use App\Http\Requests\Api\V1\ReplaceRecipeRequest;
use App\Http\Requests\Api\V1\StoreRecipeRequest;
use App\Http\Requests\Api\V1\UpdateRecipeRequest;
use App\Http\Resources\V1\RecipeResource;
use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use App\Policies\V1\RecipePolicy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        try {
            $user = User::findOrFail($request->input('data.relationships.author.data.id'));
            $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
            return new RecipeResource(Recipe::create($request->mappedAttributes()));
        } catch (ModelNotFoundException $exception) {
            return $this->error(sprintf('The provided %s id does not exist', class_basename($exception->getModel())), 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $recipe_id)
    {
        try {
            return new RecipeResource(Recipe::with($this->possibleIncludes)->findOrFail($recipe_id));
        } catch (ModelNotFoundException $exception) {
            return $this->error('Recipe cannot be found.', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecipeRequest $request, int $recipe_id)
    {
        try {
            $recipe = Recipe::findOrFail($recipe_id);
            $mappedAttributes = $request->mappedAttributes();
            if (isset($mappedAttributes['category_id'])) {
                $category = Category::findOrFail($mappedAttributes['category_id']);
            }
            Gate::authorize('update', $recipe);
            $recipe->update($mappedAttributes);
            return new RecipeResource($recipe);
        } catch (ModelNotFoundException $exception) {
            $className = class_basename($exception->getModel());
            return $this->error(
                sprintf('The provided %s id does not exist', $className),
                ($className === 'Recipe') ? 404 : 400
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function replace(ReplaceRecipeRequest $request, int $recipe_id)
    {
        try {
            $recipe = Recipe::findOrFail($recipe_id);
            $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
            $recipe->update($request->mappedAttributes());
            return new RecipeResource($recipe);
        } catch (ModelNotFoundException $exception) {
            $className = class_basename($exception->getModel());
            return $this->error(
                sprintf('The provided %s id does not exist', $className),
                ($className === 'Recipe') ? 404 : 400
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $recipe_id)
    {
        try {
            $recipe = Recipe::findOrFail($recipe_id);
            $recipe->delete();
            return $this->ok('Recipe successfully deleted');
        } catch (ModelNotFoundException $exception) {
            return $this->error('Recipe cannot be found.', 404);
        }
    }
}
