<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    private function __construct(
        private int $status,
        private string $body,
        private array $headers = [],
    ) {
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self($status, $body, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /** Redirect after POST / to another page (303 so the browser uses GET). */
    public static function redirect(string $location, int $status = 303): self
    {
        return new self($status, '', ['Location' => $location]);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
