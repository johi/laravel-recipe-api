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
     * @group Recipe/Instruction management
     * @response {"data":[{"type":"instruction","id":3,"attributes":{"description":"Iste maxime odio voluptatem id. Nemo magnam rerum ut ut quis.","order":1},"relationships":{"recipe":{"data":{"type":"recipe","id":2},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/2/instructions"}},{"type":"instruction","id":4,"attributes":{"description":"Animi nostrum nemo eaque illum. Expedita dolorem qui consequatur officia incidunt facilis dolorum.","order":2},"relationships":{"recipe":{"data":{"type":"recipe","id":2},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/2/instructions"}},{"type":"instruction","id":5,"attributes":{"description":"Non placeat sit voluptatibus. Quisquam accusamus eos inventore consequatur dolorum doloribus reiciendis aut.","order":3},"relationships":{"recipe":{"data":{"type":"recipe","id":2},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/2/instructions"}},{"type":"instruction","id":6,"attributes":{"description":"Culpa error vitae voluptatem quaerat accusantium ullam laboriosam.","order":4},"relationships":{"recipe":{"data":{"type":"recipe","id":2},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/2/instructions"}},{"type":"instruction","id":7,"attributes":{"description":"Sed aliquid officia sunt sit qui. Et architecto veritatis quasi laudantium.","order":5},"relationships":{"recipe":{"data":{"type":"recipe","id":2},"links":{"self":"http://localhost:3001/api/v1/recipes/2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/2/instructions"}}]}
     */
    public function index(Recipe $recipe)
    {
        return InstructionResource::collection($recipe->instructions);
    }

    /**
     * Add instruction to recipe
     *
     * @group Recipe/Instruction management
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.description string required
     * @response {"data":{"type":"instruction","id":533,"attributes":{"description":"Test Instruction Description","order":8},"relationships":{"recipe":{"data":{"type":"recipe","id":10},"links":{"self":"http://localhost:3001/api/v1/recipes/10"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/10/instructions"}}}
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
     * @group Recipe/Instruction management
     * @response {"data":{"type":"instruction","id":1,"attributes":{"description":"Aperiam consequatur aut perspiciatis non omnis. Eos et corporis ipsa iure aut.","order":1},"relationships":{"recipe":{"data":{"type":"recipe","id":1},"links":{"self":"http://localhost:3001/api/v1/recipes/1"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/1/instructions"}}}
     */
    public function show(Recipe $recipe, Instruction $instruction)
    {
        return new InstructionResource($instruction);
    }

    /**
     * Replace an instruction
     *
     * @group Recipe/Instruction management
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.description string required
     * @response {"data":{"type":"instruction","id":1,"attributes":{"description":"Aperiam consequatur aut perspiciatis non omnis. Eos et corporis ipsa iure aut.","order":1},"relationships":{"recipe":{"data":{"type":"recipe","id":1},"links":{"self":"http://localhost:3001/api/v1/recipes/1"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/1/instructions"}}}
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
     * @group Recipe/Instruction management
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.description string required
     * @response {"data":{"type":"instruction","id":1,"attributes":{"description":"Aperiam consequatur aut perspiciatis non omnis. Eos et corporis ipsa iure aut.","order":1},"relationships":{"recipe":{"data":{"type":"recipe","id":1},"links":{"self":"http://localhost:3001/api/v1/recipes/1"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/1/instructions"}}}
     */
    public function update(UpdateInstructionRequest $request, Recipe $recipe, Instruction $instruction)
    {
        Gate::authorize('update', $recipe);
        $attributes = $request->mappedAttributes();
        $instruction->update($attributes);
        return new InstructionResource($instruction);
    }

    /**
     * Update order for instruction
     *
     * Bulk update operation for a recipes instructions, here all the recipes instructions must be provided,
     * otherwise we get an error.
     *
     * @group Recipe/Instruction management
     * @bodyParam data object[] required
     * @bodyParam data[].id int required
     * @bodyParam data[].attributes object required
     * @bodyParam data[].attributes.order integer required
     * @response {"data":{"type":"instruction","id":533,"attributes":{"description":"Test Instruction Description","order":8},"relationships":{"recipe":{"data":{"type":"recipe","id":10},"links":{"self":"http://localhost:3001/api/v1/recipes/10"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/10/instructions"}}}
     */
    public function updateOrder(UpdateInstructionOrderRequest $request, Recipe $recipe)
    {
        Gate::authorize('update', $recipe);
        $recipeId = $recipe->id;
        $instructionIds = Instruction::where('recipe_id', $recipeId)->pluck('id')->toArray();
        $instructionsData = $request->input('data');
        $providedIds = array_column($instructionsData, 'id');

        if (array_diff($instructionIds, $providedIds)) {
            return $this->error('All instructions must be included in the update.', 400);
        }

        DB::transaction(function () use ($instructionsData, $recipeId) {
            foreach ($instructionsData as $data) {
                Instruction::where('id', $data['id'])
                    ->where('recipe_id', $recipeId)
                    ->update(['order' => $data['attributes']['order']]);
            }
        });
        return InstructionResource::collection($recipe->instructions);
    }

    /**
     * Assign order for instruction
     *
     * Assign a new order to a single instruction, either moving it up or down
     *
     * @group Recipe/Instruction management
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.order integer required
     * @response {"data":{"type":"instruction","id":533,"attributes":{"description":"Test Instruction Description","order":8},"relationships":{"recipe":{"data":{"type":"recipe","id":10},"links":{"self":"http://localhost:3001/api/v1/recipes/10"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/10/instructions"}}}
     */
    public function assignOrder(AssignInstructionOrderRequest $request, Recipe $recipe, Instruction $instruction)
    {
        Gate::authorize('update', $recipe);
        $currentOrder = $instruction->order;
        $newOrder = $request->input('data')['attributes']['order'];
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
     * @group Recipe/Instruction management
     * @response {"data":[],"message":"Ingredient successfully deleted","status":200}
     */
    public function destroy(Recipe $recipe, Instruction $instruction)
    {
        Gate::authorize('delete', $recipe);
        $instruction->delete();
        return $this->ok('Instruction successfully deleted');
    }
}
