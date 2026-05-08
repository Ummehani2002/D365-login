<?php

namespace App\Models\Rbac;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    protected $fillable = ['slug', 'name'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Role::class, 'permission_role');
    }

    public function menuMatches(): HasMany
    {
        return $this->hasMany(\App\Models\MenuPermissionMatch::class, 'permission_id');
    }
}
