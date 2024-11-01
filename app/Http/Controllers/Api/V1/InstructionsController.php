<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ReplaceInstructionRequest;
use App\Http\Requests\Api\V1\StoreInstructionRequest;
use App\Http\Requests\Api\V1\UpdateInstructionRequest;
use App\Http\Resources\V1\InstructionResource;
use App\Models\Instruction;
use App\Models\Recipe;
use App\Policies\V1\RecipePolicy;
use Illuminate\Support\Facades\Gate;

class InstructionsController extends ApiController
{
    protected string $policyClass = RecipePolicy::class;

    public function index(int $recipeId)
    {
        return InstructionResource::collection(Instruction::where('recipe_id', $recipeId)->get());
    }

    public function store(StoreInstructionRequest $request, Recipe $recipe)
    {
        Gate::authorize('storeRelated', $recipe);
        $attributes = $request->mappedAttributes();
        return new InstructionResource($recipe->instructions()->create($attributes));
    }

    public function show(int $recipeId, int $ingredientId)
    {
        $instruction = Instruction::where('recipe_id', $recipeId)->where('id', $ingredientId)->firstOrFail();
        return new InstructionResource($instruction);
    }

    public function replace(ReplaceInstructionRequest $request, Recipe $recipe, Instruction $instruction)
    {
        Gate::authorize('replace', $recipe);
        $attributes = $request->mappedAttributes();
        $attributes['recipe_id'] = $recipe->id;
        $instruction->update($attributes);
        return new InstructionResource($instruction);
    }

    public function update(UpdateInstructionRequest $request, Recipe $recipe, Instruction $instruction)
    {
        Gate::authorize('update', $recipe);
        $attributes = $request->mappedAttributes();
//        $attributes['recipe_id'] = $recipe->id;
        $instruction->update($attributes);
        return new InstructionResource($instruction);
    }

    public function destroy(Recipe $recipe, Instruction $instruction)
    {
        Gate::authorize('delete', $recipe);
        $instruction->delete();
        return $this->ok('Instruction successfully deleted');
    }
}
