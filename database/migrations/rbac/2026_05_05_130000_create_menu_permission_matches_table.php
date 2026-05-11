<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menu_permission_matches')) {
            return;
        }

        Schema::create('menu_permission_matches', function (Blueprint $table) {
            $table->id();
            $table->string('menu_key', 191)->unique();
            $table->string('menu_label');
            $table->string('route_name', 191)->nullable();
            $table->foreignId('permission_id')->nullable()->constrained('permissions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_permission_matches');
    }
};

