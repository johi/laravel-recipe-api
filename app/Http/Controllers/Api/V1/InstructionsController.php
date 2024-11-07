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

    /**
     * Get all instructions for a recipe
     *
     * @group Recipe management
     */
    public function index(Recipe $recipe)
    {
        return InstructionResource::collection($recipe->instructions);
    }

    /**
     * Add instruction to recipe
     *
     * @group Recipe management
     */
    public function store(StoreInstructionRequest $request, Recipe $recipe)
    {
        Gate::authorize('storeRelated', $recipe);
        $attributes = $request->mappedAttributes();
        $nextOrder = $recipe->instructions()->max('order') + 1;
        $attributes['order'] = $nextOrder;
        return new InstructionResource($recipe->instructions()->create($attributes));
    }

    /**
     * Get a single instruction
     *
     * @group Recipe management
     */
    public function show(Recipe $recipe, Instruction $instruction)
    {
        return new InstructionResource($instruction);
    }

    /**
     * Replace an instruction
     *
     * @group Recipe management
     */
    public function replace(ReplaceInstructionRequest $request, Recipe $recipe, Instruction $instruction)
    {
        Gate::authorize('replace', $recipe);
        $attributes = $request->mappedAttributes();
        $instruction->update($attributes);
        return new InstructionResource($instruction);
    }

    /**
     * Update an instruction
     *
     * @group Recipe management
     */
    public function update(UpdateInstructionRequest $request, Recipe $recipe, Instruction $instruction)
    {
        Gate::authorize('update', $recipe);
        $attributes = $request->mappedAttributes();
        $instruction->update($attributes);
        return new InstructionResource($instruction);
    }

    /**
     * Delete an instruction
     *
     * @group Recipe management
     */
    public function destroy(Recipe $recipe, Instruction $instruction)
    {
        Gate::authorize('delete', $recipe);
        $instruction->delete();
        return $this->ok('Instruction successfully deleted');
    }
}
