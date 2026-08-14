<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class RepositoryStorageException extends RuntimeException
{
    /**
     * Create an exception for a failed repository-storage operation.
     */
    public function __construct(
        public readonly string $operation,
        public readonly int $repositoryId,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Repository storage operation [%s] failed for repository [%d].',
                $operation,
                $repositoryId,
            ),
            previous: $previous,
        );
    }
}
