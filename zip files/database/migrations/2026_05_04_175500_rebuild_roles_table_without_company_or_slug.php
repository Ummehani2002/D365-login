<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        if (Schema::hasTable('roles_new')) {
            Schema::drop('roles_new');
        }

        Schema::disableForeignKeyConstraints();

        Schema::create('roles_new', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $oldRows = DB::table('roles')->orderBy('id')->get();
        $now = now();

        foreach ($oldRows as $row) {
            $name = trim((string) ($row->name ?? ''));
            if ($name === '') {
                $name = 'Role '.$row->id;
            }

            DB::table('roles_new')->insert([
                'id' => $row->id,
                'name' => $name,
                'created_at' => $row->created_at ?? $now,
                'updated_at' => $row->updated_at ?? $now,
            ]);
        }

        Schema::drop('roles');
        Schema::rename('roles_new', 'roles');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Irreversible because dropped columns are intentionally removed.
    }
};
