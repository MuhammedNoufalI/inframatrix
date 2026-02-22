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
        Schema::table('git_providers', function (Blueprint $table) {
            $table->uuid('import_uid')->nullable();
        });

        // Backfill existing records safely
        $records = DB::table('git_providers')->get();
        foreach ($records as $record) {
            DB::table('git_providers')->where('id', $record->id)->update(['import_uid' => (string) Str::uuid()]);
        }

        Schema::table('git_providers', function (Blueprint $table) {
            $table->uuid('import_uid')->nullable(false)->change();
            $table->unique('import_uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('git_providers', function (Blueprint $table) {
            $table->dropUnique(['import_uid']);
            $table->dropColumn('import_uid');
        });
    }
};
