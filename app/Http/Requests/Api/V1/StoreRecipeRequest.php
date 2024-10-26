<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\User;
use App\Permissions\V1\Abilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
        $authorIdAttribute = 'data.relationships.author.data.id';
        $authorRule = 'required|integer|exists:users,id';
        $rules = [
            $authorIdAttribute => $authorRule,
            'data.relationships.category.data.id' => 'required|integer',
            'data.attributes.title' => 'required|string',
            'data.attributes.description' => 'required|string',
            'data.attributes.preparationTimeMinutes' => 'required|integer',
            'data.attributes.servings' => 'required|integer',
            'data.attributes.imageUrl' => 'sometimes|string',
        ];
        $user = Auth::user();
        if ($user) {
            if ($user->tokenCan(Abilities::CREATE_OWN_RECIPE)) {
                $rules[$authorIdAttribute] = $authorRule . '|size:' . $user->id;
            }
        }
        return $rules;
    }

    public function messages() {
        $user = Auth::user();
        if ($user) {
            return [
                'data.relationships.author.data.id' => 'The data.relationships.author.data.id must match the users id.',
            ];
        }
        return [
            'data.relationships.author.data.id' => 'The data.relationships.author.data.id value is invalid.',
        ];
    }

}
