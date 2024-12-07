<?php
declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeInstructionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'instruction',
            'id' => $this->uuid,
            'attributes' => [
                'description' => $this->description,
                'order' => $this->order,
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
                'self' => route('recipes.instructions.index', ['recipe' => $this->recipe->uuid]),
            ]
        ];
    }
}
