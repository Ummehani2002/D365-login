<?php

namespace App\Http\Controllers\Settings\Rbac;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rbac\AssignMenuPermissionMatchRequest;
use App\Http\Requests\Rbac\SaveMenuPermissionMatchRequest;
use App\Models\MenuPermissionMatch;
use App\Services\Rbac\MenuAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RbacMenuMatchController extends Controller
{
    public function __construct(private readonly MenuAccessService $menuAccessService) {}

    public function index(): View
    {
        return view('settings.rbac.menu-match');
    }

    public function listMappings(): JsonResponse
    {
        if (!DB::getSchemaBuilder()->hasTable('menu_permission_matches')) {
            return response()->json(['menu_items' => [], 'permissions' => [], 'available_menus' => []]);
        }

        $menuItems = $this->menuAccessService
            ->listMappingsWithPermissions()
            ->map(fn (MenuPermissionMatch $row) => [
                'id'            => $row->id,
                'menu_key'      => $row->menu_key,
                'menu_label'    => $row->menu_label,
                'route_name'    => $row->route_name,
                'permission_id' => $row->permission_id,
                'permission'    => $row->permission ? ['id' => $row->permission->id, 'slug' => $row->permission->slug, 'name' => $row->permission->name] : null,
            ])
            ->values()
            ->all();

        return response()->json([
            'menu_items'     => $menuItems,
            'permissions'    => $this->menuAccessService->listPermissions(),
            'available_menus'=> $this->menuAccessService->listAvailableMenus(),
        ]);
    }

    public function updateMappings(SaveMenuPermissionMatchRequest $request): JsonResponse
    {
        $this->menuAccessService->saveMappings($request->validated()['mappings']);
        return response()->json(['status' => true]);
    }

    public function listAvailableMenus(): JsonResponse
    {
        return response()->json(['menus' => $this->menuAccessService->listAvailableMenus()]);
    }

    public function assignMapping(AssignMenuPermissionMatchRequest $request): JsonResponse
    {
        try {
            $match = $this->menuAccessService->saveMappingByMenuKey($request->validated());
        } catch (ValidationException $exception) {
            return response()->json(['message' => $this->firstValidationError($exception) ?? 'Could not save mapping.', 'errors' => $exception->errors()], 422);
        }

        $match->loadMissing('permission:id,slug,name');

        return response()->json([
            'mapping' => [
                'id'            => $match->id,
                'menu_key'      => $match->menu_key,
                'menu_label'    => $match->menu_label,
                'route_name'    => $match->route_name,
                'permission_id' => $match->permission_id,
                'permission'    => $match->permission ? ['id' => $match->permission->id, 'slug' => $match->permission->slug, 'name' => $match->permission->name] : null,
            ],
        ]);
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
