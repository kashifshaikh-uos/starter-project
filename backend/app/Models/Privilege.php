<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Privilege extends Model
{
    protected $fillable = [
        'privilege_group_id',
        'parent_id',
        'name',
        'slug',
        'frontend_route',
        'api_route',
        'method',
        'menu_type',
        'icon',
        'sort_order',
        'show_in_menu',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function group()
    {
        return $this->belongsTo(PrivilegeGroup::class, 'privilege_group_id');
    }

    public function parent()
    {
        return $this->belongsTo(Privilege::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Privilege::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function menuChildren()
    {
        return $this->children()
            ->where('is_active', true)
            ->where('show_in_menu', true)
            ->orderBy('sort_order')
            ->with('menuChildren');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'privilege_role');
    }
}
