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

class AuthorsRecipesController extends ApiController
{
    protected string $policyClass = RecipePolicy::class;
    private $possibleIncludes = ['category', 'ingredients', 'instructions'];

    public function index(RecipeFilter $filters, int $author_id) {
        return RecipeResource::collection(
            Recipe::where('user_id', $author_id)->with($this->includes($this->possibleIncludes))->filter($filters)->paginate()
        );
    }

    public function store(StoreRecipeRequest $request)
    {
        Gate::authorize('store', Recipe::class);
        $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
        return new RecipeResource(Recipe::create($request->mappedAttributes([
            'author' => 'user_id'
        ])));
    }

    public function update(UpdateRecipeRequest $request, int $author_id, int $recipe_id)
    {
        $recipe = Recipe::where('id', $recipe_id)
            ->where('user_id', $author_id)
            ->firstOrFail();
        Gate::authorize('update', $recipe);
        $mappedAttributes = $request->mappedAttributes();
        if (isset($mappedAttributes['category_id'])) {
            $category = Category::findOrFail($mappedAttributes['category_id']);
        }
        $recipe->update($mappedAttributes);
        return new RecipeResource($recipe);
    }

    public function replace(ReplaceRecipeRequest $request, int $author_id, int $recipe_id)
    {
        $recipe = Recipe::where('id', $recipe_id)
            ->where('user_id', $author_id)
            ->firstOrFail();
        Gate::authorize('replace', $recipe);
        $recipe->update($request->mappedAttributes());
        return new RecipeResource($recipe);
    }

    public function destroy(int $author_id, int $recipe_id)
    {
        $recipe = Recipe::where('id', $recipe_id)
            ->where('user_id', $author_id)
            ->firstOrFail();
        Gate::authorize('delete', $recipe);
        $recipe->delete();
        return $this->ok('Recipe successfully deleted');
    }
}
