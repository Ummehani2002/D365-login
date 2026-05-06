<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'microsoft_id',
        'is_super_admin',
        'user_code',
        'telemetry_id',
        'provider',
        'enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'enabled' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    /**
     * Masters, Settings, and related routes (no role gating — any signed-in user).
     */
    public function canAccessAdminScreens(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! Schema::hasTable('companies') || ! Schema::hasTable('company_memberships')) {
            return true;
        }

        $selectedCompanyCode = strtoupper(trim((string) request()->query('company', '')));

        $company = null;
        if ($selectedCompanyCode !== '') {
            $company = Company::query()
                ->whereRaw('UPPER(d365_id) = ?', [$selectedCompanyCode])
                ->first();
        }

        if (! $company) {
            $fallbackMembership = $this->companyMemberships()
                ->with('company')
                ->first();
            $company = $fallbackMembership?->company;
        }

        if (! $company) {
            return true;
        }

        foreach ([
            'settings.access',
            'users.manage',
            'roles.manage',
            'permissions.manage',
            'menu_match.manage',
            'masters.access',
        ] as $permissionSlug) {
            if ($this->hasPermissionForCompany($company, $permissionSlug)) {
                return true;
            }
        }

        $hasAnyPermissionAssigned = $this->companyMemberships()
            ->whereHas('roles.permissions')
            ->exists();

        return ! $hasAnyPermissionAssigned;
    }

    /**
     * Uppercase D365 company codes the user may work in (all companies if super admin).
     *
     * @return Collection<int, string>
     */
    public function accessibleCompanyD365Codes(): Collection
    {
        if (! Schema::hasTable('company_memberships')) {
            return collect();
        }

        if ($this->isSuperAdmin()) {
            return Company::query()
                ->whereNotNull('d365_id')
                ->pluck('d365_id')
                ->map(fn (mixed $id) => strtoupper((string) $id))
                ->unique()
                ->values();
        }

        /** @var EloquentCollection<int, CompanyMembership> $rows */
        $rows = $this->companyMemberships()
            ->whereHas('company', fn ($q) => $q->whereNotNull('d365_id'))
            ->with('company')
            ->get();

        return $rows
            ->pluck('company.d365_id')
            ->filter()
            ->map(fn (mixed $id) => strtoupper((string) $id))
            ->unique()
            ->values();
    }

    public function companyMemberships(): HasMany
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function membershipForCompany(Company $company): ?CompanyMembership
    {
        return $this->companyMemberships()
            ->where('company_id', $company->id)
            ->first();
    }

    public function hasPermissionForCompany(Company $company, string $permissionSlug): bool
    {
        if ($permissionSlug === '') {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        // D365-style: permissions are granted by roles on the user's memberships, scoped to the target company (organization).
        $memberships = $this->companyMemberships()
            ->with(['roles.permissions', 'roleScopes.companies'])
            ->get();

        if ($memberships->isEmpty()) {
            return false;
        }

        foreach ($memberships as $membership) {
            foreach ($membership->roles as $role) {
                if (! $role->hasPermission($permissionSlug)) {
                    continue;
                }
                if ($membership->roleHasAccessToCompany($role, $company)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canManageCompanyUsers(Company $company): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! Schema::hasTable('company_memberships')) {
            return true;
        }

        $memberships = CompanyMembership::query()
            ->where('company_id', $company->id)
            ->with('roles.permissions')
            ->get();

        if ($memberships->isEmpty()) {
            return true;
        }

        if ($this->hasPermissionForCompany($company, 'users.manage')) {
            return true;
        }

        $companyHasUserAdmin = $memberships->contains(
            fn (CompanyMembership $m) => $m->roles->contains(
                fn (Role $r) => $r->hasPermission('users.manage')
            )
        );

        return ! $companyHasUserAdmin;
    }
}
