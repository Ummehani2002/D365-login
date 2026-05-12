<?php

use App\Models\Pool;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        Schema::table('pools', function (Blueprint $table) {
            if (! Schema::hasColumn('pools', 'uses_project')) {
                $table->boolean('uses_project')->default(false)->after('name');
            }
            if (! Schema::hasColumn('pools', 'uses_warehouse')) {
                $table->boolean('uses_warehouse')->default(false)->after('uses_project');
            }
            if (! Schema::hasColumn('pools', 'has_attachment')) {
                $table->boolean('has_attachment')->default(false)->after('uses_warehouse');
            }
            if (! Schema::hasColumn('pools', 'has_item_category')) {
                $table->boolean('has_item_category')->default(false)->after('has_attachment');
            }
            if (! Schema::hasColumn('pools', 'has_item_id')) {
                $table->boolean('has_item_id')->default(false)->after('has_item_category');
            }
        });

        if (! Schema::hasColumn('pools', 'uses_project')) {
            return;
        }

        Pool::query()->orderBy('id')->chunkById(200, function ($pools): void {
            foreach ($pools as $pool) {
                /** @var Pool $pool */
                $project = trim((string) ($pool->project ?? ''));
                $warehouse = trim((string) ($pool->warehouse ?? ''));
                $pw = trim((string) ($pool->project_warehouse ?? ''));
                $attachment = trim((string) ($pool->attachment ?? ''));
                $itemCategory = trim((string) ($pool->item_category ?? ''));
                $categoryItem = trim((string) ($pool->category_item ?? ''));
                $itemId = trim((string) ($pool->item_id ?? ''));

                $pool->forceFill([
                    'uses_project' => $project !== '' || $pw !== '',
                    'uses_warehouse' => $warehouse !== '' || $pw !== '',
                    'has_attachment' => $attachment !== '',
                    'has_item_category' => $itemCategory !== '' || $categoryItem !== '',
                    'has_item_id' => $itemId !== '' || $categoryItem !== '',
                ])->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        Schema::table('pools', function (Blueprint $table) {
            foreach (['uses_project', 'uses_warehouse', 'has_attachment', 'has_item_category', 'has_item_id'] as $col) {
                if (Schema::hasColumn('pools', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
