<?php

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
        return [
            'type' => 'ingredient',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'quantity' => $this->quantity,
                'unit' => $this->unit,
            ]
        ];
    }
}
