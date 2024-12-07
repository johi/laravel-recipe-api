<?php
declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeIngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'ingredient',
            'id' => $this->uuid,
            'attributes' => [
                'title' => $this->title,
                'quantity' => $this->quantity,
                'unit' => $this->unit,
            ],
            'relationships' => [
                'recipe' => [
                    'data' => [
                        'type' => 'recipe',
                        'id' => $this->recipe->uuid
                    ],
                    'links' => [
                        'self' => route('recipes.show', ['recipe' => $this->recipe->uuid])
                    ]
                ]
            ],
            'links' => [
                'self' => route('recipes.ingredients.index', ['recipe' => $this->recipe->uuid]),
            ]
        ];
    }
}
