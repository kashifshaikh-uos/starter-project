<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivilegeGroup extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function privileges()
    {
        return $this->hasMany(Privilege::class);
    }
}
