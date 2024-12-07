<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BaseRecipeIngredientRequest extends FormRequest
{
    public function mappedAttributes(array $otherAttributes = []): array
    {
        $mappedAttributes = array_merge([
            'data.attributes.title' => 'title',
            'data.attributes.quantity' => 'quantity',
            'data.attributes.unit' => 'unit',
        ], $otherAttributes);
        $attributesToUpdate = [];
        foreach ($mappedAttributes as $key => $attribute) {
            if ($this->has($key)) {
                $attributesToUpdate[$attribute] = $this->input($key);
            }
        }

        return $attributesToUpdate;
    }
}
