<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $roleId = $this->route('role')->id;
        return [
            'name'          => "sometimes|string|max:100|unique:roles,name,{$roleId}",
            'permissions'   => 'sometimes|array',
            'permissions.*' => 'string|exists:permissions,name',
        ];
    }
}
