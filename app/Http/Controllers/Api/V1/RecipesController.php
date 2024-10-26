<?php
declare(strict_types=1);

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
     * Get all recipes
     *
     * @group Manage recipes
     * @queryParam sort string Data field(s) to sort by. Separate multiple with commas.
     * Denote descending sort with a minus sign. Example: sort=title,-createdAt
     * @queryParam filter[createdAt] Filter by created date Example: filter[createdAt]=2024-10-13
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
     * Create a recipe
     *
     * @group Manage recipes
     *
     */
    public function store(StoreRecipeRequest $request)
    {
        Gate::authorize('store', Recipe::class);
        $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
        return new RecipeResource(Recipe::create($request->mappedAttributes()));
    }

    public function show(int $recipeId)
    {
        return new RecipeResource(Recipe::with($this->includes($this->possibleIncludes))
                ->where('id', $recipeId)
                ->firstOrFail()
        );
    }

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

    public function replace(ReplaceRecipeRequest $request, Recipe $recipe)
    {
        Gate::authorize('replace', $recipe);
        $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
        $recipe->update($request->mappedAttributes());
        return new RecipeResource($recipe);
    }

    public function destroy(Recipe $recipe)
    {
        Gate::authorize('delete', $recipe);
        $recipe->delete();
        return $this->ok('Recipe successfully deleted');
    }
}
