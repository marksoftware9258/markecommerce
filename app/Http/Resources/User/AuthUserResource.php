<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'avatar_url'      => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'status'          => $this->status,
            'email_verified'  => !is_null($this->email_verified_at),
            'two_factor_enabled' => $this->hasTwoFactorEnabled(),
            'roles'           => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permissions'     => $this->getAllPermissions()->pluck('name'),
            'last_login_at'   => $this->last_login_at?->toIso8601String(),
        ];
    }
}
