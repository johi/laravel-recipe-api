<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class BaseRecipeRequest extends FormRequest
{
    public function mappedAttributes(array $otherAttributes = []): array
    {
        $mappedAttributes = array_merge([
            'data.relationships.author.data.id' => 'user_id',
            'data.relationships.category.data.id' => 'category_id',
            'data.attributes.title' => 'title',
            'data.attributes.description' => 'description',
            'data.attributes.preparationTimeMinutes' => 'preparation_time_minutes',
            'data.attributes.servings' => 'servings',
        ], $otherAttributes);
        $attributesToUpdate = [];
        foreach ($mappedAttributes as $key => $attribute) {
            if ($this->has($key)) {
                $attributesToUpdate[$attribute] = $this->input($key);
            }
        }

        return $this->resolveUuids($attributesToUpdate);
    }

    protected function resolveUuids(array $attributes): array
    {
        // Resolve UUIDs to surrogate IDs
        if (isset($attributes['user_id'])) {
            $attributes['user_id'] = User::where('uuid', $attributes['user_id'])->value('id');
        }

        if (isset($attributes['category_id'])) {
            $attributes['category_id'] = Category::where('uuid', $attributes['category_id'])->value('id');
        }

        return $attributes;
    }
}
