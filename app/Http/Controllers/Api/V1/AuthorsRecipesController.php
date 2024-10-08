<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\RecipeFilter;
use App\Http\Requests\Api\V1\ReplaceRecipeRequest;
use App\Http\Requests\Api\V1\StoreRecipeRequest;
use App\Http\Requests\Api\V1\UpdateRecipeRequest;
use App\Http\Resources\V1\RecipeResource;
use App\Models\Category;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AuthorsRecipesController extends ApiController
{
    private $possibleIncludes = ['category', 'ingredients', 'instructions'];

    public function index(int $author_id, RecipeFilter $filters) {
        return RecipeResource::collection(
            Recipe::where('user_id', $author_id)->with($this->includes($this->possibleIncludes))->filter($filters)->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(int $author_id, StoreRecipeRequest $request)
    {
        try {
            $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
        } catch (ModelNotFoundException $exception) {
            return $this->error( 'The provided category id does not exists', 400);
        }
        return new RecipeResource(Recipe::create($request->mappedAttributes()));
    }
    public function update(UpdateRecipeRequest $request, int $author_id, int $recipe_id)
    {
        try {
            $recipe = Recipe::findOrFail($recipe_id);
            if ($recipe->user_id == $author_id) {
                $mappedAttributes = $request->mappedAttributes();
                if (isset($mappedAttributes['category_id'])) {
                    $category = Category::findOrFail($mappedAttributes['category_id']);
                }
                $recipe->update($mappedAttributes);
                return new RecipeResource($recipe);
            }
            return $this->error('Recipe cannot be found.', 404);
        } catch (ModelNotFoundException $exception) {
            $className = class_basename($exception->getModel());
            return $this->error(
                sprintf('The provided %s id does not exist', $className),
                ($className === 'Recipe') ? 404 : 400
            );
        }
    }

    public function replace(ReplaceRecipeRequest $request, int $author_id, int $recipe_id)
    {
        try {
            $ticket = Recipe::findOrFail($recipe_id);

            if ($ticket->user_id == $author_id) {
                $ticket->update($request->mappedAttributes());
                return new RecipeResource($ticket);
            }
            return $this->error('Recipe cannot be found.', 404);
        } catch (ModelNotFoundException $exception) {
            return $this->error('Recipe cannot be found.', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $author_id, int $recipe_id)
    {
        try {
            $ticket = Recipe::findOrFail($recipe_id);

            if ($ticket->user_id == $author_id) {
                $ticket->delete();
                return $this->ok('Recipe successfully deleted');
            }

            return $this->error('Recipe cannot be found.', 404);
        } catch (ModelNotFoundException $exception) {
            return $this->error('Recipe cannot be found.', 404);
        }
    }
}
