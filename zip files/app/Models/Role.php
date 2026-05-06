<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withTimestamps();
    }

    public function companyMemberships(): BelongsToMany
    {
        return $this->belongsToMany(CompanyMembership::class, 'company_membership_roles')
            ->withTimestamps();
    }

    public function hasPermission(string $slug): bool
    {
        $this->loadMissing('permissions:id,slug');

        return $this->permissions->contains('slug', $slug);
    }
}
