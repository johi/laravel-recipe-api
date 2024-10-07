<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\RecipeFilter;
use App\Http\Resources\V1\RecipeResource;
use App\Models\Recipe;
use Illuminate\Http\Request;

class AuthorsRecipesController extends ApiController
{
    private $possibleIncludes = ['category', 'ingredients', 'instructions'];

    public function index($author_id, RecipeFilter $filters) {
        return RecipeResource::collection(
            Recipe::where('user_id', $author_id)->with($this->includes($this->possibleIncludes))->filter($filters)->paginate()
        );
    }
}
