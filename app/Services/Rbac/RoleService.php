<?php

namespace App\Services\Rbac;

use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoleService
{
    /**
     * @return Collection<int, Role>
     */
    public function listRolesWithPermissions(?int $companyId = null): Collection
    {
        return Role::query()
            ->with(['permissions:id,slug,name'])
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createRole(array $payload, int $companyId): Role
    {
        return DB::transaction(function () use ($payload, $companyId): Role {
            $slugBase = Str::slug((string) $payload['name']) ?: 'role';
            $slug = $slugBase;
            $i = 1;
            while (Role::query()->where('company_id', $companyId)->where('slug', $slug)->exists()) {
                $i++;
                $slug = $slugBase.'-'.$i;
            }

            $role = Role::query()->create([
                'company_id' => $companyId,
                'name' => $payload['name'],
                'slug' => $slug,
            ]);

            $permissionIds = $this->normalizeIds($payload['permission_ids'] ?? []);
            $role->permissions()->sync($permissionIds);

            $role->load(['permissions:id,slug,name']);

            return $role;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateRole(Role $role, array $payload): Role
    {
        return DB::transaction(function () use ($role, $payload): Role {
            $role->update([
                'name' => $payload['name'],
            ]);

            $permissionIds = $this->normalizeIds($payload['permission_ids'] ?? []);
            $role->permissions()->sync($permissionIds);

            $role->load(['permissions:id,slug,name']);

            return $role;
        });
    }

    public function deleteRole(Role $role): void
    {
        if ($role->companyMemberships()->exists()) {
            throw ValidationException::withMessages([
                'role' => ['This role is assigned to one or more user memberships and cannot be deleted.'],
            ]);
        }

        DB::transaction(function () use ($role): void {
            $role->permissions()->sync([]);
            $role->delete();
        });
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    private function normalizeIds(array $ids): array
    {
        return collect($ids)
            ->map(fn (mixed $id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}

