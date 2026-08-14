<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class GitTransportException extends RuntimeException
{
    /**
     * Create an exception for a failed Smart HTTP operation.
     */
    public function __construct(
        public readonly string $operation,
        public readonly int $processExitCode,
        string $message = 'Git transport operation failed.',
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            code: $processExitCode,
            previous: $previous,
        );
    }
}
