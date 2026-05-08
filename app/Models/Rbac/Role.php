<?php

namespace App\Models\Rbac;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['company_id', 'name', 'slug'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Permission::class, 'permission_role');
    }

    public function companyMemberships(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\CompanyMembership::class, 'company_membership_roles');
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions->contains('slug', $slug);
    }

    public static function ensurePresetRoles(\App\Models\Company $company): void
    {
        if (!\App\Models\Permission::query()->exists()) {
            return;
        }

        $allIds = \App\Models\Permission::query()->pluck('id');

        $admin = static::query()->firstOrCreate(
            ['company_id' => $company->id, 'slug' => 'admin'],
            ['name' => 'Admin']
        );
        $admin->permissions()->sync($allIds);

        $userSlugs = ['settings.access', 'modules.access', 'item_issue.access'];
        $userIds   = \App\Models\Permission::query()->whereIn('slug', $userSlugs)->pluck('id');
        $user = static::query()->firstOrCreate(
            ['company_id' => $company->id, 'slug' => 'user'],
            ['name' => 'User']
        );
        $user->permissions()->sync($userIds);

        $keeperSlugs = ['settings.access', 'pr.access', 'grn.access'];
        $keeperIds   = \App\Models\Permission::query()->whereIn('slug', $keeperSlugs)->pluck('id');
        $keeper = static::query()->firstOrCreate(
            ['company_id' => $company->id, 'slug' => 'store_keeper'],
            ['name' => 'Store keeper']
        );
        $keeper->permissions()->sync($keeperIds);
    }

    /** @deprecated Use ensurePresetRoles() */
    public static function bootstrapForCompany(\App\Models\Company $company): void
    {
        static::ensurePresetRoles($company);
    }
}
