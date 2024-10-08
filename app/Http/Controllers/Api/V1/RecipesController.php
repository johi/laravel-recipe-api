<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\RecipeFilter;
use App\Http\Requests\Api\V1\StoreRecipeRequest;
use App\Http\Requests\Api\V1\UpdateRecipeRequest;
use App\Http\Resources\V1\RecipeResource;
use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RecipesController extends ApiController
{
    private $possibleIncludes = ['category', 'ingredients', 'instructions'];

    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     */
    public function store(StoreRecipeRequest $request)
    {
        try {
            $user = User::findOrFail($request->input('data.relationships.author.data.id'));
        } catch (ModelNotFoundException $exception) {
            return $this->error('User not found', [
                'error' => 'The provided user id does not exists'
            ], 400);
        }
        try {
            $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
        } catch (ModelNotFoundException $exception) {
            return $this->error('Category not found', [
                'error' => 'The provided category id does not exists'
            ], 400);
        }


        $model = [
            'user_id' => $request->input('data.relationships.author.data.id'),
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
     * Display the specified resource.
     */
    public function show(int $recipe_id)
    {
        return new RecipeResource(Recipe::with($this->possibleIncludes)->find($recipe_id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        //
    }
}
