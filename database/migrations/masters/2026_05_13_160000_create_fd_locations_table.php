<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fd_locations', function (Blueprint $table) {
            $table->id();
            $table->string('company_id', 100);
            $table->string('fd_location_id', 100);
            $table->string('description', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'fd_location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fd_locations');
    }
};
