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
        'user_code',
        'provider',
        'microsoft_id',
        'is_super_admin',
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
        ];
    }

    public function isSuperAdmin(): bool
    {
        if ((bool) $this->is_super_admin) {
            return true;
        }

        $email = strtolower(trim((string) $this->email));
        $allowList = config('access.super_admin_emails', []);

        if ($email !== '' && in_array($email, $allowList, true)) {
            return true;
        }

        // Backward-compatible fallback: honor legacy RBAC role naming.
        return $this->hasAnyRoleSlugOrName([
            'superadmin',
            'super_admin',
            'super admin',
        ]);
    }

    public function canAccessAdminScreens(): bool
    {
        return $this->canAccessMasters();
    }

    public function canAccessMasters(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (
            ! Schema::hasTable('company_memberships')
            || ! Schema::hasTable('company_membership_roles')
            || ! Schema::hasTable('roles')
        ) {
            return false;
        }

        try {
            $hasRoleSlugColumn = Schema::hasColumn('roles', 'slug');
            $hasRoleNameColumn = Schema::hasColumn('roles', 'name');

            if (! $hasRoleSlugColumn && ! $hasRoleNameColumn) {
                return false;
            }

            return CompanyMembership::query()
                ->where('user_id', $this->id)
                ->whereHas('roles', function ($query) use ($hasRoleSlugColumn, $hasRoleNameColumn) {
                    $query->where(function ($inner) use ($hasRoleSlugColumn, $hasRoleNameColumn) {
                        if ($hasRoleSlugColumn) {
                            $inner->orWhereRaw('LOWER(slug) = ?', ['admin']);
                        }
                        if ($hasRoleNameColumn) {
                            $inner->orWhereRaw('LOWER(name) = ?', ['admin']);
                        }
                    });
                })
                ->exists();
        } catch (\Throwable) {
            return false;
        }
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

        if ($this->isSuperAdmin() || $this->isCompanyAdmin()) {
            return Company::query()
                ->whereNotNull('d365_id')
                ->pluck('d365_id')
                ->map(fn (mixed $id) => strtoupper((string) $id))
                ->unique()
                ->values();
        }

        $hasRoleScopeTables = Schema::hasTable('company_membership_role_scopes')
            && Schema::hasTable('company_membership_role_scope_companies');

        $membershipQuery = $this->companyMemberships()
            ->whereHas('company', fn ($q) => $q->whereNotNull('d365_id'))
            ->with('company');

        if ($hasRoleScopeTables) {
            $membershipQuery->with('roleScopes.companies');
        }

        /** @var EloquentCollection<int, CompanyMembership> $rows */
        $rows = $membershipQuery->get();

        if (! $hasRoleScopeTables) {
            return $rows
                ->pluck('company.d365_id')
                ->filter()
                ->map(fn (mixed $id) => strtoupper((string) $id))
                ->unique()
                ->values();
        }

        $scopeRows = $rows->flatMap(function (CompanyMembership $membership) {
            return $membership->roleScopes;
        });

        if ($scopeRows->contains(fn (CompanyMembershipRoleScope $scope) => (bool) $scope->all_organizations)) {
            return Company::query()
                ->whereNotNull('d365_id')
                ->pluck('d365_id')
                ->map(fn (mixed $id) => strtoupper((string) $id))
                ->unique()
                ->values();
        }

        $scopedCodes = $scopeRows
            ->flatMap(fn (CompanyMembershipRoleScope $scope) => $scope->companies->pluck('d365_id'))
            ->filter()
            ->map(fn (mixed $id) => strtoupper((string) $id))
            ->unique()
            ->values();

        if ($scopedCodes->isNotEmpty()) {
            return $scopedCodes;
        }

        // Backward-compatible fallback when role scopes are not populated yet.
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
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! Schema::hasTable('company_memberships')) {
            return false;
        }

        try {
            // A company admin has full permissions across all companies (except token/credentials which is route-level).
            if ($this->isCompanyAdmin()) {
                return true;
            }

            $hasRoleTables = Schema::hasTable('company_membership_roles')
                && Schema::hasTable('permission_role')
                && Schema::hasTable('permissions');

            if (! $hasRoleTables) {
                $membership = $this->membershipForCompany($company);
                return $membership ? $membership->hasPermission($permissionSlug) : false;
            }

            $hasRoleScopeTables = Schema::hasTable('company_membership_role_scopes')
                && Schema::hasTable('company_membership_role_scope_companies');

            $memberships = $this->companyMemberships()
                ->with(['roles.permissions'])
                ->when($hasRoleScopeTables, fn ($q) => $q->with('roleScopes.companies:id,d365_id,name'))
                ->get();

            foreach ($memberships as $membership) {
                foreach ($membership->roles as $role) {
                    if (! $role->hasPermission($permissionSlug)) {
                        continue;
                    }

                    if ($hasRoleScopeTables) {
                        $scope = $membership->roleScopes->firstWhere('role_id', $role->id);
                        if ($scope) {
                            if ((bool) $scope->all_organizations) {
                                return true;
                            }

                            $allowedCompanyIds = $scope->companies->pluck('id')->map(fn (mixed $id) => (int) $id);
                            if ($allowedCompanyIds->contains((int) $company->id)) {
                                return true;
                            }

                            // Scope exists for this role but target company isn't in it.
                            continue;
                        }
                    }

                    // Backward-compatible behavior if no scope exists for the role.
                    if ((int) $membership->company_id === (int) $company->id) {
                        return true;
                    }
                }
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    /**
     * True if the user holds the 'admin' role in ANY company.
     * Admins get the same access as super admins across all companies,
     * except token/credentials which is enforced at the route level.
     */
    public function isCompanyAdmin(): bool
    {
        return $this->hasAnyRoleSlugOrName(['admin']);
    }

    /**
     * Check if the user has at least one role whose normalized slug/name
     * matches any value from the provided list.
     *
     * @param  array<int, string>  $normalizedCandidates
     */
    private function hasAnyRoleSlugOrName(array $normalizedCandidates): bool
    {
        if (
            ! Schema::hasTable('company_memberships')
            || ! Schema::hasTable('company_membership_roles')
            || ! Schema::hasTable('roles')
        ) {
            return false;
        }

        $candidates = collect($normalizedCandidates)
            ->map(fn (mixed $value) => strtolower(trim((string) $value)))
            ->filter(fn (string $value) => $value !== '')
            ->values();

        if ($candidates->isEmpty()) {
            return false;
        }

        try {
            $hasRoleSlugColumn = Schema::hasColumn('roles', 'slug');
            $hasRoleNameColumn = Schema::hasColumn('roles', 'name');

            if (! $hasRoleSlugColumn && ! $hasRoleNameColumn) {
                return false;
            }

            return CompanyMembership::query()
                ->where('user_id', $this->id)
                ->whereHas('roles', function ($query) use ($hasRoleSlugColumn, $hasRoleNameColumn, $candidates) {
                    $query->where(function ($inner) use ($hasRoleSlugColumn, $hasRoleNameColumn, $candidates) {
                        foreach ($candidates as $candidate) {
                            if ($hasRoleSlugColumn) {
                                $inner->orWhereRaw('LOWER(slug) = ?', [$candidate]);
                            }
                            if ($hasRoleNameColumn) {
                                $inner->orWhereRaw('LOWER(name) = ?', [$candidate]);
                            }
                        }
                    });
                })
                ->exists();
        } catch (\Throwable) {
            return false;
        }
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
