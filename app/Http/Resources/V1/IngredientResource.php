<?php
declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $recipeId = is_string($request->route('recipe'))
            ? $request->route('recipe') : $request->route('recipe')->id;
        return [
            'type' => 'ingredient',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'quantity' => $this->quantity,
                'unit' => $this->unit,
            ],
            'relationships' => [
                'recipe' => [
                    'data' => [
                        'type' => 'recipe',
                        'id' => $recipeId
                    ],
                    'links' => [
                        'self' => route('recipes.show', ['recipe' => $recipeId])
                    ]
                ]
            ],
            'links' => [
                'self' => route('ingredients.index', ['recipe' => $recipeId]),
            ]
        ];
    }
}
