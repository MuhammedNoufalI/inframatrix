<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('active'); // active, on_hold, archived
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('environments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // staging, live
            $table->string('url')->nullable();
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('git_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('repo_url')->nullable();
            $table->string('repo_branch')->nullable();
            $table->boolean('cicd_configured')->default(false);
            $table->text('cicd_not_configured_reason')->nullable();
            $table->string('checklist_attachment')->nullable(); // Only for live
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role'); // owner, editor, viewer
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('environment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('integration_type_id')->constrained()->cascadeOnDelete();
            
            // Specific fields
            $table->string('sendgrid_id')->nullable();
            $table->foreignId('recaptcha_account_id')->nullable()->constrained()->nullOnDelete();
            
            // Generic value
            $table->text('value')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('environments');
        Schema::dropIfExists('projects');
    }
};
