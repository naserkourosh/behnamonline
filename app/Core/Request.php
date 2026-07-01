<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Immutable-ish wrapper around the incoming HTTP request.
 */
final class Request
{
    /** @var array<string,mixed> */
    private array $query;
    /** @var array<string,mixed> */
    private array $post;
    /** @var array<string,mixed> */
    private array $json;
    /** @var array<string,string> */
    private array $params = [];

    public function __construct()
    {
        $this->query = $_GET;
        $this->post  = $_POST;
        $this->json  = $this->parseJson();
    }

    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // Support method spoofing via _method for HTML forms.
        if ($method === 'POST' && isset($this->post['_method'])) {
            $method = strtoupper((string) $this->post['_method']);
        }
        return $method;
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function path(): string
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rawurldecode($path);
        return '/' . trim($path, '/');
    }

    public function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xrw    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json')
            || strtolower($xrw) === 'xmlhttprequest'
            || str_starts_with($this->path(), '/api/');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->json[$key] ?? $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        return array_merge($this->query, $this->post, $this->json);
    }

    /** Full query string params (including array values like brand[]). @return array<string,mixed> */
    public function queryAll(): array
    {
        return $this->query;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $val = $_SERVER[$key] ?? null;
        return $val !== null ? (string) $val : null;
    }

    public function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /** @param array<string,string> $params */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, ?string $default = null): ?string
    {
        return $this->params[$key] ?? $default;
    }

    /** @return array<string,mixed> */
    private function parseJson(): array
    {
        $type = $_SERVER['CONTENT_TYPE'] ?? '';
        if (!str_contains($type, 'application/json')) {
            return [];
        }
        $raw = file_get_contents('php://input') ?: '';
        if ($raw === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
