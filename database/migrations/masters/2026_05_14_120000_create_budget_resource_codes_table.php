<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_resource_codes', function (Blueprint $table) {
            $table->id();
            $table->string('company_id', 100);
            $table->string('resource_code', 100);
            $table->string('description', 255)->nullable();
            $table->string('unit', 30)->nullable();
            $table->string('resource_category', 50)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'resource_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_resource_codes');
    }
};
