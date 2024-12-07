<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\AssignInstructionOrderRequest;
use App\Http\Requests\Api\V1\ReplaceRecipeInstructionRequest;
use App\Http\Requests\Api\V1\StoreRecipeInstructionRequest;
use App\Http\Requests\Api\V1\UpdateInstructionOrderRequest;
use App\Http\Requests\Api\V1\UpdateRecipeInstructionRequest;
use App\Http\Resources\V1\RecipeInstructionResource;
use App\Models\RecipeInstruction;
use App\Models\Recipe;
use App\Policies\V1\RecipePolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RecipeInstructionsController extends ApiController
{
    protected string $policyClass = RecipePolicy::class;

    /**
     * Get all instructions for a recipe
     *
     * @group RecipeInstructions
     * @response {"data":[{"type":"instruction","id":"7e8af067-23b9-451b-ab48-a29256529f63","attributes":{"description":"Veritatis autem pariatur minima tenetur esse. Sapiente ducimus sed quia sapiente.","order":1},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}},{"type":"instruction","id":"13c87437-ebdf-4107-a0e7-d5f229e1ef99","attributes":{"description":"Et adipisci vel enim vel. Itaque quis neque enim consequatur inventore vero dolorum.","order":2},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}},{"type":"instruction","id":"b6461bcb-a732-4510-8e84-29990982b9fe","attributes":{"description":"Quae earum eveniet autem magni.","order":3},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}},{"type":"instruction","id":"0540a660-0ce5-4f0b-8872-4b83eac67bc2","attributes":{"description":"Repellendus est enim odit inventore neque. Expedita sapiente fuga dolor aliquam id et ex.","order":4},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}},{"type":"instruction","id":"58e1b0de-afed-4566-bb8f-96069adc6ad4","attributes":{"description":"Quod amet delectus amet qui optio.","order":5},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}},{"type":"instruction","id":"d835b0a9-2753-4c88-babc-c6306b326483","attributes":{"description":"Ipsa cupiditate eum quia facere nostrum quis est maxime.","order":6},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}},{"type":"instruction","id":"e8d8c955-00d3-4266-9809-a30a0e74ba2b","attributes":{"description":"Eveniet vero voluptas enim distinctio. Doloribus deleniti odio officiis.","order":7},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}},{"type":"instruction","id":"7ecf7f11-0bfd-4ea6-ba21-c0f699a855da","attributes":{"description":"Voluptas officiis cupiditate voluptas rem. Perspiciatis molestiae voluptas quia facilis harum dolores voluptatem.","order":8},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}},{"type":"instruction","id":"a5dcc70c-1282-4310-ab05-725984fc71c0","attributes":{"description":"Consequatur commodi neque quo assumenda ullam. Porro dolor itaque ut rerum aut unde.","order":9},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}},{"type":"instruction","id":"155c30c5-d153-46f7-9d10-26c1718f6a44","attributes":{"description":"Repellat doloremque et similique.","order":10},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}}]}
     */
    public function index(Recipe $recipe)
    {
        return RecipeInstructionResource::collection($recipe->instructions);
    }

    /**
     * Add instruction to recipe
     *
     * @group RecipeInstructions
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.description string required
     * @response {"data":{"type":"instruction","id":"7e8af067-23b9-451b-ab48-a29256529f63","attributes":{"description":"Veritatis autem pariatur minima tenetur esse. Sapiente ducimus sed quia sapiente.","order":1},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}}}
     */
    public function store(StoreRecipeInstructionRequest $request, Recipe $recipe)
    {
        Gate::authorize('storeRelated', $recipe);
        $attributes = $request->mappedAttributes();
        $nextOrder = $recipe->instructions()->max('order') + 1;
        $attributes['order'] = $nextOrder;
        return new RecipeInstructionResource($recipe->instructions()->create($attributes));
    }

    /**
     * Get a single instruction
     *
     * @group RecipeInstructions
     * @response {"data":{"type":"instruction","id":"7e8af067-23b9-451b-ab48-a29256529f63","attributes":{"description":"Veritatis autem pariatur minima tenetur esse. Sapiente ducimus sed quia sapiente.","order":1},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}}}
     */
    public function show(Recipe $recipe, RecipeInstruction $instruction)
    {
        return new RecipeInstructionResource($instruction);
    }

    /**
     * Replace an instruction
     *
     * @group RecipeInstructions
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.description string required
     * @response {"data":{"type":"instruction","id":"7e8af067-23b9-451b-ab48-a29256529f63","attributes":{"description":"Veritatis autem pariatur minima tenetur esse. Sapiente ducimus sed quia sapiente.","order":1},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}}}
     */
    public function replace(ReplaceRecipeInstructionRequest $request, Recipe $recipe, RecipeInstruction $instruction)
    {
        Gate::authorize('replace', $recipe);
        $attributes = $request->mappedAttributes();
        $instruction->update($attributes);
        return new RecipeInstructionResource($instruction);
    }

    /**
     * Update an instruction
     *
     * @group RecipeInstructions
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.description string required
     * @response {"data":{"type":"instruction","id":"7e8af067-23b9-451b-ab48-a29256529f63","attributes":{"description":"Veritatis autem pariatur minima tenetur esse. Sapiente ducimus sed quia sapiente.","order":1},"relationships":{"recipe":{"data":{"type":"recipe","id":"268b64ce-170d-4c39-97fa-515a531da1d2"},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/268b64ce-170d-4c39-97fa-515a531da1d2/instructions"}}}
     */
    public function update(UpdateRecipeInstructionRequest $request, Recipe $recipe, RecipeInstruction $instruction)
    {
        Gate::authorize('update', $recipe);
        $attributes = $request->mappedAttributes();
        $instruction->update($attributes);
        return new RecipeInstructionResource($instruction);
    }

    /**
     * Update order for instruction
     *
     * Bulk update operation for a recipes instructions, here all the recipes instructions must be provided,
     * otherwise we get an error.
     *
     * @group RecipeInstructions
     * @bodyParam data object[] required
     * @bodyParam data[].id uuid required Example: b16d6b53-9ab6-4090-9189-e7bad7a4b8d4
     * @bodyParam data[].attributes object required
     * @bodyParam data[].attributes.order integer required
     * @response {"data":[{"type":"instruction","id":"99f1bfec-3cc8-4527-9fa7-d6875fb95860","attributes":{"description":"Test Instruction Description","order":1},"relationships":{"recipe":{"data":{"type":"recipe","id":"cea9a46f-524e-4aca-8471-2d0cd7e31864"},"links":{"self":"http://localhost:3001/api/v1/recipes/cea9a46f-524e-4aca-8471-2d0cd7e31864"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/cea9a46f-524e-4aca-8471-2d0cd7e31864/instructions"}},{"type":"instruction","id":"b16d6b53-9ab6-4090-9189-e7bad7a4b8d4","attributes":{"description":"Test Instruction Description","order":2},"relationships":{"recipe":{"data":{"type":"recipe","id":"cea9a46f-524e-4aca-8471-2d0cd7e31864"},"links":{"self":"http://localhost:3001/api/v1/recipes/cea9a46f-524e-4aca-8471-2d0cd7e31864"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/cea9a46f-524e-4aca-8471-2d0cd7e31864/instructions"}}]}
     */
    public function updateOrder(UpdateInstructionOrderRequest $request, Recipe $recipe)
    {

        Gate::authorize('update', $recipe);
        $recipeId = $recipe->id;
        $instructionUuids = RecipeInstruction::where('recipe_id', $recipeId)->pluck('uuid')->toArray();
        $instructionsData = $request->input('data');
        $providedUuids = array_column($instructionsData, 'id');

        if (array_diff($instructionUuids, $providedUuids)) {
            return $this->error('All instructions must be included in the update.', 400);
        }
        // Get total number of instructions
        $instructionCount = RecipeInstruction::where('recipe_id', $recipeId)->count();
        // Check if all provided orders are within the valid range (1 to total count)
        foreach ($instructionsData as $data) {
            if ($data['attributes']['order'] > $instructionCount) {
                return $this->error('Order values must be within the valid range.', 400);
            }
        }
        DB::transaction(function () use ($instructionsData, $recipeId) {
            foreach ($instructionsData as $data) {
                RecipeInstruction::where('uuid', $data['id'])
                    ->where('recipe_id', $recipeId)
                    ->update(['order' => $data['attributes']['order']]);
            }
        });
        return RecipeInstructionResource::collection($recipe->instructions);
    }

    /**
     * Assign order for instruction
     *
     * Assign a new order to a single instruction, either moving it up or down
     *
     * @group RecipeInstructions
     * @bodyParam data object required
     * @bodyParam data.attributes object required
     * @bodyParam data.attributes.order integer required
     * @response {"data":[{"type":"instruction","id":"b16d6b53-9ab6-4090-9189-e7bad7a4b8d4","attributes":{"description":"Test Instruction Description","order":1},"relationships":{"recipe":{"data":{"type":"recipe","id":"cea9a46f-524e-4aca-8471-2d0cd7e31864"},"links":{"self":"http://localhost:3001/api/v1/recipes/cea9a46f-524e-4aca-8471-2d0cd7e31864"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/cea9a46f-524e-4aca-8471-2d0cd7e31864/instructions"}},{"type":"instruction","id":"99f1bfec-3cc8-4527-9fa7-d6875fb95860","attributes":{"description":"Test Instruction Description","order":2},"relationships":{"recipe":{"data":{"type":"recipe","id":"cea9a46f-524e-4aca-8471-2d0cd7e31864"},"links":{"self":"http://localhost:3001/api/v1/recipes/cea9a46f-524e-4aca-8471-2d0cd7e31864"}}},"links":{"self":"http://localhost:3001/api/v1/recipes/cea9a46f-524e-4aca-8471-2d0cd7e31864/instructions"}}]}
     */
    public function assignOrder(AssignInstructionOrderRequest $request, Recipe $recipe, RecipeInstruction $instruction)
    {
        Gate::authorize('update', $recipe);
        $currentOrder = $instruction->order;
        $newOrder = $request->input('data')['attributes']['order'];
        $recipeId = $recipe->id;

        $instructionCount = RecipeInstruction::where('recipe_id', $recipeId)->count();
        if ($newOrder < 1 || $newOrder > $instructionCount) {
            return $this->error('Order must be between 1 and ' . $instructionCount . '.', 400);
        }

        DB::transaction(function () use ($recipeId, $instruction, $currentOrder, $newOrder) {
            if ($currentOrder < $newOrder) {
                RecipeInstruction::where('recipe_id', $recipeId)
                    ->whereBetween('order', [$currentOrder + 1, $newOrder])
                    ->decrement('order');
            } elseif ($currentOrder > $newOrder) {
                RecipeInstruction::where('recipe_id', $recipeId)
                    ->whereBetween('order', [$newOrder, $currentOrder - 1])
                    ->increment('order');
            }
            $instruction->update(['order' => $newOrder]);
        });
        return RecipeInstructionResource::collection($recipe->instructions);
    }

    /**
     * Delete an instruction
     *
     * @group RecipeInstructions
     * @response {"data":[],"message":"Ingredient successfully deleted","status":200}
     */
    public function destroy(Recipe $recipe, RecipeInstruction $instruction)
    {
        Gate::authorize('delete', $recipe);
        $instruction->delete();
        return $this->ok('Instruction successfully deleted');
    }
}
