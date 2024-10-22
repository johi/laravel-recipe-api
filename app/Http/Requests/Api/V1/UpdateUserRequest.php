<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends BaseUserRequest
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
        return [
            'data.attributes.name' => 'sometimes|string',
            'data.attributes.email' => 'sometimes|email|string|unique:users,email' . $emailValidationFragment,
            'data.attributes.isAdmin' => 'sometimes|boolean',
            'data.attributes.password' => 'sometimes|string',
        ];
    }
}
