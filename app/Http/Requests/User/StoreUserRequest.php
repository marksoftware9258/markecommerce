<?php

namespace App\Http\Requests\User;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email|max:200',
            'password'    => ['required', Password::min(8)->letters()->mixedCase()->numbers()],
            'phone'       => 'nullable|string|max:20|unique:users,phone',
            'status'      => ['nullable', Rule::enum(UserStatus::class)],
            'timezone'    => 'nullable|string|timezone',
            'locale'      => 'nullable|string|max:10',
            'roles'       => 'nullable|array',
            'roles.*'     => 'string|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ];
    }
}
