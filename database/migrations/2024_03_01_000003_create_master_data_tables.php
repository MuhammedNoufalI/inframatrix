<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('server_name')->unique();
            $table->string('subscription_name')->nullable();
            $table->string('location')->nullable();
            $table->string('provider'); // Azure, AWS, Contabo, On-prem, Other
            $table->string('panel'); // CloudPanel, Plesk, None
            $table->string('os_name')->nullable();
            $table->string('os_version')->nullable();
            $table->string('status')->default('active'); // active, maintenance, decommissioned
            $table->boolean('amc')->default(false);
            $table->string('public_ip')->nullable();
            $table->string('private_ip')->nullable();
            $table->timestamps();
        });

        Schema::create('git_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // GitHub, GitLab, Other
            $table->string('base_url')->nullable();
            $table->timestamps();
        });

        Schema::create('integration_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('behavior'); // sendgrid_id, recaptcha_account, generic_value
            $table->timestamps();
        });

        Schema::create('recaptcha_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recaptcha_accounts');
        Schema::dropIfExists('integration_types');
        Schema::dropIfExists('git_providers');
        Schema::dropIfExists('servers');
    }
};
