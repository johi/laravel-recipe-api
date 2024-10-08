<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BaseRecipeRequest extends FormRequest
{
    public function mappedAttributes(): array
    {
        $mappedAttributes = [
            'data.relationships.author.data.id' => 'user_id',
            'data.relationships.category.data.id' => 'category_id',
            'data.attributes.title' => 'title',
            'data.attributes.description' => 'description',
            'data.attributes.preparationTimeMinutes' => 'preparation_time_minutes',
            'data.attributes.servings' => 'servings',
            'data.attributes.imageUrl' => 'image_url',
            'data.attributes.createdAt' => 'created_at',
            'data.attributes.updatedAt' => 'updated_at',
        ];
        $attributesToUpdate = [];
        foreach ($mappedAttributes as $key => $attribute) {
            if ($this->has($key)) {
                $attributesToUpdate[$attribute] = $this->input($key);
            }
        }

        return $attributesToUpdate;
    }
}
