<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('amc');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->unique(['integration_type_id', 'account_name'], 'account_integration_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique('account_integration_unique');
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
