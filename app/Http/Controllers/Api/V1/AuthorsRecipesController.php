<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\RecipeFilter;
use App\Http\Resources\V1\RecipeResource;
use App\Models\Recipe;
use App\Policies\V1\RecipePolicy;

class AuthorsRecipesController extends ApiController
{
    protected string $policyClass = RecipePolicy::class;
    private $possibleIncludes = ['category', 'ingredients', 'instructions'];

    /**
     * Get authors recipes
     *
     * Retrieve all recipes for an author. Please refer to laravel documentation on how
     *  to use pagination: https://laravel.com/docs/11.x/pagination
     * 
     * @group Authors
     * @queryParam filter[createdAt] Filter by created date (iso: YYYY-MM-DD)  Example: exact date
     *  filter[createdAt]=2024-10-13 or between dates filter[createdAt]=2024-10-13,2024-11-13
     * @queryParam filter[updatedAt] Filter by updated date (iso: YYYY-MM-DD) Example: exact date
     *  filter[updatedAt]=2024-10-13 or between dates filter[updatedAt]=2024-10-13,2024-11-13
     * @queryParam filter[preparationTimeMinutes] Filter by preparationTimeMinutes Example: less
     * than or equal to filter[preparationTimeMinutes]=30 or between filter[preparationTimeMinutes]=15,45
     * @queryParam filter[title] Filter by title, works with or without use of wildcard Example:
     *  filter[title]=`*`Muffins
     * @queryParam include Include related possible values: category, ingredients, instructions
     * Example: include=instructions,recipes
     * @queryParam sort Data field(s) to sort by: title, preparationTimeMinutes, createdAt, updatedAt.
     * Separate multiple with commas. Denote descending sort with a minus sign. Example: sort: name,-createdAt
     */
    public function index(RecipeFilter $filters, int $authorId) {
        return RecipeResource::collection(
            Recipe::where('user_id', $authorId)
                ->with($this->includes($this->possibleIncludes))
                ->filter($filters)
                ->paginate()
        );
    }
}
