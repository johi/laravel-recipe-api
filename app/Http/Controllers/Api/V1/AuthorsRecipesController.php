<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\RecipeFilter;
use App\Http\Requests\Api\V1\ReplaceRecipeRequest;
use App\Http\Requests\Api\V1\StoreRecipeRequest;
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

        $model = [
            'user_id' => $author_id,
            'category_id' => $request->input('data.relationships.category.data.id'),
            'title' => $request->input('data.attributes.title'),
            'description' => $request->input('data.attributes.description'),
            'preparation_time_minutes' => $request->input('data.attributes.preparationTimeMinutes'),
            'servings' => $request->input('data.attributes.servings'),
            'image_url' => $request->input('data.attributes.imageUrl') ?? '',
        ];

        return new RecipeResource(Recipe::create($model));
    }

    public function replace(ReplaceRecipeRequest $request, int $author_id, int $recipe_id)
    {
        try {
            $ticket = Recipe::findOrFail($recipe_id);

            if ($ticket->user_id == $author_id) {

                $model = [
                    'user_id' => $author_id,
                    'category_id' => $request->input('data.relationships.category.data.id'),
                    'title' => $request->input('data.attributes.title'),
                    'description' => $request->input('data.attributes.description'),
                    'preparation_time_minutes' => $request->input('data.attributes.preparationTimeMinutes'),
                    'servings' => $request->input('data.attributes.servings'),
                    'image_url' => $request->input('data.attributes.imageUrl') ?? '',
                ];

                $ticket->update($model);
                return new RecipeResource($ticket);
            }

        } catch (ModelNotFoundException $exception) {
            return $this->error('Ticket cannot be found.', 404);
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
