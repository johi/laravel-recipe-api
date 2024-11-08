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
     * @group Recipe management
     * @queryParam filter[createdAt] Filter by exact (single value) or between created iso-date (comma separated) Example: 2024-10-13,2024-11-13
     * @queryParam filter[updatedAt] Filter by exact (single value) or between created iso-date (comma separated) Example: 2024-10-13,2024-11-13
     * @queryParam filter[preparationTimeMinutes] Filter by preparationTimeMinutes single value less than, or between Example: 15,45
     * @queryParam filter[title] Filter by title, works with or without use of wildcard Example: `*`Carbonara
     * @queryParam filter[category] Filter by category.title, works with or without use of wildcard Example: start`*`
     * @queryParam filter[ingredient] Filter by ingredient.title, works with or without use of wildcard Example: chicken`*`
     * @queryParam include Include related resources, possible values: category, ingredients, instructions  Example: instructions,ingredients
     * @queryParam sort string Data field(s) to sort by: title, preparationTimeMinutes, createdAt, updatedAT  Separate multiple with commas.
     * Denote descending sort with a minus sign. Example: title,-createdAt
     * @response {"data":[{"type":"recipe","id":1,"attributes":{"title":"sed maiores sint","preparationTimeMinutes":85,"servings":4,"imageUrl":"https://via.placeholder.com/640x480.png/0055aa?text=velit","createdAt":"2024-10-19T11:06:00.000000Z","updatedAt":"2024-10-19T11:06:00.000000Z"},"relationships":{"author":{"data":{"type":"user","id":4},"links":{"self":"http://localhost:3001/api/v1/authors/4"}},"category":{"data":{"type":"category","id":5},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/1/ingredients"}}},"included":{"author":{"type":"user","id":4,"attributes":{"name":"Dr. Osbaldo Bednar Jr.","email":"fbeier@example.net","isAdmin":0,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/4"}]}},"links":{"self":"http://localhost:3001/api/v1/recipes/1"}},{"type":"recipe","id":2,"attributes":{"title":"quia possimus fugit","preparationTimeMinutes":70,"servings":4,"imageUrl":"https://via.placeholder.com/640x480.png/0099ff?text=inventore","createdAt":"2024-10-19T11:06:00.000000Z","updatedAt":"2024-10-19T11:06:00.000000Z"},"relationships":{"author":{"data":{"type":"user","id":9},"links":{"self":"http://localhost:3001/api/v1/authors/9"}},"category":{"data":{"type":"category","id":1},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/2/ingredients"}}},"included":{"author":{"type":"user","id":9,"attributes":{"name":"Ms. Joanne Leuschke","email":"becker.rachel@example.org","isAdmin":0,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/9"}]}},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}],"links":{"first":"http://localhost:3001/api/v1/recipes?page=1","last":"http://localhost:3001/api/v1/recipes?page=8","prev":null,"next":"http://localhost:3001/api/v1/recipes?page=2"},"meta":{"current_page":1,"from":1,"last_page":8,"links":[{"url":null,"label":"&laquo; Previous","active":false},{"url":"http://localhost:3001/api/v1/recipes?page=1","label":"1","active":true},{"url":"http://localhost:3001/api/v1/recipes?page=2","label":"2","active":false},{"url":"http://localhost:3001/api/v1/recipes?page=3","label":"3","active":false},{"url":"http://localhost:3001/api/v1/recipes?page=4","label":"4","active":false},{"url":"http://localhost:3001/api/v1/recipes?page=5","label":"5","active":false},{"url":"http://localhost:3001/api/v1/recipes?page=6","label":"6","active":false},{"url":"http://localhost:3001/api/v1/recipes?page=7","label":"7","active":false},{"url":"http://localhost:3001/api/v1/recipes?page=8","label":"8","active":false},{"url":"http://localhost:3001/api/v1/recipes?page=2","label":"Next &raquo;","active":false}],"path":"http://localhost:3001/api/v1/recipes","per_page":15,"to":15,"total":112}}
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
     * @group Recipe management
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
     * @response {"data":{"type":"recipe","id":113,"attributes":{"title":"libero et dolor","description":"Lorem Ipsum Dolor Sit Amet","preparationTimeMinutes":60,"servings":4,"imageUrl":"https://via.placeholder.com/640x480.png/006688?text=soluta","createdAt":"2024-11-06T10:27:37.000000Z","updatedAt":"2024-11-06T10:27:37.000000Z"},"relationships":{"author":{"data":{"type":"user","id":1},"links":{"self":"http://localhost:3001/api/v1/authors/1"}},"category":{"data":{"type":"category","id":2},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/113/ingredients"}}},"included":{"author":{"type":"user","id":1,"attributes":{"name":"Admin","email":"admin@example.com","isAdmin":1,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/1"}]}},"links":{"self":"http://localhost:3001/api/v1/recipes/113"}}}
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
     * @group Recipe management
     * @urlParam id integer required
     * @queryParam include Include related resources, possible values: category, ingredients, instructions  Example: instructions,ingredients
     * @response {"data":{"type":"recipe","id":11,"attributes":{"title":"non quidem quas","description":"Commodi dolore quod iste. Expedita est aut veniam eligendi qui voluptas rerum. Et occaecati quas sapiente sunt deleniti assumenda. Tempore enim voluptatem id ipsa.","preparationTimeMinutes":5,"servings":4,"imageUrl":"https://via.placeholder.com/640x480.png/0066ff?text=eius","createdAt":"2024-10-19T11:06:00.000000Z","updatedAt":"2024-10-19T11:06:00.000000Z"},"relationships":{"author":{"data":{"type":"user","id":2},"links":{"self":"http://localhost:3001/api/v1/authors/2"}},"category":{"data":{"type":"category","id":5},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/11/ingredients"}}},"included":{"author":{"type":"user","id":2,"attributes":{"name":"Mrs. Eulah Schaefer V","email":"mabel.kris@example.com","isAdmin":0,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/2"}]},"ingredients":[{"type":"ingredient","id":56,"attributes":{"title":"PATCH Ingredient","quantity":50,"unit":"g"},"relationships":{"recipe":{"data":{"type":"recipe","id":"11"},"links":{"self":"http://localhost:3001/api/v1/recipes/11"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/11/ingredients"}},{"type":"ingredient","id":57,"attributes":{"title":"asperiores","quantity":69,"unit":"cl"},"relationships":{"recipe":{"data":{"type":"recipe","id":"11"},"links":{"self":"http://localhost:3001/api/v1/recipes/11"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/11/ingredients"}}]},"links":{"self":"http://localhost:3001/api/v1/recipes/11"}}}
     */
    public function show(int $recipeId)
    {
        return new RecipeResource(Recipe::with($this->includes($this->possibleIncludes))
                ->where('id', $recipeId)
                ->firstOrFail()
        );
    }

    /**
     * Update a recipe
     *
     * @group Recipe management
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
     * @response {"data":{"type":"recipe","id":11,"attributes":{"title":"non quidem quas","description":"Commodi dolore quod iste. Expedita est aut veniam eligendi qui voluptas rerum. Et occaecati quas sapiente sunt deleniti assumenda. Tempore enim voluptatem id ipsa.","preparationTimeMinutes":5,"servings":4,"imageUrl":"https://via.placeholder.com/640x480.png/0066ff?text=eius","createdAt":"2024-10-19T11:06:00.000000Z","updatedAt":"2024-10-19T11:06:00.000000Z"},"relationships":{"author":{"data":{"type":"user","id":2},"links":{"self":"http://localhost:3001/api/v1/authors/2"}},"category":{"data":{"type":"category","id":5},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/11/ingredients"}}},"included":{"author":{"type":"user","id":2,"attributes":{"name":"Mrs. Eulah Schaefer V","email":"mabel.kris@example.com","isAdmin":0,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/2"}]},"ingredients":[{"type":"ingredient","id":56,"attributes":{"title":"PATCH Ingredient","quantity":50,"unit":"g"},"relationships":{"recipe":{"data":{"type":"recipe","id":"11"},"links":{"self":"http://localhost:3001/api/v1/recipes/11"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/11/ingredients"}},{"type":"ingredient","id":57,"attributes":{"title":"asperiores","quantity":69,"unit":"cl"},"relationships":{"recipe":{"data":{"type":"recipe","id":"11"},"links":{"self":"http://localhost:3001/api/v1/recipes/11"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/11/ingredients"}}]},"links":{"self":"http://localhost:3001/api/v1/recipes/11"}}}
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
     * @group Recipe management
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
     * @response {"data":{"type":"recipe","id":113,"attributes":{"title":"libero et dolor","description":"Lorem Ipsum Dolor Sit Amet","preparationTimeMinutes":60,"servings":4,"imageUrl":"https://via.placeholder.com/640x480.png/006688?text=soluta","createdAt":"2024-11-06T10:27:37.000000Z","updatedAt":"2024-11-06T10:27:37.000000Z"},"relationships":{"author":{"data":{"type":"user","id":1},"links":{"self":"http://localhost:3001/api/v1/authors/1"}},"category":{"data":{"type":"category","id":2},"links":{"self":"http://localhost:3001/api/v1/categories"}},"ingredients":{"links":{"self":"http://localhost:3001/api/v1/recipes/113/ingredients"}}},"included":{"author":{"type":"user","id":1,"attributes":{"name":"Admin","email":"admin@example.com","isAdmin":1,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/1"}]}},"links":{"self":"http://localhost:3001/api/v1/recipes/113"}}}
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
     * @group Recipe management
     * @response {"data":[],"message":"Recipe successfully deleted","status":200}
     */
    public function destroy(Recipe $recipe)
    {
        Gate::authorize('delete', $recipe);
        $recipe->delete();
        return $this->ok('Recipe successfully deleted');
    }
}
