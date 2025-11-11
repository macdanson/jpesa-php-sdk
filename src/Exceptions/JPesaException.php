<?php

namespace JPesa\SDK\Exceptions;

use Throwable;

class JPesaException extends \RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?array $payload = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
