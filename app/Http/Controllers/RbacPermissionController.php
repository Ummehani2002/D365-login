<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rbac\StorePermissionRequest;
use App\Http\Requests\Rbac\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\Rbac\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RbacPermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {
    }

    public function index(): View
    {
        return view('settings.rbac.permissions');
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

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->permissionService->createPermission($request->validated());
        return response()->json(['permission' => $permission], 201);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission = $this->permissionService->updatePermission($permission, $request->validated());
        return response()->json(['permission' => $permission]);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        try {
            $this->permissionService->deletePermission($permission);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => $this->firstValidationError($exception) ?? 'Permission cannot be deleted.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json(['status' => true]);
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

