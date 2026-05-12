<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grn_request_id_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('company', 20)->index();
            $table->string('request_id')->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_request_id_reservations');
    }
};
