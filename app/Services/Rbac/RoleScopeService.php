<?php

namespace App\Services\Rbac;

use App\Models\CompanyMembership;
use App\Models\CompanyMembershipRoleScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoleScopeService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listScopesForMembership(CompanyMembership $membership): array
    {
        $membership->loadMissing(['roleScopes.companies:id,d365_id,name']);

        return $membership->roleScopes
            ->map(fn (CompanyMembershipRoleScope $scope) => [
                'id' => $scope->id,
                'role_id' => $scope->role_id,
                'all_organizations' => (bool) $scope->all_organizations,
                'company_ids' => $scope->companies->pluck('id')->map(fn (mixed $id) => (int) $id)->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $roleIds
     * @param  array<int, array<string, mixed>>  $roleScopes
     */
    public function validateRoleScopes(array $roleIds, array $roleScopes): void
    {
        if ($roleScopes === []) {
            return;
        }

        $selectedRoleIds = collect($roleIds)->map(fn (mixed $id) => (int) $id)->filter()->unique()->values();
        $seenRoleIds = [];

        foreach ($roleScopes as $scope) {
            $roleId = (int) ($scope['role_id'] ?? 0);
            if (! $selectedRoleIds->contains($roleId)) {
                throw ValidationException::withMessages([
                    'role_scopes' => ['Organization scope can only be set for roles selected in Assign roles.'],
                ]);
            }
            if (in_array($roleId, $seenRoleIds, true)) {
                throw ValidationException::withMessages([
                    'role_scopes' => ['Duplicate organization scope found for the same role.'],
                ]);
            }
            $seenRoleIds[] = $roleId;
        }
    }

    /**
     * @param  array<int, int>  $roleIds
     * @param  array<int, array<string, mixed>>  $roleScopes
     */
    public function syncScopesForMembership(CompanyMembership $membership, array $roleIds, array $roleScopes): void
    {
        $scopesByRole = collect($roleScopes)->keyBy(fn (array $scope) => (int) $scope['role_id']);
        $defaultCompanyIds = $this->defaultScopeCompanyIds($membership);

        foreach ($roleIds as $roleId) {
            $scopePayload = $scopesByRole->get((int) $roleId);
            $allOrganizations = $scopePayload ? (bool) ($scopePayload['all_organizations'] ?? false) : false;
            $companyIds = $scopePayload
                ? $this->normalizeIds($scopePayload['company_ids'] ?? [])
                : $defaultCompanyIds;

            $scope = CompanyMembershipRoleScope::query()->updateOrCreate(
                ['company_membership_id' => $membership->id, 'role_id' => (int) $roleId],
                ['all_organizations' => $allOrganizations]
            );

            $scope->companies()->sync($allOrganizations ? [] : $companyIds);
        }
    }

    /**
     * @param  array<int, int>  $roleIds
     */
    public function pruneScopesByRoleIds(CompanyMembership $membership, array $roleIds): void
    {
        if ($roleIds === []) {
            CompanyMembershipRoleScope::query()
                ->where('company_membership_id', $membership->id)
                ->delete();
            return;
        }

        CompanyMembershipRoleScope::query()
            ->where('company_membership_id', $membership->id)
            ->whereNotIn('role_id', $roleIds)
            ->delete();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function upsertRoleScope(CompanyMembership $membership, array $payload): CompanyMembershipRoleScope
    {
        $roleId = (int) $payload['role_id'];
        $allOrganizations = (bool) $payload['all_organizations'];
        $companyIds = $this->normalizeIds($payload['company_ids'] ?? []);

        return DB::transaction(function () use ($membership, $roleId, $allOrganizations, $companyIds): CompanyMembershipRoleScope {
            $scope = CompanyMembershipRoleScope::query()->updateOrCreate(
                ['company_membership_id' => $membership->id, 'role_id' => $roleId],
                ['all_organizations' => $allOrganizations]
            );

            $scope->companies()->sync($allOrganizations ? [] : $companyIds);
            $scope->loadMissing('companies:id,d365_id,name');

            return $scope;
        });
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    private function normalizeIds(array $ids): array
    {
        return collect($ids)->map(fn (mixed $id) => (int) $id)->filter()->unique()->values()->all();
    }

    /**
     * @return array<int, int>
     */
    private function defaultScopeCompanyIds(CompanyMembership $membership): array
    {
        $companyId = (int) $membership->company_id;
        return $companyId > 0 ? [$companyId] : [];
    }
}

