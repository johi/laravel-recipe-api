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
     * This retrieves all recipes. Please refer to laravel documentation on how
     * to use pagination: https://laravel.com/docs/11.x/pagination
     *
     * @group Recipes
     * @queryParam filter[createdAt] Filter by exact (single value) or between created iso-date (comma separated) Example: 2024-10-13,2024-11-13
     * @queryParam filter[updatedAt] Filter by exact (single value) or between created iso-date (comma separated) Example: 2024-10-13,2024-11-13
     * @queryParam filter[preparationTimeMinutes] Filter by preparationTimeMinutes single value less than, or between Example: 15,45
     * @queryParam filter[title] Filter by title, works with or without use of wildcard Example: `*`Carbonara
     * @queryParam filter[category] Filter by category.title, works with or without use of wildcard Example: start`*`
     * @queryParam filter[ingredient] Filter by ingredient.title, works with or without use of wildcard Example: chicken`*`
     * @queryParam include Include related resources, possible values: category, ingredients, instructions  Example: instructions,ingredients
     * @queryParam sort string Data field(s) to sort by: title, preparationTimeMinutes, createdAt, updatedAT  Separate multiple with commas.
     * Denote descending sort with a minus sign. Example: title,-createdAt
     * @response {"data":[{"type":"recipe","id":44,"attributes":{"title":"animi consectetur autem","preparationTimeMinutes":80,"servings":4,"createdAt":"2024-11-23T12:57:02.000000Z","updatedAt":"2024-11-23T12:57:07.000000Z"},"relationships":{"author":{"data":{"type":"user","id":10},"links":{"self":"http://localhost:3001/api/v1/authors/10"}},"category":{"data":{"type":"category","id":2},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/44/ingredients"}}},"included":{"author":{"type":"user","id":10,"attributes":{"name":"Jadon Bruen","email":"tpfannerstill@example.com","isAdmin":false,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/10"}]}},"links":{"self":"http://localhost:3001/api/v1/recipes/44"}}],"links":{"first":"http://localhost:3001/api/v1/recipes?page=1","last":"http://localhost:3001/api/v1/recipes?page=1","prev":null,"next":null},"meta":{"current_page":1,"from":1,"last_page":1,"links":[{"url":null,"label":"&laquo; Previous","active":false},{"url":"http://localhost:3001/api/v1/recipes?page=1","label":"1","active":true},{"url":null,"label":"Next &raquo;","active":false}],"path":"http://localhost:3001/api/v1/recipes","per_page":15,"to":1,"total":1}}
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
     * Creates a recipe
     *
     * @group Recipes
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.title string required
     * @bodyParam data.attributes.description string required
     * @bodyParam data.attributes.preparationTimeMinutes integer required
     * @bodyParam data.attributes.servings integer required
     * @bodyParam data.attributes.imageUrl string optional
     * @bodyParam data.relationships object[] required
     * @bodyParam data.relationships[].author object required
     * @bodyParam data.relationships[].author.data object required
     * @bodyParam data.relationships[].author.data.id integer required
     * @response {"data":{"type":"recipe","id":44,"attributes":{"title":"animi consectetur autem","description":"Nemo tenetur necessitatibus quis est reiciendis et. Dolores qui occaecati aut impedit dolores ea distinctio.","preparationTimeMinutes":80,"servings":4,"createdAt":"2024-11-23T12:57:02.000000Z","updatedAt":"2024-11-23T12:57:07.000000Z"},"relationships":{"author":{"data":{"type":"user","id":10},"links":{"self":"http://localhost:3001/api/v1/authors/10"}},"category":{"data":{"type":"category","id":2},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/44/ingredients"}}},"included":{"author":{"type":"user","id":10,"attributes":{"name":"Jadon Bruen","email":"tpfannerstill@example.com","isAdmin":false,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/10"}]}},"links":{"self":"http://localhost:3001/api/v1/recipes/44"}}}
     */
    public function store(StoreRecipeRequest $request)
    {
        Gate::authorize('store', Recipe::class);
        $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
        return new RecipeResource(Recipe::create($request->mappedAttributes()));
    }

    /**
     * Get a single recipe
     *
     * @group Recipes
     * @urlParam id integer required
     * @queryParam include Include related resources, possible values: category, ingredients, instructions  Example: instructions,ingredients
     * @response {"data":{"type":"recipe","id":44,"attributes":{"title":"animi consectetur autem","description":"Nemo tenetur necessitatibus quis est reiciendis et. Dolores qui occaecati aut impedit dolores ea distinctio.","preparationTimeMinutes":80,"servings":4,"createdAt":"2024-11-23T12:57:02.000000Z","updatedAt":"2024-11-23T12:57:07.000000Z"},"relationships":{"author":{"data":{"type":"user","id":10},"links":{"self":"http://localhost:3001/api/v1/authors/10"}},"category":{"data":{"type":"category","id":2},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/44/ingredients"}}},"included":{"author":{"type":"user","id":10,"attributes":{"name":"Jadon Bruen","email":"tpfannerstill@example.com","isAdmin":false,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/10"}]}},"links":{"self":"http://localhost:3001/api/v1/recipes/44"}}}
     */
    public function show(Recipe $recipe)
    {
        return new RecipeResource(Recipe::with($this->includes($this->possibleIncludes))
                ->where('id', $recipe->id)
                ->firstOrFail()
        );
    }

    /**
     * Update a recipe
     *
     * @group Recipes
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.title string optional
     * @bodyParam data.attributes.description string optional
     * @bodyParam data.attributes.preparationTimeMinutes integer optional
     * @bodyParam data.attributes.servings integer optional
     * @bodyParam data.attributes.imageUrl string optional
     * @bodyParam data.relationships object[] optional
     * @bodyParam data.relationships[].author array optional
     * @bodyParam data.relationships[].author.data array optional
     * @bodyParam data.relationships[].author.data.id int optional
     * @response {"data":{"type":"recipe","id":44,"attributes":{"title":"animi consectetur autem","description":"Nemo tenetur necessitatibus quis est reiciendis et. Dolores qui occaecati aut impedit dolores ea distinctio.","preparationTimeMinutes":80,"servings":4,"createdAt":"2024-11-23T12:57:02.000000Z","updatedAt":"2024-11-23T12:57:07.000000Z"},"relationships":{"author":{"data":{"type":"user","id":10},"links":{"self":"http://localhost:3001/api/v1/authors/10"}},"category":{"data":{"type":"category","id":2},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/44/ingredients"}}},"included":{"author":{"type":"user","id":10,"attributes":{"name":"Jadon Bruen","email":"tpfannerstill@example.com","isAdmin":false,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/10"}]}},"links":{"self":"http://localhost:3001/api/v1/recipes/44"}}}
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
     * Replace a recipe
     *
     * @group Recipes
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.title string required
     * @bodyParam data.attributes.description string required
     * @bodyParam data.attributes.preparationTimeMinutes int required
     * @bodyParam data.attributes.servings int required
     * @bodyParam data.attributes.imageUrl string optional
     * @bodyParam data.relationships object[] required
     * @bodyParam data.relationships[].author array required
     * @bodyParam data.relationships[].author.data array required
     * @bodyParam data.relationships[].author.data.id integer required
     * @response {"data":{"type":"recipe","id":44,"attributes":{"title":"animi consectetur autem","description":"Nemo tenetur necessitatibus quis est reiciendis et. Dolores qui occaecati aut impedit dolores ea distinctio.","preparationTimeMinutes":80,"servings":4,"createdAt":"2024-11-23T12:57:02.000000Z","updatedAt":"2024-11-23T12:57:07.000000Z"},"relationships":{"author":{"data":{"type":"user","id":10},"links":{"self":"http://localhost:3001/api/v1/authors/10"}},"category":{"data":{"type":"category","id":2},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/44/ingredients"}}},"included":{"author":{"type":"user","id":10,"attributes":{"name":"Jadon Bruen","email":"tpfannerstill@example.com","isAdmin":false,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/10"}]}},"links":{"self":"http://localhost:3001/api/v1/recipes/44"}}}
     */
    public function replace(ReplaceRecipeRequest $request, Recipe $recipe)
    {
        Gate::authorize('replace', $recipe);
        $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
        $recipe->update($request->mappedAttributes());
        return new RecipeResource($recipe);
    }

    /**
     * Delete a recipe
     *
     * @group Recipes
     * @response {"data":[],"message":"Recipe successfully deleted","status":200}
     */
    public function destroy(Recipe $recipe)
    {
        Gate::authorize('delete', $recipe);
        $recipe->delete();
        return $this->ok('Recipe successfully deleted');
    }
}
