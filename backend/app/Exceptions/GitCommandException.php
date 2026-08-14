<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class GitCommandException extends RuntimeException
{
    /**
     * Create an exception for a failed controlled Git operation.
     */
    public function __construct(
        public readonly string $operation,
        public readonly int $processExitCode,
        public readonly string $errorOutput,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Git operation [%s] failed with exit code [%d].',
                $operation,
                $processExitCode,
            ),
            $processExitCode,
            $previous,
        );
    }
}
