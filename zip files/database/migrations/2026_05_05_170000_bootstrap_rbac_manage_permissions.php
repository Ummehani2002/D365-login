<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles') || ! Schema::hasTable('permission_role')) {
            return;
        }

        $now = now();

        $corePermissions = [
            ['slug' => 'users.manage', 'name' => 'Manage users'],
            ['slug' => 'roles.manage', 'name' => 'Manage roles'],
            ['slug' => 'permissions.manage', 'name' => 'Manage permissions'],
            ['slug' => 'menu_match.manage', 'name' => 'Manage menu match'],
        ];

        $permissionIds = [];
        foreach ($corePermissions as $permission) {
            $slug = strtolower(trim((string) $permission['slug']));
            if ($slug === '') {
                continue;
            }

            $existingId = DB::table('permissions')
                ->whereRaw('LOWER(slug) = ?', [$slug])
                ->value('id');

            if ($existingId) {
                $permissionIds[] = (int) $existingId;
                continue;
            }

            DB::table('permissions')->insert([
                'slug' => $slug,
                'name' => (string) $permission['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $permissionIds[] = (int) DB::getPdo()->lastInsertId();
        }

        if ($permissionIds === []) {
            return;
        }

        $adminRoleId = DB::table('roles')
            ->whereRaw('LOWER(name) = ?', ['admin'])
            ->value('id');

        if (! $adminRoleId) {
            return;
        }

        $hasTimestamps = Schema::hasColumn('permission_role', 'created_at') && Schema::hasColumn('permission_role', 'updated_at');

        foreach ($permissionIds as $permissionId) {
            $exists = DB::table('permission_role')
                ->where('role_id', (int) $adminRoleId)
                ->where('permission_id', (int) $permissionId)
                ->exists();

            if ($exists) {
                continue;
            }

            $row = [
                'role_id' => (int) $adminRoleId,
                'permission_id' => (int) $permissionId,
            ];

            if ($hasTimestamps) {
                $row['created_at'] = $now;
                $row['updated_at'] = $now;
            }

            DB::table('permission_role')->insert($row);
        }
    }

    public function down(): void
    {
        // Intentional no-op: bootstrap migration should not remove permissions from active systems.
    }
};

