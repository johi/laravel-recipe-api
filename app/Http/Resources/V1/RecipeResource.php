<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
//    public static $wrap = 'recipe';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // still need to map: author, category, instructions, ingredients
        return [
            'type' => 'recipe',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'description' => $this->when(
                    $request->routeIs('tickets.show'),
                    $this->description
                ),
                'preparationTimeMinutes' => $this->preparation_time_minutes,
                'servings' => $this->servings,
                'imageUrl' => $this->image_url,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at
            ],
            'included' => [
                'author' => new UserResource($this->user),
                'category' => new CategoryResource($this->whenLoaded('category')),
                'ingredients' => IngredientResource::collection($this->whenLoaded('ingredients')),
                'instructions' => InstructionResource::collection($this->whenLoaded('instructions')),
            ],
            'links' => [
                ['self' => route('recipes.show', ['recipe' => $this->id])]
            ]
        ];
    }
}
