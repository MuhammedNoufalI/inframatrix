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
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId('integration_type_id')->nullable()->constrained()->cascadeOnDelete()->after('id');
            $table->dropColumn('provider_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('provider_type')->after('account_name');
            $table->dropForeign(['integration_type_id']);
            $table->dropColumn('integration_type_id');
        });
    }
};
