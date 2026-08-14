<?php

use App\Enums\RepositoryStatus;
use App\Enums\RepositoryVisibility;
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
        Schema::create('repositories', function (Blueprint $table): void {
            $table->id();

            $table
                ->foreignId('owner_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table
                ->uuid('storage_uuid')
                ->unique();

            $table->string('name', 100);
            $table->string('slug', 100);
            $table->text('description')->nullable();

            $table
                ->string('visibility', 16)
                ->default(RepositoryVisibility::PrivateAccess->value);

            $table
                ->string('status', 32)
                ->default(RepositoryStatus::Active->value);

            $table
                ->string('default_branch', 255)
                ->nullable();

            $table->timestampTz('archived_at')->nullable();
            $table->timestampTz('deletion_requested_at')->nullable();
            $table->text('deletion_failure_reason')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['owner_id', 'slug'],
                'repositories_owner_slug_unique',
            );

            $table->index('visibility');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
};
