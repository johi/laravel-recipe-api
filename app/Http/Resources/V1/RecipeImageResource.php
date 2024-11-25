<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeImageResource extends JsonResource
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
            'type' => 'recipeImage',
            'id' => $this->id,
            'attributes' => [
                'imageUrl' => asset($this->file_path)
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
                'self' => route('recipes.images.index', ['recipe' => $recipeId]),
            ]
        ];
    }
}
