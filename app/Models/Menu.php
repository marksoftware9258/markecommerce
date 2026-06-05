<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'url',
        'route_name',
        'icon',
        'type',        // sidebar | topbar | footer
        'target',      // _self | _blank
        'order',
        'is_active',
        'guard_name',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata'  => 'array',
        ];
    }

    // -----------------------------------------------
    // Relationships
    // -----------------------------------------------
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->orderBy('order')
            ->with('children');
    }

    public function roles()
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            'menu_role',
            'menu_id',
            'role_id'
        );
    }

    public function permissions()
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            'menu_permission',
            'menu_id',
            'permission_id'
        );
    }

    // -----------------------------------------------
    // Scopes
    // -----------------------------------------------
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // -----------------------------------------------
    // Helpers
    // -----------------------------------------------
    public static function buildTree(array $items, $parentId = null): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] === $parentId) {
                $item['children'] = static::buildTree($items, $item['id']);
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
