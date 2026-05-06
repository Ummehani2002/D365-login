<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rbac\StoreRoleRequest;
use App\Http\Requests\Rbac\UpdateRoleRequest;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Rbac\PermissionService;
use App\Services\Rbac\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RbacRoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly PermissionService $permissionService
    ) {
    }

    public function index(): View
    {
        return view('settings.rbac.roles');
    }

    public function listRoles(): JsonResponse
    {
        if (! DB::getSchemaBuilder()->hasTable('roles')) {
            return response()->json(['roles' => []]);
        }

        $companyId = $this->resolveCompanyId();

        return response()->json([
            'roles' => $this->roleService
                ->listRolesWithPermissions($companyId)
                ->map(fn (Role $role) => $this->rolePayload($role))
                ->values()
                ->all(),
        ]);
    }

    public function listPermissions(): JsonResponse
    {
        if (! DB::getSchemaBuilder()->hasTable('permissions')) {
            return response()->json(['permissions' => []]);
        }

        return response()->json([
            'permissions' => $this->permissionService->listPermissions(),
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId();
        if (! $companyId) {
            return response()->json(['message' => 'Select a valid company to create role.'], 422);
        }
        $role = $this->roleService->createRole($request->validated(), $companyId);

        return response()->json([
            'role' => $this->rolePayload($role),
        ], 201);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role = $this->roleService->updateRole($role, $request->validated());

        return response()->json([
            'role' => $this->rolePayload($role),
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        try {
            $this->roleService->deleteRole($role);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => $this->firstValidationError($exception) ?? 'Role cannot be deleted.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json(['status' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rolePayload(Role $r): array
    {
        $r->loadMissing(['permissions:id,slug,name']);

        return [
            'id' => $r->id,
            'name' => $r->name,
            'permission_count' => $r->permissions->count(),
            'permissions' => $r->permissions->map(fn (Permission $p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'name' => $p->name,
            ])->values()->all(),
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

    private function resolveCompanyId(): ?int
    {
        $companyCode = request()->query('company');
        $company = Company::resolveFromMixed($companyCode);
        if (! $company) {
            $company = Company::query()->whereNotNull('d365_id')->orderBy('name')->first();
        }
        return $company?->id;
    }
}
