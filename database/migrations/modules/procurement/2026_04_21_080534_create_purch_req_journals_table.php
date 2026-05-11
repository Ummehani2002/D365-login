<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purch_req_journals', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->nullable();
            $table->string('pr_no')->nullable();
            $table->string('company')->nullable();
            $table->date('pr_date')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('pool_id')->nullable();
            $table->string('contact_name')->nullable();
            $table->text('remarks')->nullable();
            $table->string('department')->nullable();
            $table->json('lines')->nullable();
            $table->json('attachments')->nullable();
            $table->json('d365_response')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purch_req_journals');
    }
};
