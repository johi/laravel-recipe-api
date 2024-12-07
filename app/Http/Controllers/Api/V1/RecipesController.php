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
     * @response {"data":[{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2","attributes":{"title":"quis ducimus possimus","preparationTimeMinutes":50,"servings":4,"createdAt":"2024-12-05T13:02:10.000000Z","updatedAt":"2024-12-05T13:02:14.000000Z"},"relationships":{"author":{"data":{"type":"user","id":"ec791f55-cea3-4244-ae6e-93f18bf9e96a"},"links":{"self":"http://localhost:3001/api/v1/authors/ec791f55-cea3-4244-ae6e-93f18bf9e96a"}},"category":{"data":{"type":"category","id":"c68b55d0-eb62-45da-aaa2-38363bcf75f5"},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/ingredients"}}},"included":{"author":{"type":"user","id":"ec791f55-cea3-4244-ae6e-93f18bf9e96a","attributes":{"name":"Mr. Elliot Windler Sr.","email":"tomas.dare@example.com","isAdmin":false,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/ec791f55-cea3-4244-ae6e-93f18bf9e96a"}]},"category":{"type":"recipe","id":"c68b55d0-eb62-45da-aaa2-38363bcf75f5","attributes":{"title":"Bakery"},"links":{"self":"http://localhost:3001/api/v1/categories"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}},{"type":"recipe","id":"7b2430bd-ccf3-4b9d-a862-e00ac8a6a288","attributes":{"title":"eos repellendus eum","preparationTimeMinutes":10,"servings":4,"createdAt":"2024-12-05T13:02:10.000000Z","updatedAt":"2024-12-05T13:02:17.000000Z"},"relationships":{"author":{"data":{"type":"user","id":"d45334b6-1a75-441e-bdf5-39dee9736b24"},"links":{"self":"http://localhost:3001/api/v1/authors/d45334b6-1a75-441e-bdf5-39dee9736b24"}},"category":{"data":{"type":"category","id":"8859abaa-d617-44c4-939f-93e40148a671"},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/7b2430bd-ccf3-4b9d-a862-e00ac8a6a288/ingredients"}}},"included":{"author":{"type":"user","id":"d45334b6-1a75-441e-bdf5-39dee9736b24","attributes":{"name":"Velda Hermiston V","email":"lhoeger@example.net","isAdmin":false,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/d45334b6-1a75-441e-bdf5-39dee9736b24"}]},"category":{"type":"recipe","id":"8859abaa-d617-44c4-939f-93e40148a671","attributes":{"title":"Side dishes"},"links":{"self":"http://localhost:3001/api/v1/categories"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/7b2430bd-ccf3-4b9d-a862-e00ac8a6a288"}},{"type":"recipe","id":"f6b05281-4d87-43b8-bbc5-5dc939169c13","attributes":{"title":"ut illum dolor","preparationTimeMinutes":20,"servings":4,"createdAt":"2024-12-05T13:02:10.000000Z","updatedAt":"2024-12-05T13:02:19.000000Z"},"relationships":{"author":{"data":{"type":"user","id":"58878fc0-a575-4fed-87f2-d6eec26a348d"},"links":{"self":"http://localhost:3001/api/v1/authors/58878fc0-a575-4fed-87f2-d6eec26a348d"}},"category":{"data":{"type":"category","id":"8859abaa-d617-44c4-939f-93e40148a671"},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/f6b05281-4d87-43b8-bbc5-5dc939169c13/ingredients"}}},"included":{"author":{"type":"user","id":"58878fc0-a575-4fed-87f2-d6eec26a348d","attributes":{"name":"Jazmyne Bernhard Jr.","email":"beier.jillian@example.net","isAdmin":false,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/58878fc0-a575-4fed-87f2-d6eec26a348d"}]},"category":{"type":"recipe","id":"8859abaa-d617-44c4-939f-93e40148a671","attributes":{"title":"Side dishes"},"links":{"self":"http://localhost:3001/api/v1/categories"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/f6b05281-4d87-43b8-bbc5-5dc939169c13"}}],"links":{"first":"http://localhost:3001/api/v1/recipes?page=1","last":"http://localhost:3001/api/v1/recipes?page=1","prev":null,"next":null},"meta":{"current_page":1,"from":1,"last_page":1,"links":[{"url":null,"label":"&laquo; Previous","active":false},{"url":"http://localhost:3001/api/v1/recipes?page=1","label":"1","active":true},{"url":null,"label":"Next &raquo;","active":false}],"path":"http://localhost:3001/api/v1/recipes","per_page":15,"to":3,"total":3}}
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
     * @response {"data":{"type":"recipe","id":"7b103867-e174-45dc-92ed-7a69daf9ef52","attributes":{"title":"libero et dolor","description":"Lorem Ipsum Dolor Sit Amet","preparationTimeMinutes":60,"servings":4,"createdAt":"2024-12-06T11:54:19.000000Z","updatedAt":"2024-12-06T11:54:19.000000Z"},"relationships":{"author":{"data":{"type":"user","id":"4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"},"links":{"self":"http://localhost:3001/api/v1/authors/4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"}},"category":{"data":{"type":"category","id":"764f4ba5-450e-4bd1-a988-d6cbc16dbf7e"},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/7b103867-e174-45dc-92ed-7a69daf9ef52/ingredients"}}},"included":{"author":{"type":"user","id":"4ccaa5ed-aaf9-4ed3-81bb-54140a77132b","attributes":{"name":"Admin","email":"admin@example.com","isAdmin":true,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"}]},"category":{"type":"recipe","id":"764f4ba5-450e-4bd1-a988-d6cbc16dbf7e","attributes":{"title":"Main dishes"},"links":{"self":"http://localhost:3001/api/v1/categories"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/7b103867-e174-45dc-92ed-7a69daf9ef52"}}}
     */
    public function store(StoreRecipeRequest $request)
    {
        Gate::authorize('store', Recipe::class);
        $category = Category::where('uuid', $request->input('data.relationships.category.data.id'))->firstOrFail();
        return new RecipeResource(Recipe::create($request->mappedAttributes()));
    }

    /**
     * Get a single recipe
     *
     * @group Recipes
     * @urlParam id integer required
     * @queryParam include Include related resources, possible values: category, ingredients, instructions  Example: instructions,ingredients
     * @response {"data":{"type":"recipe","id":"7b103867-e174-45dc-92ed-7a69daf9ef52","attributes":{"title":"libero et dolor","description":"Lorem Ipsum Dolor Sit Amet","preparationTimeMinutes":60,"servings":4,"createdAt":"2024-12-06T11:54:19.000000Z","updatedAt":"2024-12-06T11:54:19.000000Z"},"relationships":{"author":{"data":{"type":"user","id":"4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"},"links":{"self":"http://localhost:3001/api/v1/authors/4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"}},"category":{"data":{"type":"category","id":"764f4ba5-450e-4bd1-a988-d6cbc16dbf7e"},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/7b103867-e174-45dc-92ed-7a69daf9ef52/ingredients"}}},"included":{"author":{"type":"user","id":"4ccaa5ed-aaf9-4ed3-81bb-54140a77132b","attributes":{"name":"Admin","email":"admin@example.com","isAdmin":true,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"}]},"category":{"type":"recipe","id":"764f4ba5-450e-4bd1-a988-d6cbc16dbf7e","attributes":{"title":"Main dishes"},"links":{"self":"http://localhost:3001/api/v1/categories"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/7b103867-e174-45dc-92ed-7a69daf9ef52"}}}
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
     * @response {"data":{"type":"recipe","id":"7b103867-e174-45dc-92ed-7a69daf9ef52","attributes":{"title":"libero et dolor","description":"Lorem Ipsum Dolor Sit Amet","preparationTimeMinutes":60,"servings":4,"createdAt":"2024-12-06T11:54:19.000000Z","updatedAt":"2024-12-06T11:54:19.000000Z"},"relationships":{"author":{"data":{"type":"user","id":"4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"},"links":{"self":"http://localhost:3001/api/v1/authors/4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"}},"category":{"data":{"type":"category","id":"764f4ba5-450e-4bd1-a988-d6cbc16dbf7e"},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/7b103867-e174-45dc-92ed-7a69daf9ef52/ingredients"}}},"included":{"author":{"type":"user","id":"4ccaa5ed-aaf9-4ed3-81bb-54140a77132b","attributes":{"name":"Admin","email":"admin@example.com","isAdmin":true,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"}]},"category":{"type":"recipe","id":"764f4ba5-450e-4bd1-a988-d6cbc16dbf7e","attributes":{"title":"Main dishes"},"links":{"self":"http://localhost:3001/api/v1/categories"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/7b103867-e174-45dc-92ed-7a69daf9ef52"}}}
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        Gate::authorize('update', $recipe);
        $mappedAttributes = $request->mappedAttributes();
        if (isset($mappedAttributes['category_id'])) {
            $category = Category::where('uuid', $request->input('data.relationships.category.data.id'))->firstOrFail();
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
     * @response {"data":{"type":"recipe","id":"7b103867-e174-45dc-92ed-7a69daf9ef52","attributes":{"title":"libero et dolor","description":"Lorem Ipsum Dolor Sit Amet","preparationTimeMinutes":60,"servings":4,"createdAt":"2024-12-06T11:54:19.000000Z","updatedAt":"2024-12-06T11:54:19.000000Z"},"relationships":{"author":{"data":{"type":"user","id":"4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"},"links":{"self":"http://localhost:3001/api/v1/authors/4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"}},"category":{"data":{"type":"category","id":"764f4ba5-450e-4bd1-a988-d6cbc16dbf7e"},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/7b103867-e174-45dc-92ed-7a69daf9ef52/ingredients"}}},"included":{"author":{"type":"user","id":"4ccaa5ed-aaf9-4ed3-81bb-54140a77132b","attributes":{"name":"Admin","email":"admin@example.com","isAdmin":true,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/4ccaa5ed-aaf9-4ed3-81bb-54140a77132b"}]},"category":{"type":"recipe","id":"764f4ba5-450e-4bd1-a988-d6cbc16dbf7e","attributes":{"title":"Main dishes"},"links":{"self":"http://localhost:3001/api/v1/categories"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/7b103867-e174-45dc-92ed-7a69daf9ef52"}}}
     */
    public function replace(ReplaceRecipeRequest $request, Recipe $recipe)
    {
        Gate::authorize('replace', $recipe);
        $category = Category::where('uuid', $request->input('data.relationships.category.data.id'))->firstOrFail();
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
