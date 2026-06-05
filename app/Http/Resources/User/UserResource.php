<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'avatar_url' => $this->avatar
                ? asset('storage/' . $this->avatar)
                : null,
            'status'     => [
                'value' => $this->status,
                'label' => $this->status?->label(),
                'color' => $this->status?->color(),
            ],
            'timezone'    => $this->timezone,
            'locale'      => $this->locale,
            'email_verified' => !is_null($this->email_verified_at),
            'two_factor_enabled' => $this->hasTwoFactorEnabled(),
            'roles'       => $this->whenLoaded('roles', fn () =>
                $this->roles->map(fn ($r) => [
                    'id'   => $r->id,
                    'name' => $r->name,
                ])
            ),
            'permissions' => $this->whenLoaded('permissions', fn () =>
                $this->permissions->pluck('name')
            ),
            'all_permissions' => $this->when(
                $request->routeIs('*.me') || $request->routeIs('*.profile.*'),
                fn () => $this->getAllPermissions()->pluck('name')
            ),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at->toIso8601String(),
            'deleted_at'    => $this->deleted_at?->toIso8601String(),
        ];
    }
}
