<?php

namespace App\Http\Requests\Api\V1;

use App\Permissions\V1\Abilities;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeRequest extends BaseRecipeRequest
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
        $authorIdAttribute = $this->routeIs('recipes.store') ? 'data.relationships.author.data.id' : 'author';
        $user = $this->user();
        $authorRule = 'required|integer|exists:users,id';
        $rules = [
            $authorIdAttribute => $authorRule . '|size:' . $user->id,
            'data.relationships.category.data.id' => 'required|integer',
            'data.attributes.title' => 'required|string',
            'data.attributes.description' => 'required|string',
            'data.attributes.preparationTimeMinutes' => 'required|integer',
            'data.attributes.servings' => 'required|integer',
            'data.attributes.imageUrl' => 'sometimes|string',
        ];
        if ($user()->tokenCan(Abilities::CREATE_RECIPE)) {
            $rules[$authorIdAttribute] .= $authorIdAttribute;
        }
        return $rules;
    }

    public function messages() {
        return [
            'data.relationships.author.data.id' => 'The data.relationships.author.data.id value is invalid.',
            'author' => 'The authors id must match with current users id',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->routeIs('authors.recipes.store')) {
            $this->merge([
                'author' => $this->route('author')
            ]);
        }
    }
}
