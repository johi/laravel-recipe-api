<?php
declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
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
            ],
            'links' => [
                'self' => route('categories.index')
            ]
        ];
    }
}
