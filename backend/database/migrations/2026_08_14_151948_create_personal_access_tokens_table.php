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
        Schema::create(
            'personal_access_tokens',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->string('name', 100);

                $table
                    ->string('token_identifier', 32)
                    ->unique();

                $table->char(
                    'token_hash',
                    64,
                );

                $table
                    ->timestampTz('revoked_at')
                    ->nullable();

                $table->timestampsTz();

                $table->index(
                    ['user_id', 'revoked_at'],
                    'personal_access_tokens_user_revoked_index',
                );
            },
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'personal_access_tokens',
        );
    }
};