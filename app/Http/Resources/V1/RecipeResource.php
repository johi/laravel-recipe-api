<?php
declare(strict_types=1);

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
                    !$request->routeIs(['recipes.index', 'authors.recipes.index']),
                    $this->description
                ),
                'preparationTimeMinutes' => $this->preparation_time_minutes,
                'servings' => $this->servings,
                'imageUrl' => $this->image_url,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'type' => 'user',
                        'id' => $this->user_id
                    ],
                    'links' => [
                        'self' => route('authors.show', ['author' => $this->user_id])
                    ]
                ],
                'category' => [
                    'data' => [
                        'type' => 'category',
                        'id' => $this->category_id
                    ],
                    'links' => [
                        'self' => route('categories.index')
                    ]
                ]
            ],
            'included' => [
                'author' => new UserResource($this->author),
                'category' => new CategoryResource($this->whenLoaded('category')),
                'ingredients' => IngredientResource::collection($this->whenLoaded('ingredients')),
                'instructions' => InstructionResource::collection($this->whenLoaded('instructions')),
            ],
            'links' => [
                'self' => route('recipes.show', ['recipe' => $this->id])
            ]
        ];
    }
}
