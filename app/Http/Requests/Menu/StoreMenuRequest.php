<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:100',
            'slug'          => 'sometimes|string|max:120|unique:menus,slug',
            'url'           => 'sometimes|nullable|string|max:500',
            'route_name'    => 'sometimes|nullable|string|max:200',
            'icon'          => 'sometimes|nullable|string|max:100',
            'type'          => 'sometimes|in:sidebar,topbar,footer',
            'target'        => 'sometimes|in:_self,_blank',
            'order'         => 'sometimes|integer|min:0',
            'is_active'     => 'sometimes|boolean',
            'parent_id'     => 'sometimes|nullable|exists:menus,id',
            'roles'         => 'sometimes|array',
            'roles.*'       => 'integer|exists:roles,id',
            'permissions'   => 'sometimes|array',
            'permissions.*' => 'integer|exists:permissions,id',
            'metadata'      => 'sometimes|array',
        ];
    }
}
