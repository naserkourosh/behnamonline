<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP response value object. Controllers return one of these; the
 * router sends it.
 */
final class Response
{
    private int $status = 200;
    /** @var array<string,string> */
    private array $headers = [];
    private string $body = '';

    public function status(int $code): self
    {
        $this->status = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function body(string $content): self
    {
        $this->body = $content;
        return $this;
    }

    public static function html(string $html, int $status = 200): self
    {
        return (new self())
            ->status($status)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->body($html);
    }

    /** @param array<string,mixed>|list<mixed> $data */
    public static function json(array $data, int $status = 200): self
    {
        return (new self())
            ->status($status)
            ->header('Content-Type', 'application/json; charset=utf-8')
            ->body((string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return (new self())->status($status)->header('Location', $url);
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            // Dynamic output must never be served stale by the browser (admin
            // lists, cart badge, review queue…). Static assets are served by
            // Apache directly, so this only affects PHP responses.
            if (!isset($this->headers['Cache-Control'])) {
                header('Cache-Control: no-store, max-age=0');
            }
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
        }
        echo $this->body;
    }
}
