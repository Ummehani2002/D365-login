<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('company_memberships')) {
            return;
        }

        Schema::create('company_membership_role_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_membership_id')->constrained('company_memberships')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->boolean('all_organizations')->default(false);
            $table->timestamps();

            $table->unique(['company_membership_id', 'role_id'], 'cms_role_scope_unique');
        });

        Schema::create('company_membership_role_scope_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_membership_role_scope_id')
                ->constrained('company_membership_role_scopes')
                ->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_membership_role_scope_id', 'company_id'], 'cms_role_scope_company_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_membership_role_scope_companies');
        Schema::dropIfExists('company_membership_role_scopes');
    }
};

