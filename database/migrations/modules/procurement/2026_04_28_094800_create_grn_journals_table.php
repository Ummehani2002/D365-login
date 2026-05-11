<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grn_journals', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->nullable()->index();
            $table->string('company', 20)->nullable()->index();
            $table->string('purch_id')->nullable()->index();
            $table->string('project_id')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('packing_slip_id')->nullable()->index();
            $table->date('document_date')->nullable();
            $table->json('lines')->nullable();
            $table->json('d365_response')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_journals');
    }
};
