<?php

namespace App\Services\Rbac;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PermissionService
{
    /**
     * @return Collection<int, Permission>
     */
    public function listPermissions(): Collection
    {
        return Permission::query()
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);
    }

    public function createPermission(array $payload): Permission
    {
        return Permission::query()->create([
            'slug' => $payload['slug'],
            'name' => $payload['name'],
        ]);
    }

    public function updatePermission(Permission $permission, array $payload): Permission
    {
        $permission->update([
            'name' => $payload['name'],
        ]);

        return $permission->fresh(['roles', 'menuMatches']) ?? $permission;
    }

    public function deletePermission(Permission $permission): void
    {
        if ($permission->roles()->exists()) {
            throw ValidationException::withMessages([
                'permission' => ['This permission is assigned to one or more roles and cannot be deleted.'],
            ]);
        }

        if ($permission->menuMatches()->exists()) {
            throw ValidationException::withMessages([
                'permission' => ['This permission is mapped to a menu item and cannot be deleted.'],
            ]);
        }

        DB::transaction(function () use ($permission): void {
            $permission->delete();
        });
    }
}

