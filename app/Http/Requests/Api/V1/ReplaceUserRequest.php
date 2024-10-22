<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceUserRequest extends BaseUserRequest
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
        $user = $this->route('user');
        $emailValidationFragment = '';
        if ($user) {
            $emailValidationFragment = ',' . $user->id;
        }
        $rules = [
            'data.attributes.name' => 'required|string',
            'data.attributes.email' => 'required|email|string|unique:users,email' . $emailValidationFragment,
            'data.attributes.isAdmin' => 'required|boolean',
            'data.attributes.password' => 'required|string',
        ];

        return $rules;
    }
}
