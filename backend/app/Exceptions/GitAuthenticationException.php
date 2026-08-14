<?php

namespace App\Exceptions;

use RuntimeException;

class GitAuthenticationException extends RuntimeException
{
    /**
     * Create an authentication exception without exposing credentials.
     */
    public function __construct(
        string $message = 'Git authentication failed.',
    ) {
        parent::__construct($message);
    }
}
