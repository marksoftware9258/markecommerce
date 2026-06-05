<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    // Uses auto-increment integer ID by default.
    // Swap to UUID primary key by overriding $primaryKey and $keyType.
    // This trait exposes a public `uuid` column for external references.

    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        // Resolve model binding by ID (default) or UUID via ?use_uuid=1
        return request()->boolean('use_uuid') ? 'uuid' : $this->getKeyName();
    }
}
