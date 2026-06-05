<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => 'sometimes|string|max:100',
            'phone'    => 'sometimes|nullable|string|max:20|unique:users,phone,' . auth()->id(),
            'timezone' => 'sometimes|string|timezone',
            'locale'   => 'sometimes|string|max:10',
            'metadata' => 'sometimes|array',
        ];
    }
}
