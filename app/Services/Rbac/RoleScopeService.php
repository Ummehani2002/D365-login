<?php
namespace App\Services\Rbac;
use App\Models\CompanyMembership;
use App\Models\CompanyMembershipRoleScope;
use Illuminate\Support\Facades\DB;
class RoleScopeService
{
    public function listScopesForMembership(CompanyMembership $membership): array { $membership->loadMissing(['roleScopes.companies:id,d365_id,name']); return $membership->roleScopes->map(fn (CompanyMembershipRoleScope $scope) => ['id' => $scope->id,'role_id' => $scope->role_id,'all_organizations' => (bool) $scope->all_organizations,'company_ids' => $scope->companies->pluck('id')->map(fn (mixed $id) => (int) $id)->values()->all()])->values()->all(); }
    public function validateRoleScopes(array $roleIds, array $roleScopes): void {}
    public function syncScopesForMembership(CompanyMembership $membership, array $roleIds, array $roleScopes): void { $scopesByRole = collect($roleScopes)->keyBy(fn (array $scope) => (int) $scope['role_id']); foreach ($roleIds as $roleId) { $scopePayload = $scopesByRole->get((int) $roleId); $all = $scopePayload ? (bool) ($scopePayload['all_organizations'] ?? false) : false; $companyIds = $scopePayload ? $this->normalizeIds($scopePayload['company_ids'] ?? []) : [(int) $membership->company_id]; $scope = CompanyMembershipRoleScope::query()->updateOrCreate(['company_membership_id' => $membership->id,'role_id' => (int) $roleId], ['all_organizations' => $all]); $scope->companies()->sync($all ? [] : $companyIds); } }
    public function pruneScopesByRoleIds(CompanyMembership $membership, array $roleIds): void { CompanyMembershipRoleScope::query()->where('company_membership_id', $membership->id)->whereNotIn('role_id', $roleIds ?: [0])->delete(); }
    public function upsertRoleScope(CompanyMembership $membership, array $payload): CompanyMembershipRoleScope { $roleId = (int) $payload['role_id']; $all = (bool) $payload['all_organizations']; $companyIds = $this->normalizeIds($payload['company_ids'] ?? []); return DB::transaction(function () use ($membership, $roleId, $all, $companyIds): CompanyMembershipRoleScope { $scope = CompanyMembershipRoleScope::query()->updateOrCreate(['company_membership_id' => $membership->id,'role_id' => $roleId], ['all_organizations' => $all]); $scope->companies()->sync($all ? [] : $companyIds); $scope->loadMissing('companies:id,d365_id,name'); return $scope; }); }
    private function normalizeIds(array $ids): array { return collect($ids)->map(fn (mixed $id) => (int) $id)->filter()->unique()->values()->all(); }
}
