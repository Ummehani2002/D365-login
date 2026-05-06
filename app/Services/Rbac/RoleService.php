<?php
namespace App\Services\Rbac;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class RoleService
{
    public function listRolesWithPermissions(?int $companyId = null): Collection
    {
        $q = Role::query()->with(['permissions:id,slug,name'])->orderBy('name');
        if ($companyId && Schema::hasColumn('roles', 'company_id')) {
            $q->where('company_id', $companyId);
        }
        return $q->get();
    }
    public function createRole(array $payload, ?int $companyId = null): Role
    {
        return DB::transaction(function () use ($payload, $companyId): Role {
            $attrs = ['name' => $payload['name']];
            if (Schema::hasColumn('roles', 'company_id')) {
                $resolvedCompanyId = $this->resolveCompanyIdForWrite($companyId);
                $attrs['company_id'] = $resolvedCompanyId;
            }
            if (Schema::hasColumn('roles', 'slug')) {
                $scopeCompanyId = $attrs['company_id'] ?? null;
                $attrs['slug'] = $this->nextSlug((string) $payload['name'], $scopeCompanyId);
            }
            $role = Role::query()->create($attrs);
            $role->permissions()->sync($this->normalizeIds($payload['permission_ids'] ?? []));
            $role->load(['permissions:id,slug,name']);
            return $role;
        });
    }
    public function updateRole(Role $role, array $payload, ?int $companyId = null): Role
    {
        return DB::transaction(function () use ($role, $payload, $companyId): Role {
            $attrs = ['name' => $payload['name']];
            if (Schema::hasColumn('roles', 'company_id') && $companyId) {
                $attrs['company_id'] = $companyId;
            }
            if (Schema::hasColumn('roles', 'slug')) {
                $scopeCompanyId = $attrs['company_id'] ?? $role->company_id ?? null;
                $attrs['slug'] = $this->nextSlug((string) $payload['name'], $scopeCompanyId, $role->id);
            }
            $role->update($attrs);
            $role->permissions()->sync($this->normalizeIds($payload['permission_ids'] ?? []));
            $role->load(['permissions:id,slug,name']);
            return $role;
        });
    }
    public function deleteRole(Role $role): void { if ($role->companyMemberships()->exists()) throw ValidationException::withMessages(['role' => ['This role is assigned to one or more user memberships and cannot be deleted.']]); DB::transaction(function () use ($role): void { $role->permissions()->sync([]); $role->delete(); }); }
    private function resolveCompanyIdForWrite(?int $companyId): int
    {
        if ($companyId) {
            return $companyId;
        }
        $fallback = Company::query()->value('id');
        if ($fallback) {
            return (int) $fallback;
        }
        throw ValidationException::withMessages([
            'company' => ['Select a company before creating a role.'],
        ]);
    }
    private function nextSlug(string $name, ?int $companyId = null, ?int $exceptRoleId = null): string
    {
        $base = Str::of($name)->lower()->slug('_')->limit(64, '')->value();
        $base = $base !== '' ? $base : 'role';
        $slug = $base;
        $i = 2;
        while ($this->slugExists($slug, $companyId, $exceptRoleId)) {
            $suffix = '_' . $i;
            $slug = Str::limit($base, 64 - strlen($suffix), '') . $suffix;
            $i++;
        }
        return $slug;
    }
    private function slugExists(string $slug, ?int $companyId = null, ?int $exceptRoleId = null): bool
    {
        $q = Role::query()->where('slug', $slug);
        if (Schema::hasColumn('roles', 'company_id') && $companyId) {
            $q->where('company_id', $companyId);
        }
        if ($exceptRoleId) {
            $q->where('id', '!=', $exceptRoleId);
        }
        return $q->exists();
    }
    private function normalizeIds(array $ids): array { return collect($ids)->map(fn (mixed $id) => (int) $id)->filter(fn (int $id) => $id > 0)->unique()->values()->all(); }
}
