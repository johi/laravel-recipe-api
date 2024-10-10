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
use App\Policies\V1\RecipePolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuthorsRecipesController extends ApiController
{
    protected string $policyClass = RecipePolicy::class;
    private $possibleIncludes = ['category', 'ingredients', 'instructions'];

    public function index(RecipeFilter $filters, int $author_id) {
        return RecipeResource::collection(
            Recipe::where('user_id', $author_id)->with($this->includes($this->possibleIncludes))->filter($filters)->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecipeRequest $request, int $author_id)
    {
        try {
            $category = Category::findOrFail($request->input('data.relationships.category.data.id'));
            Gate::authorize('store', Recipe::class);
            return new RecipeResource(Recipe::create($request->mappedAttributes([
                'author' => 'user_id'
            ])));
        } catch (ModelNotFoundException $exception) {
            return $this->error(sprintf('%s cannot be found.', class_basename($exception->getModel())), 400);
        } catch (AuthorizationException $ex) {
            // in this case when providing another author_id, would it be a bad request?
            return $this->error('You are not authorized to create that resource', 401);
        }
    }

    public function update(UpdateRecipeRequest $request, int $author_id, int $recipe_id)
    {
        try {
            $recipe = Recipe::where('id', $recipe_id)
                ->where('user_id', $author_id)
                ->firstOrFail();

            $mappedAttributes = $request->mappedAttributes();
            if (isset($mappedAttributes['category_id'])) {
                $category = Category::findOrFail($mappedAttributes['category_id']);
            }
            Gate::authorize('update', $recipe);
            $recipe->update($mappedAttributes);
            return new RecipeResource($recipe);
        } catch (ModelNotFoundException $exception) {
            $className = class_basename($exception->getModel());
            return $this->error(
                sprintf('%s cannot be found.', $className),
                ($className === 'Recipe') ? 404 : 400
            );
        } catch (AuthorizationException $ex) {
            // in this case when providing another author_id, would it be a bad request?
            return $this->error('You are not authorized to update that resource', 401);
        }
    }

    public function replace(ReplaceRecipeRequest $request, int $author_id, int $recipe_id)
    {
        try {
            $recipe = Recipe::where('id', $recipe_id)
                ->where('user_id', $author_id)
                ->firstOrFail();
            Gate::authorize('replace', $recipe);
            $recipe->update($request->mappedAttributes());
            return new RecipeResource($recipe);
        } catch (ModelNotFoundException $exception) {
            return $this->error('Recipe cannot be found.', 404);
        } catch (AuthorizationException $ex) {
            // in this case when providing another author_id, would it be a bad request?
            return $this->error('You are not authorized to update that resource', 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $author_id, int $recipe_id)
    {
        try {
            $recipe = Recipe::where('id', $recipe_id)
                ->where('user_id', $author_id)
                ->firstOrFail();
            Gate::authorize('delete', $recipe);
            $recipe->delete();
            return $this->ok('Recipe successfully deleted');
        } catch (ModelNotFoundException $exception) {
            return $this->error('Recipe cannot be found.', 404);
        } catch (AuthorizationException $ex) {
            // in this case when providing another author_id, would it be a bad request?
            return $this->error('You are not authorized to delete that resource', 401);
        }
    }
}
