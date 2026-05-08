<?php

namespace App\Models\Rbac;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyMembership extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Role::class, 'company_membership_roles')->withTimestamps();
    }

    public function roleScopes(): HasMany
    {
        return $this->hasMany(\App\Models\CompanyMembershipRoleScope::class, 'company_membership_id');
    }

    public function scopes(): HasMany
    {
        return $this->roleScopes();
    }

    public function hasPermission(string $slug): bool
    {
        $this->loadMissing('roles.permissions');

        return $this->roles->contains(fn (\App\Models\Role $role) => $role->hasPermission($slug));
    }
}
