<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_issue_journals', function (Blueprint $table): void {
            $table->id();
            $table->string('request_id', 50)->index();
            $table->string('journal_id', 100)->nullable()->index();
            $table->string('company', 20)->index();
            $table->string('project_id', 100)->index();
            $table->string('description', 255);
            $table->string('invent_site_id', 100);
            $table->string('invent_location_id', 100);
            $table->string('tax_group_id', 100)->nullable();
            $table->string('tax_item_group_id', 100)->nullable();
            $table->json('lines');
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_issue_journals');
    }
};
