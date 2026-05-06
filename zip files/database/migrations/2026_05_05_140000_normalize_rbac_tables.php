<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeRolesTable();
        $this->normalizePivotTimestamps();
    }

    public function down(): void
    {
        // Non-destructive normalization migration: no down actions.
    }

    private function normalizeRolesTable(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $rows = DB::table('roles')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get();

        $seen = [];
        foreach ($rows as $row) {
            $base = trim((string) $row->name);
            if ($base === '') {
                $base = 'Role '.$row->id;
            }

            $candidate = $base;
            while (isset($seen[strtolower($candidate)])) {
                $candidate = $base.' #'.$row->id;
            }
            $seen[strtolower($candidate)] = true;

            if ($candidate !== $row->name) {
                DB::table('roles')
                    ->where('id', $row->id)
                    ->update(['name' => $candidate]);
            }
        }

        try {
            Schema::table('roles', function (Blueprint $table): void {
                $table->unique('name', 'roles_name_unique');
            });
        } catch (\Throwable) {
            // Unique index may already exist in some environments.
        }
    }

    private function normalizePivotTimestamps(): void
    {
        $this->ensurePivotHasTimestamps('permission_role');
        $this->ensurePivotHasTimestamps('company_membership_roles');
        $this->ensureUniqueIndex('permission_role', ['role_id', 'permission_id'], 'permission_role_role_permission_unique');
        $this->ensureUniqueIndex('company_membership_roles', ['company_membership_id', 'role_id'], 'company_membership_roles_membership_role_unique');
    }

    private function ensurePivotHasTimestamps(string $tableName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $hasCreatedAt = Schema::hasColumn($tableName, 'created_at');
        $hasUpdatedAt = Schema::hasColumn($tableName, 'updated_at');

        if (! $hasCreatedAt || ! $hasUpdatedAt) {
            Schema::table($tableName, function (Blueprint $table) use ($hasCreatedAt, $hasUpdatedAt): void {
                if (! $hasCreatedAt) {
                    $table->timestamp('created_at')->nullable();
                }
                if (! $hasUpdatedAt) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        $now = now();
        if (Schema::hasColumn($tableName, 'created_at')) {
            DB::table($tableName)->whereNull('created_at')->update(['created_at' => $now]);
        }
        if (Schema::hasColumn($tableName, 'updated_at')) {
            DB::table($tableName)->whereNull('updated_at')->update(['updated_at' => $now]);
        }
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function ensureUniqueIndex(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        try {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
                $table->unique($columns, $indexName);
            });
        } catch (\Throwable) {
            // Already exists or cannot be created in current state.
        }
    }
};
