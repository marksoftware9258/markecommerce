<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $menuId = $this->route('menu')->id;
        return [
            'name'          => 'sometimes|string|max:100',
            'slug'          => "sometimes|string|max:120|unique:menus,slug,{$menuId}",
            'url'           => 'sometimes|nullable|string|max:500',
            'route_name'    => 'sometimes|nullable|string|max:200',
            'icon'          => 'sometimes|nullable|string|max:100',
            'type'          => 'sometimes|in:sidebar,topbar,footer',
            'target'        => 'sometimes|in:_self,_blank',
            'order'         => 'sometimes|integer|min:0',
            'is_active'     => 'sometimes|boolean',
            'parent_id'     => "sometimes|nullable|exists:menus,id|not_in:{$menuId}",
            'roles'         => 'sometimes|array',
            'roles.*'       => 'integer|exists:roles,id',
            'permissions'   => 'sometimes|array',
            'permissions.*' => 'integer|exists:permissions,id',
            'metadata'      => 'sometimes|array',
        ];
    }
}
