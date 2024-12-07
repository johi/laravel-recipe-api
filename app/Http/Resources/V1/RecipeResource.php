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
        return [
            'type' => 'recipe',
            'id' => $this->uuid,
            'attributes' => [
                'title' => $this->title,
                'description' => $this->when(
                    !$request->routeIs(['recipes.index', 'authors.recipes.index']),
                    $this->description
                ),
                'preparationTimeMinutes' => $this->preparation_time_minutes,
                'servings' => $this->servings,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'type' => 'user',
                        'id' => $this->author->uuid
                    ],
                    'links' => [
                        'self' => route('authors.show', ['author' => $this->author->uuid])
                    ]
                ],
                'category' => [
                    'data' => [
                        'type' => 'category',
                        'id' => $this->category->uuid
                    ],
                    'links' => [
                        'self' => route('categories.index')
                    ]
                ]
            ],
            'included' => [
                'author' => new UserResource($this->author),
                'category' => new CategoryResource($this->whenLoaded('category')),
                'ingredients' => RecipeIngredientResource::collection($this->whenLoaded('ingredients')),
                'instructions' => RecipeInstructionResource::collection($this->whenLoaded('instructions')),
            ],
            'links' => [
                'self' => route('recipes.show', ['recipe' => $this->uuid])
            ]
        ];
    }
}
