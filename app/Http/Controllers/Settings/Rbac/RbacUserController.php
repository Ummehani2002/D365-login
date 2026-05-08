<?php

namespace App\Http\Controllers\Settings\Rbac;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rbac\StoreMembershipRequest;
use App\Http\Requests\Rbac\UpdateMembershipRequest;
use App\Http\Requests\Rbac\UpsertRoleScopeRequest;
use App\Models\CompanyMembership;
use App\Models\CompanyMembershipRoleScope;
use App\Models\Role;
use App\Services\Rbac\MembershipService;
use App\Services\Rbac\RoleScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RbacUserController extends Controller
{
    public function __construct(
        private readonly MembershipService $membershipService,
        private readonly RoleScopeService $roleScopeService
    ) {}

    public function index(): View
    {
        return view('settings.rbac.users');
    }

    public function listMemberships(): JsonResponse
    {
        if (!DB::getSchemaBuilder()->hasTable('company_memberships')) {
            return response()->json(['memberships' => []]);
        }

        return response()->json([
            'memberships' => $this->membershipService
                ->listMemberships()
                ->map(fn (CompanyMembership $m) => $this->membershipPayload($m))
                ->values()
                ->all(),
        ]);
    }

    public function listCompanies(): JsonResponse
    {
        return response()->json(['companies' => $this->membershipService->listCompanies()]);
    }

    public function rolesForCompany(): JsonResponse
    {
        return response()->json(['roles' => $this->membershipService->listRoles()]);
    }

    public function roleScopes(CompanyMembership $membership): JsonResponse
    {
        return response()->json([
            'membership_id' => $membership->id,
            'scopes'        => $this->roleScopeService->listScopesForMembership($membership),
        ]);
    }

    public function upsertRoleScope(UpsertRoleScopeRequest $request, CompanyMembership $membership): JsonResponse
    {
        try {
            $scope = $this->roleScopeService->upsertRoleScope($membership, $request->validated());
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Could not save role scope.', 'errors' => $e->errors()], 422);
        }

        return response()->json(['scope' => $this->scopePayload($scope)]);
    }

    public function storeMembership(StoreMembershipRequest $request): JsonResponse
    {
        try {
            $m = $this->membershipService->createMembership($request->validated());
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Could not save membership.', 'errors' => $e->errors()], 422);
        }

        return response()->json(['membership' => $this->membershipPayload($m)], 201);
    }

    public function updateMembership(UpdateMembershipRequest $request, CompanyMembership $membership): JsonResponse
    {
        try {
            $m = $this->membershipService->updateMembership($membership, $request->validated());
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Could not update membership.', 'errors' => $e->errors()], 422);
        }

        return response()->json(['membership' => $this->membershipPayload($m)]);
    }

    public function destroyMembership(CompanyMembership $membership): JsonResponse
    {
        $this->membershipService->deleteMembership($membership);
        return response()->json(['status' => true]);
    }

    private function membershipPayload(CompanyMembership $membership): array
    {
        $membership->loadMissing(['user', 'company', 'roles']);
        $u = $membership->user;

        return [
            'id'           => $membership->id,
            'user_id'      => $u->id,
            'user_code'    => $u->user_code ?? '',
            'name'         => $u->name,
            'email'        => $u->email,
            'provider'     => $u->provider ?? null,
            'company_id'   => $membership->company_id,
            'company_code' => strtoupper((string) ($membership->company->d365_id ?? '')),
            'person'       => strtoupper(trim($u->name)),
            'roles'        => $membership->roles->map(fn (Role $r) => ['id' => $r->id, 'name' => $r->name])->values()->all(),
        ];
    }

    private function scopePayload(CompanyMembershipRoleScope $scope): array
    {
        return [
            'id'               => $scope->id,
            'role_id'          => $scope->role_id,
            'all_organizations'=> (bool) $scope->all_organizations,
            'company_ids'      => $scope->companies->pluck('id')->map(fn (mixed $id) => (int) $id)->values()->all(),
        ];
    }
}
