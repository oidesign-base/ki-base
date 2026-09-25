<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private function __construct(
        public readonly string $method,
        public readonly string $path,
        private array $query,
        private array $post,
        private array $server,
    ) {
    }

    public static function capture(): self
    {
        $uri  = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = (string) parse_url($uri, PHP_URL_PATH);
        $path = '/' . trim(rawurldecode($path), '/');

        return new self(
            strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            $path,
            $_GET,
            $_POST,
            $_SERVER,
        );
    }

    /** POST value as a trimmed string ('' when missing or not a string). */
    public function input(string $key, string $default = ''): string
    {
        $value = $this->post[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    /** POST value exactly as sent (for passwords: no trimming). */
    public function raw(string $key): string
    {
        $value = $this->post[$key] ?? '';
        return is_string($value) ? $value : '';
    }

    public function all(): array
    {
        return $this->post;
    }

    public function query(string $key, string $default = ''): string
    {
        $value = $this->query[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    public function ip(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function isSecure(): bool
    {
        $https = strtolower((string) ($this->server['HTTPS'] ?? ''));
        return ($https !== '' && $https !== 'off')
            || (int) ($this->server['SERVER_PORT'] ?? 0) === 443
            || strtolower((string) ($this->server['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }

    public function host(): string
    {
        return (string) ($this->server['HTTP_HOST'] ?? 'localhost');
    }

    public function uri(): string
    {
        return (string) ($this->server['REQUEST_URI'] ?? '/');
    }
}
