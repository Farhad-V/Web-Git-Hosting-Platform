<?php

namespace App\Enums;

enum RepositoryVisibility: string
{
    case PublicAccess = 'public';
    case PrivateAccess = 'private';

    /**
     * Return all supported values.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $visibility): string => $visibility->value,
            self::cases(),
        );
    }
}
