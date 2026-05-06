<?php

namespace App\Services\Rbac;

use App\Models\Company;
use App\Models\MenuPermissionMatch;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MenuAccessService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function configuredModules(): array
    {
        $modules = config('rbac_menu_match.modules', []);

        return is_array($modules) ? $modules : [];
    }

    public function syncConfiguredModules(): void
    {
        if (! Schema::hasTable('menu_permission_matches')) {
            return;
        }

        foreach ($this->normalizedConfiguredModules() as $module) {
            $menuKey = $module['key'];

            MenuPermissionMatch::query()->updateOrCreate(
                ['menu_key' => $menuKey],
                [
                    'menu_label' => $module['label'],
                    'route_name' => $module['route_name'] ?? null,
                ]
            );
        }
    }

    /**
     * @return Collection<int, MenuPermissionMatch>
     */
    public function listMappingsWithPermissions(): Collection
    {
        if (! Schema::hasTable('menu_permission_matches')) {
            return new Collection();
        }

        $configuredKeys = collect($this->normalizedConfiguredModules())
            ->pluck('key')
            ->all();

        if ($configuredKeys === []) {
            return new Collection();
        }

        $this->syncConfiguredModules();

        return MenuPermissionMatch::query()
            ->with('permission:id,slug,name')
            ->whereIn('menu_key', $configuredKeys)
            ->orderBy('menu_label')
            ->get(['id', 'menu_key', 'menu_label', 'route_name', 'permission_id']);
    }

    /**
     * @return Collection<int, Permission>
     */
    public function listPermissions(): Collection
    {
        if (! Schema::hasTable('permissions')) {
            return new Collection();
        }

        return Permission::query()
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $mappings
     */
    public function saveMappings(array $mappings): void
    {
        if (! Schema::hasTable('menu_permission_matches')) {
            return;
        }

        DB::transaction(function () use ($mappings): void {
            foreach ($mappings as $mapping) {
                MenuPermissionMatch::query()
                    ->whereKey((int) $mapping['id'])
                    ->update([
                        'permission_id' => array_key_exists('permission_id', $mapping)
                            ? $mapping['permission_id']
                            : null,
                    ]);
            }
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAvailableMenus(): array
    {
        $configured = collect($this->normalizedConfiguredModules());
        if ($configured->isEmpty()) {
            return [];
        }

        if (! Schema::hasTable('menu_permission_matches')) {
            return $configured
                ->map(fn (array $module) => [
                    'key' => $module['key'],
                    'label' => $module['label'],
                    'route_name' => $module['route_name'],
                    'mapping_id' => null,
                    'permission_id' => null,
                    'mapped' => false,
                ])
                ->values()
                ->all();
        }

        $byKey = MenuPermissionMatch::query()
            ->whereIn('menu_key', $configured->pluck('key')->all())
            ->get(['id', 'menu_key', 'permission_id'])
            ->keyBy('menu_key');

        return $configured
            ->map(function (array $module) use ($byKey): array {
                /** @var MenuPermissionMatch|null $match */
                $match = $byKey->get($module['key']);
                $permissionId = $match?->permission_id !== null ? (int) $match->permission_id : null;

                return [
                    'key' => $module['key'],
                    'label' => $module['label'],
                    'route_name' => $module['route_name'],
                    'mapping_id' => $match?->id,
                    'permission_id' => $permissionId,
                    'mapped' => $permissionId !== null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveMappingByMenuKey(array $payload): MenuPermissionMatch
    {
        if (! Schema::hasTable('menu_permission_matches')) {
            throw ValidationException::withMessages([
                'menu_key' => ['Menu item mapping table is missing.'],
            ]);
        }

        $menuKey = trim((string) ($payload['menu_key'] ?? ''));
        $module = $this->configuredModuleByKey($menuKey);
        if (! $module) {
            throw ValidationException::withMessages([
                'menu_key' => ['Invalid menu item.'],
            ]);
        }

        return DB::transaction(function () use ($module, $payload): MenuPermissionMatch {
            return MenuPermissionMatch::query()->updateOrCreate(
                ['menu_key' => $module['key']],
                [
                    'menu_label' => $module['label'],
                    'route_name' => $module['route_name'] ?? null,
                    'permission_id' => array_key_exists('permission_id', $payload)
                        ? $payload['permission_id']
                        : null,
                ]
            );
        });
    }

    public function canUserAccessMenuKey(?User $user, ?Company $company, string $menuKey): bool
    {
        return $this->canUserAccessPermissionSlug($user, $company, $this->permissionSlugForMenuKey($menuKey));
    }

    /**
     * @return array<string, bool>
     */
    public function menuVisibilityForUser(?User $user, ?Company $company): array
    {
        $visibility = [];
        $permissionByMenuKey = $this->permissionSlugByMenuKey();

        foreach ($this->configuredModules() as $module) {
            $key = trim((string) ($module['key'] ?? ''));
            if ($key === '') {
                continue;
            }
            $permissionSlug = $permissionByMenuKey[$key] ?? null;
            $visibility[$key] = $this->canUserAccessPermissionSlug($user, $company, $permissionSlug);
        }

        return $visibility;
    }

    public function resolveCompanyFromCode(?string $companyCode): ?Company
    {
        $candidate = strtoupper(trim((string) $companyCode));
        if ($candidate === '') {
            return null;
        }

        return Company::query()
            ->whereRaw('UPPER(d365_id) = ?', [$candidate])
            ->first();
    }

    public function permissionSlugForMenuKey(string $menuKey): ?string
    {
        return $this->permissionSlugByMenuKey()[$menuKey] ?? null;
    }

    public function assertMappingExists(string $menuKey): void
    {
        if (! Schema::hasTable('menu_permission_matches')) {
            throw ValidationException::withMessages([
                'menu_key' => ['Menu item mapping table is missing.'],
            ]);
        }

        $exists = MenuPermissionMatch::query()
            ->where('menu_key', $menuKey)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'menu_key' => ['Menu item mapping is missing.'],
            ]);
        }
    }

    private function canUserAccessPermissionSlug(?User $user, ?Company $company, ?string $permissionSlug): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        if (! $company || $permissionSlug === null || $permissionSlug === '') {
            return false;
        }

        return $user->hasPermissionForCompany($company, $permissionSlug);
    }

    /**
     * @return array<string, string>
     */
    private function permissionSlugByMenuKey(): array
    {
        if (! Schema::hasTable('menu_permission_matches')) {
            return [];
        }

        return MenuPermissionMatch::query()
            ->leftJoin('permissions', 'permissions.id', '=', 'menu_permission_matches.permission_id')
            ->orderBy('menu_permission_matches.menu_key')
            ->get(['menu_permission_matches.menu_key', 'permissions.slug'])
            ->reduce(function (array $carry, MenuPermissionMatch $row): array {
                $slug = $row->getAttribute('slug');
                if (is_string($slug) && $slug !== '') {
                    $carry[(string) $row->menu_key] = $slug;
                }

                return $carry;
            }, []);
    }

    /**
     * @return array<int, array{key: string, label: string, route_name: ?string}>
     */
    private function normalizedConfiguredModules(): array
    {
        return collect($this->configuredModules())
            ->map(function (mixed $module): ?array {
                if (! is_array($module)) {
                    return null;
                }

                $key = trim((string) ($module['key'] ?? ''));
                if ($key === '') {
                    return null;
                }

                return [
                    'key' => $key,
                    'label' => trim((string) ($module['label'] ?? $key)) ?: $key,
                    'route_name' => isset($module['route_name']) ? (string) $module['route_name'] : null,
                ];
            })
            ->filter(fn (?array $module) => $module !== null)
            ->unique(fn (array $module) => $module['key'])
            ->sortBy(fn (array $module) => strtolower($module['label']))
            ->values()
            ->all();
    }

    /**
     * @return array{key: string, label: string, route_name: ?string}|null
     */
    private function configuredModuleByKey(string $menuKey): ?array
    {
        return collect($this->normalizedConfiguredModules())
            ->first(fn (array $module) => $module['key'] === $menuKey);
    }
}
