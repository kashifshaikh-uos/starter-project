<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cnic_no',
        'email',
        'phone',
        'password',
        'is_active',
        'active_role_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('is_active', 'created_at', 'updated_at');
    }

    public function activeRoles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->wherePivot('is_active', true)
            ->withPivot('is_active', 'created_at', 'updated_at');
    }

    public function activeRole()
    {
        return $this->belongsTo(Role::class, 'active_role_id');
    }

    public function hasPrivilege(string $slug): bool
    {
        if (!$this->active_role_id) return false;

        return Role::where('id', $this->active_role_id)
            ->where('is_active', true)
            ->whereHas('privileges', function ($q) use ($slug) {
                $q->where('privileges.slug', $slug)->where('privileges.is_active', true);
            })->exists();
    }

    public function hasRole(string $slug): bool
    {
        return $this->activeRoles()->where('slug', $slug)->where('is_active', true)->exists();
    }

    public function getAllPrivileges()
    {
        if (!$this->active_role_id) return collect();

        return Privilege::whereHas('roles', function ($q) {
            $q->where('roles.id', $this->active_role_id)
              ->where('roles.is_active', true);
        })->where('is_active', true)->get();
    }

    public function getMenuItems()
    {
        if (!$this->active_role_id) return collect();

        $userPrivilegeIds = Privilege::whereHas('roles', function ($q) {
            $q->where('roles.id', $this->active_role_id)
              ->where('roles.is_active', true);
        })->where('is_active', true)->pluck('id');

        return Privilege::whereIn('id', $userPrivilegeIds)
            ->where('show_in_menu', true)
            ->whereNull('parent_id')
            ->with('menuChildren')
            ->orderBy('sort_order')
            ->get();
    }
}
