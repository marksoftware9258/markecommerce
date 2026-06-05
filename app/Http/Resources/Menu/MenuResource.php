<?php

namespace App\Http\Resources\Menu;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'url'         => $this->url,
            'route_name'  => $this->route_name,
            'icon'        => $this->icon,
            'type'        => $this->type,
            'target'      => $this->target,
            'order'       => $this->order,
            'is_active'   => $this->is_active,
            'parent_id'   => $this->parent_id,
            'metadata'    => $this->metadata,
            'children'    => MenuResource::collection($this->whenLoaded('children')),
            'roles'       => $this->whenLoaded('roles', fn () =>
                $this->roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])
            ),
            'permissions' => $this->whenLoaded('permissions', fn () =>
                $this->permissions->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])
            ),
            'created_at'  => $this->created_at->toIso8601String(),
            'updated_at'  => $this->updated_at->toIso8601String(),
        ];
    }
}
