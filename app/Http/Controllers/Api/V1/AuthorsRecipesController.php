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

    public function index(RecipeFilter $filters, int $authorId) {
        return RecipeResource::collection(
            Recipe::where('user_id', $authorId)->with($this->includes($this->possibleIncludes))->filter($filters)->paginate()
        );
    }
}
