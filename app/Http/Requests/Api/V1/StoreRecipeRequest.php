<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Category;
use App\Models\User;
use App\Permissions\V1\Abilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        $authorRule = 'required|uuid|exists:users,uuid';
        $rules = [
            $authorIdAttribute => $authorRule,
            'data.relationships.category.data.id' => 'required|uuid|exists:categories,uuid',
            'data.attributes.title' => 'required|string',
            'data.attributes.description' => 'required|string',
            'data.attributes.preparationTimeMinutes' => 'required|integer',
            'data.attributes.servings' => 'required|integer',
        ];
        $user = Auth::user();
        if ($user) {
            if ($user->tokenCan(Abilities::CREATE_OWN_RECIPE)) {
                $rules[$authorIdAttribute] = $authorRule . '|in:' . $user->uuid;
            }
        }
        return $rules;
    }

    public function messages() {
        $user = Auth::user();
        if ($user) {
            return [
                'data.relationships.author.data.id' => 'The data.relationships.author.data.id must match the users uuid.',
            ];
        }
        return [
            'data.relationships.author.data.id' => 'The data.relationships.author.data.id value is invalid.',
        ];
    }

}
