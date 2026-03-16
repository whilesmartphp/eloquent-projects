<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->index(['owner_type', 'owner_id']);

            $table->string('creator_type')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->index(['creator_type', 'creator_id']);

            $table->foreignId('status_id')->nullable()->constrained('project_statuses')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('archived_at')->nullable();
            $table->index('archived_at');

            $table->timestamps();
        });

        Schema::create('project_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            $table->string('assignable_type');
            $table->unsignedBigInteger('assignable_id');
            $table->index(['assignable_type', 'assignable_id']);

            $table->string('role');
            $table->index('role');

            $table->string('assigned_by_type')->nullable();
            $table->unsignedBigInteger('assigned_by_id')->nullable();
            $table->index(['assigned_by_type', 'assigned_by_id']);

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(
                ['project_id', 'assignable_type', 'assignable_id', 'role'],
                'project_assignment_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_assignments');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_statuses');
    }
};
