<?php

namespace App\Http\Controllers\Settings\Rbac;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rbac\StoreRoleRequest;
use App\Http\Requests\Rbac\UpdateRoleRequest;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Rbac\PermissionService;
use App\Services\Rbac\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RbacRoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly PermissionService $permissionService
    ) {}

    public function index(): View
    {
        return view('settings.rbac.roles');
    }

    public function listRoles(Request $request): JsonResponse
    {
        if (!DB::getSchemaBuilder()->hasTable('roles')) {
            return response()->json(['roles' => []]);
        }

        $companyId = $this->resolveCompanyId($request);

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
        if (!DB::getSchemaBuilder()->hasTable('permissions')) {
            return response()->json(['permissions' => []]);
        }

        return response()->json([
            'permissions' => $this->permissionService->listPermissions(),
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            $role = $this->roleService->createRole($request->validated(), $this->resolveCompanyId($request));
        } catch (ValidationException $exception) {
            return response()->json(['message' => $this->firstValidationError($exception) ?? 'Could not save role.', 'errors' => $exception->errors()], 422);
        }

        return response()->json(['role' => $this->rolePayload($role)], 201);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        try {
            $role = $this->roleService->updateRole($role, $request->validated(), $this->resolveCompanyId($request));
        } catch (ValidationException $exception) {
            return response()->json(['message' => $this->firstValidationError($exception) ?? 'Could not update role.', 'errors' => $exception->errors()], 422);
        }

        return response()->json(['role' => $this->rolePayload($role)]);
    }

    public function destroy(Role $role): JsonResponse
    {
        try {
            $this->roleService->deleteRole($role);
        } catch (ValidationException $exception) {
            return response()->json(['message' => $this->firstValidationError($exception) ?? 'Role cannot be deleted.', 'errors' => $exception->errors()], 422);
        }

        return response()->json(['status' => true]);
    }

    private function rolePayload(Role $r): array
    {
        $r->loadMissing(['permissions:id,slug,name']);

        return [
            'id'               => $r->id,
            'name'             => $r->name,
            'permission_count' => $r->permissions->count(),
            'permissions'      => $r->permissions->map(fn (Permission $p) => ['id' => $p->id, 'slug' => $p->slug, 'name' => $p->name])->values()->all(),
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

    private function resolveCompanyId(Request $request): ?int
    {
        if (!Schema::hasColumn('roles', 'company_id')) {
            return null;
        }

        $raw = (string) $request->query('company', '');
        if ($raw === '') return null;

        if (ctype_digit($raw)) {
            return Company::query()->whereKey((int) $raw)->value('id');
        }

        return Company::query()
            ->whereRaw('UPPER(COALESCE(d365_id, "")) = ?', [strtoupper($raw)])
            ->value('id');
    }
}
