<?php

namespace App\Models;

use App\Enums\RepositoryVisibility;
use Database\Factories\RepositoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repository extends Model
{
    /** @use HasFactory<RepositoryFactory> */
    use HasFactory;

    /**
     * Attributes that may be assigned through application workflows.
     *
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'visibility',
    ];

    /**
     * Get the repository owner.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'owner_id',
        );
    }

    /**
     * Return the logical repository namespace.
     */
    public function fullName(): string
    {
        return "{$this->owner->username}/{$this->name}";
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
        ];
    }
}
