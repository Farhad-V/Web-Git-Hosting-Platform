<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class RepositoryBrowserException extends RuntimeException
{
    /**
     * Create an exception for a failed repository-browsing operation.
     */
    public function __construct(
        public readonly string $operation,
        public readonly int $repositoryId,
        public readonly string $reason,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Repository browsing operation [%s] failed for repository [%d]: %s',
                $operation,
                $repositoryId,
                $reason,
            ),
            previous: $previous,
        );
    }
}
