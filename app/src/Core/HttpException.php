<?php

declare(strict_types=1);

namespace App\Core;

/** Exception that maps to an HTTP status page (403, 404, 419, ...). */
final class HttpException extends \RuntimeException
{
    public function __construct(private int $status, string $message = '')
    {
        parent::__construct($message, $status);
    }

    public function status(): int
    {
        return $this->status;
    }
}
