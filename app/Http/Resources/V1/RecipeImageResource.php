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
        return [
            'type' => 'image',
            'id' => $this->uuid,
            'attributes' => [
                'imageUrl' => asset($this->file_path)
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
                'self' => route('recipes.images.index', ['recipe' => $this->recipe->uuid]),
            ]
        ];
    }
}
