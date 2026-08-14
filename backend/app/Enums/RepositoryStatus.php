<?php

namespace App\Enums;

enum RepositoryStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
    case DeletionPending = 'deletion_pending';
    case DeletionFailed = 'deletion_failed';

    /**
     * Return all supported values.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            self::cases(),
        );
    }

    /**
     * Determine whether repository mutations are allowed.
     */
    public function isWritable(): bool
    {
        return $this === self::Active;
    }
}
