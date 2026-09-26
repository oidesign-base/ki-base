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

    /** PDF shown in the browser's viewer (print from there); $filename for saving. */
    public static function pdf(string $body, string $filename): self
    {
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?? 'document.pdf';
        return new self(200, $body, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $safe . '"',
            'Content-Length'      => (string) strlen($body),
            'Cache-Control'       => 'private, no-store',
        ]);
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
