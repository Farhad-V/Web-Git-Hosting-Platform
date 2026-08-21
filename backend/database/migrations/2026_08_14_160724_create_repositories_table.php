<?php

use App\Enums\RepositoryVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            'repositories',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('owner_id')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->string('name', 100);

                $table
                    ->string('visibility', 16)
                    ->default(
                        RepositoryVisibility::PrivateAccess->value,
                    );

                $table->timestampsTz();

                $table->unique(
                    ['owner_id', 'name'],
                    'repositories_owner_name_unique',
                );

                $table->index(
                    'owner_id',
                    'repositories_owner_id_index',
                );

                $table->index(
                    'visibility',
                    'repositories_visibility_index',
                );
            },
        );

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                <<<'SQL'
                ALTER TABLE repositories
                ADD CONSTRAINT repositories_visibility_check
                CHECK (visibility IN ('public', 'private'))
                SQL,
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
};
