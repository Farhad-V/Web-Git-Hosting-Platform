<?php

namespace App\Models;

use App\Enums\RepositoryStatus;
use App\Enums\RepositoryVisibility;
use Database\Factories\RepositoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use LogicException;

class Repository extends Model
{
    /** @use HasFactory<RepositoryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Attributes that may be assigned by application services.
     *
     * storage_uuid is intentionally excluded because it is server-controlled.
     *
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'visibility',
        'status',
        'default_branch',
        'archived_at',
        'deletion_requested_at',
        'deletion_failure_reason',
    ];

    /**
     * Get the repository owner.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Return the immutable relative path of the managed bare repository.
     */
    public function storageRelativePath(): string
    {
        return "repositories/{$this->storage_uuid}.git";
    }

    /**
     * Determine whether repository mutations are currently allowed.
     */
    public function isWritable(): bool
    {
        return $this->status->isWritable();
    }

    /**
     * Configure model invariants.
     */
    protected static function booted(): void
    {
        static::creating(function (self $repository): void {
            $repository->storage_uuid ??= (string) Str::uuid();
        });

        static::updating(function (self $repository): void {
            if ($repository->isDirty('owner_id')) {
                throw new LogicException(
                    'Repository ownership cannot be transferred in V1.',
                );
            }

            if ($repository->isDirty('storage_uuid')) {
                throw new LogicException(
                    'Repository storage identity is immutable.',
                );
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visibility' => RepositoryVisibility::class,
            'status' => RepositoryStatus::class,
            'archived_at' => 'immutable_datetime',
            'deletion_requested_at' => 'immutable_datetime',
        ];
    }
}
