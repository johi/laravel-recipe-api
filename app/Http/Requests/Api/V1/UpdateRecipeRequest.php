<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Category;
use App\Models\User;
use App\Permissions\V1\Abilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'data.relationships.author.data.id' => 'prohibited',
            'data.relationships.category.data.id' => 'sometimes|uuid|exists:categories,uuid',
            'data.attributes.title' => 'sometimes|string',
            'data.attributes.description' => 'sometimes|string',
            'data.attributes.preparationTimeMinutes' => 'sometimes|integer',
            'data.attributes.servings' => 'sometimes|integer',
        ];
        $user = Auth::user();
        if ($user) {
            if ($user->tokenCan(Abilities::UPDATE_RECIPE)) {
                $rules['data.relationships.author.data.id'] = 'sometimes|uuid|exists:users,uuid';
            }
        }
        return $rules;
    }
}
