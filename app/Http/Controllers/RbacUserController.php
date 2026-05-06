<?php

namespace App\Http\Controllers;

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
    ) {
    }

    public function index(): View
    {
        return view('settings.rbac.users');
    }

    public function listMemberships(): JsonResponse
    {
        if (! DB::getSchemaBuilder()->hasTable('company_memberships')) {
            return response()->json(['memberships' => []]);
        }

        return response()->json([
            'memberships' => $this->membershipService
                ->listMemberships()
                ->map(fn (CompanyMembership $membership) => $this->membershipPayload($membership))
                ->values()
                ->all(),
        ]);
    }

    public function listCompanies(): JsonResponse
    {
        if (! DB::getSchemaBuilder()->hasTable('companies')) {
            return response()->json(['companies' => []]);
        }

        return response()->json(['companies' => $this->membershipService->listCompanies()]);
    }

    public function rolesForCompany(): JsonResponse
    {
        if (! DB::getSchemaBuilder()->hasTable('roles')) {
            return response()->json(['roles' => []]);
        }

        return response()->json(['roles' => $this->membershipService->listRoles()]);
    }

    public function roleScopes(CompanyMembership $membership): JsonResponse
    {
        return response()->json([
            'membership_id' => $membership->id,
            'scopes' => $this->roleScopeService->listScopesForMembership($membership),
        ]);
    }

    public function upsertRoleScope(UpsertRoleScopeRequest $request, CompanyMembership $membership): JsonResponse
    {
        try {
            $scope = $this->roleScopeService->upsertRoleScope($membership, $request->validated());
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => $this->firstValidationError($exception) ?? 'Could not save role scope.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json(['scope' => $this->scopePayload($scope)]);
    }

    public function storeMembership(StoreMembershipRequest $request): JsonResponse
    {
        try {
            $membership = $this->membershipService->createMembership($request->validated());
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => $this->firstValidationError($exception) ?? 'Could not save membership.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json(['membership' => $this->membershipPayload($membership)], 201);
    }

    public function updateMembership(UpdateMembershipRequest $request, CompanyMembership $membership): JsonResponse
    {
        try {
            $updatedMembership = $this->membershipService->updateMembership($membership, $request->validated());
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => $this->firstValidationError($exception) ?? 'Could not update membership.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json(['membership' => $this->membershipPayload($updatedMembership)]);
    }

    public function destroyMembership(CompanyMembership $membership): JsonResponse
    {
        $this->membershipService->deleteMembership($membership);
        return response()->json(['status' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function membershipPayload(CompanyMembership $membership): array
    {
        $membership->loadMissing(['user', 'company', 'roles']);
        $user = $membership->user;

        return [
            'id' => $membership->id,
            'user_id' => $user->id,
            'user_code' => $user->user_code ?? '',
            'name' => $user->name,
            'email' => $user->email,
            'provider' => $user->provider ?? null,
            'company_id' => $membership->company_id,
            'company_code' => strtoupper((string) ($membership->company->d365_id ?? '')),
            'person' => strtoupper(trim($user->name)),
            'roles' => $membership->roles->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function scopePayload(CompanyMembershipRoleScope $scope): array
    {
        return [
            'id' => $scope->id,
            'role_id' => $scope->role_id,
            'all_organizations' => (bool) $scope->all_organizations,
            'company_ids' => $scope->companies->pluck('id')->map(fn (mixed $id) => (int) $id)->values()->all(),
        ];
    }

    private function firstValidationError(ValidationException $exception): ?string
    {
        foreach ($exception->errors() as $messages) {
            if (is_array($messages) && isset($messages[0])) {
                return (string) $messages[0];
            }
        }

        return null;
    }
}

