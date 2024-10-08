<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\RecipeFilter;
use App\Http\Requests\Api\V1\StoreRecipeRequest;
use App\Http\Resources\V1\RecipeResource;
use App\Models\Category;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AuthorsRecipesController extends ApiController
{
    private $possibleIncludes = ['category', 'ingredients', 'instructions'];

    public function index($author_id, RecipeFilter $filters) {
        return RecipeResource::collection(
            Recipe::where('user_id', $author_id)->with($this->includes($this->possibleIncludes))->filter($filters)->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($author_id, StoreRecipeRequest $request)
    {
        try {
            $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
        } catch (ModelNotFoundException $exception) {
            return $this->error('Category not found', [
                'error' => 'The provided category id does not exists'
            ], 400);
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($author_id, $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($ticket->user_id == $author_id) {
                $ticket->delete();
                return $this->ok('Ticket successfully deleted');
            }

            return $this->error('Ticket cannot found.', 404);

        } catch (ModelNotFoundException $exception) {
            return $this->error('Ticket cannot found.', 404);
        }
    }
}
