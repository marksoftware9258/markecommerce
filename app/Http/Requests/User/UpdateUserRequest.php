<?php

namespace App\Http\Requests\User;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('user')->id;
        return [
            'name'        => 'sometimes|string|max:100',
            'email'       => "sometimes|email|unique:users,email,{$userId}|max:200",
            'password'    => 'sometimes|string|min:8|confirmed',
            'phone'       => "sometimes|nullable|string|max:20|unique:users,phone,{$userId}",
            'status'      => ['sometimes', Rule::enum(UserStatus::class)],
            'timezone'    => 'sometimes|string|timezone',
            'locale'      => 'sometimes|string|max:10',
            'roles'       => 'sometimes|array',
            'roles.*'     => 'string|exists:roles,name',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string|exists:permissions,name',
        ];
    }
}
