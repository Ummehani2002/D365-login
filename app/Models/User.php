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

        return $email !== '' && in_array($email, $allowList, true);
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

        if ($this->isSuperAdmin()) {
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
        $membership = $this->membershipForCompany($company);
        if (! $membership) {
            return false;
        }

        return $membership->hasPermission($permissionSlug);
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
