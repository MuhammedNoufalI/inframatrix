<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['integration_types', 'accounts', 'servers', 'users', 'projects', 'environments'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->uuid('import_uid')->nullable();
            });

            // Backfill existing records safely
            $records = DB::table($table)->get();
            foreach ($records as $record) {
                DB::table($table)->where('id', $record->id)->update(['import_uid' => (string) Str::uuid()]);
            }

            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->uuid('import_uid')->nullable(false)->change();
                $tableBlueprint->unique('import_uid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['integration_types', 'accounts', 'servers', 'users', 'projects', 'environments'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->dropUnique([$tableBlueprint->getTable() . '_import_uid_unique'] ?? ['import_uid']);
                $tableBlueprint->dropColumn('import_uid');
            });
        }
    }
};
