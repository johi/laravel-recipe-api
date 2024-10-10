<?php

namespace App\Http\Requests\Api\V1;

use App\Permissions\V1\Abilities;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipeRequest extends BaseRecipeRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'data.relationships.author.data.id' => 'sometimes|integer',
            'data.relationships.category.data.id' => 'sometimes|integer',
            'data.attributes.title' => 'sometimes|string',
            'data.attributes.description' => 'sometimes|string',
            'data.attributes.preparationTimeMinutes' => 'sometimes|integer',
            'data.attributes.servings' => 'sometimes|integer',
            'data.attributes.imageUrl' => 'sometimes|string',
        ];
        if ($this->user()->tokenCan(Abilities::UPDATE_OWN_RECIPE)) {
            $rules['data.relationships.author.data.id'] = 'prohibited';
        }
        return $rules;
    }
}
