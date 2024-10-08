<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceRecipeRequest extends FormRequest
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
            'data.relationships.category.data.id' => 'required|integer',
            'data.attributes.title' => 'required|string',
            'data.attributes.description' => 'required|string',
            'data.attributes.preparationTimeMinutes' => 'required|integer',
            'data.attributes.servings' => 'required|integer',
            'data.attributes.imageUrl' => 'sometimes|string',
        ];
        if ($this->routeIs('recipes.replace')) {
            $rules['data.relationships.author.data.id'] = 'required|integer';
        }
        return $rules;
    }
}
