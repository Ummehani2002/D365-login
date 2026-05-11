<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('company_memberships')) {
            return;
        }

        Schema::create('company_membership_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_membership_id')->constrained('company_memberships')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_membership_id', 'role_id']);
        });

        if (Schema::hasColumn('company_memberships', 'role_id')) {
            $now = now();
            $rows = DB::table('company_memberships')
                ->select(['id', 'role_id'])
                ->whereNotNull('role_id')
                ->get();

            foreach ($rows as $row) {
                DB::table('company_membership_roles')->insert([
                    'company_membership_id' => $row->id,
                    'role_id' => $row->role_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            Schema::table('company_memberships', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('company_memberships')) {
            return;
        }

        if (! Schema::hasColumn('company_memberships', 'role_id') && Schema::hasTable('company_membership_roles')) {
            Schema::table('company_memberships', function (Blueprint $table) {
                $table->foreignId('role_id')->nullable()->after('company_id')->constrained('roles')->cascadeOnDelete();
            });

            $pivotRows = DB::table('company_membership_roles')
                ->orderBy('id')
                ->get()
                ->groupBy('company_membership_id');

            foreach ($pivotRows as $membershipId => $roles) {
                $first = $roles->first();
                if ($first) {
                    DB::table('company_memberships')
                        ->where('id', $membershipId)
                        ->update(['role_id' => $first->role_id]);
                }
            }

            Schema::dropIfExists('company_membership_roles');
        }
    }
};
