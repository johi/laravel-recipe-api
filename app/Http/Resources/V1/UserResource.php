<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'user',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'email' => $this->email,
                'isAdmin' => $this->is_admin,
                $this->mergeWhen($request->routeIs('authors.*'), [
                    'emailVerifiedAt' => $this->email_verified_at,
                    'createdAt' => $this->created_at,
                    'updatedAt' => $this->updated_at,
                ]),
                'included' => [
                    'recipes' => RecipeResource::collection($this->whenLoaded('recipes')),
                ]
            ],
            'links' => [
                ['self' => route('authors.show', ['author' => $this->id])]
            ]
        ];
    }
}
