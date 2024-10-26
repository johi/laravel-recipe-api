<?php
declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructionResource extends JsonResource
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
            'type' => 'instruction',
            'id' => $this->id,
            'attributes' => [
                'description' => $this->description,
                'order' => $this->order,
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
                'self' => route('instructions.index', ['recipe' => $recipeId]),
            ]
        ];
    }
}
