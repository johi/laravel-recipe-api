<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\AssignInstructionOrderRequest;
use App\Http\Requests\Api\V1\ReplaceInstructionRequest;
use App\Http\Requests\Api\V1\StoreInstructionRequest;
use App\Http\Requests\Api\V1\UpdateInstructionOrderRequest;
use App\Http\Requests\Api\V1\UpdateInstructionRequest;
use App\Http\Resources\V1\InstructionResource;
use App\Models\Instruction;
use App\Models\Recipe;
use App\Policies\V1\RecipePolicy;
use Illuminate\Support\Facades\DB;
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

    public function updateOrder(UpdateInstructionOrderRequest $request, Recipe $recipe)
    {
        Gate::authorize('update', $recipe);
        $recipeId = $recipe->id;
        $instructionIds = Instruction::where('recipe_id', $recipeId)->pluck('id')->toArray();
        $providedIds = array_column($request->instructions, 'id');

        if (array_diff($instructionIds, $providedIds)) {
            return $this->error('All instructions must be included in the update.', 400);
        }

        DB::transaction(function () use ($request, $recipeId) {
            foreach ($request->instructions as $data) {
                Instruction::where('id', $data['id'])
                    ->where('recipe_id', $recipeId)
                    ->update(['order' => $data['order']]);
            }
        });
        return InstructionResource::collection($recipe->instructions);
    }

    public function assignOrder(AssignInstructionOrderRequest $request, Recipe $recipe, Instruction $instruction)
    {
        Gate::authorize('update', $recipe);
        $currentOrder = $instruction->order;
        $newOrder = $request->order;
        $recipeId = $recipe->id;
        DB::transaction(function () use ($recipeId, $instruction, $currentOrder, $newOrder) {
            if ($currentOrder < $newOrder) {
                Instruction::where('recipe_id', $recipeId)
                    ->whereBetween('order', [$currentOrder + 1, $newOrder])
                    ->decrement('order');
            } elseif ($currentOrder > $newOrder) {
                Instruction::where('recipe_id', $recipeId)
                    ->whereBetween('order', [$newOrder, $currentOrder - 1])
                    ->increment('order');
            }
            $instruction->update(['order' => $newOrder]);
        });
        return InstructionResource::collection($recipe->instructions);
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
