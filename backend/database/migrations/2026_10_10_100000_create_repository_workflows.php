<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pull_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('repository_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('number');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('base_branch', 255);
            $table->string('head_branch', 255);
            $table->string('status', 16)->default('open');
            $table->string('merge_commit', 64)->nullable();
            $table->string('merge_base', 64)->nullable();
            $table->string('merge_head', 64)->nullable();
            $table->timestampTz('merged_at')->nullable();
            $table->timestampsTz();
            $table->unique(['repository_id', 'number']);
            $table->index(['repository_id', 'status']);
        });
        Schema::create('pull_request_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pull_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->text('body');
            $table->timestampsTz();
        });
        Schema::create('repository_action_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('repository_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('branch', 255);
            $table->string('commit', 64);
            $table->string('status', 16)->default('queued');
            $table->json('steps')->nullable();
            $table->text('log')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();
            $table->timestampsTz();
            $table->index(['repository_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repository_action_runs');
        Schema::dropIfExists('pull_request_comments');
        Schema::dropIfExists('pull_requests');
    }
};
