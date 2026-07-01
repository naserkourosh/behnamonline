<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

/**
 * PHP-template renderer with a simple layout system.
 *
 * Inside a template, $this is the View instance, so templates can call:
 *   $this->meta(['title' => '…', 'description' => '…'])
 *   $this->partial('product-card', ['product' => $p])
 *   $this->push('json_ld', $schema)   // collected and emitted by the layout
 */
final class View
{
    private static string $viewsPath = '';
    /** @var array<string,mixed> */
    private array $data = [];
    /** @var array<string,mixed> */
    private array $meta = [];
    /** @var array<string,list<string>> */
    private array $stacks = [];

    public static function setPath(string $path): void
    {
        self::$viewsPath = rtrim($path, '/');
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function render(string $template, array $data = [], ?string $layout = 'storefront'): string
    {
        $view = new self();
        $view->data = $data;

        $content = $view->renderFile($template, $data);

        if ($layout === null) {
            return $content;
        }

        return $view->renderFile('layouts/' . $layout, array_merge($data, [
            'content' => $content,
            'meta'    => $view->meta,
        ]));
    }

    public static function renderError(int $status): string
    {
        $titles = [
            404 => 'صفحه یافت نشد',
            405 => 'روش درخواست مجاز نیست',
            419 => 'نشست منقضی شده است',
            429 => 'درخواست بیش از حد',
            500 => 'خطای سرور',
        ];
        $view = new self();
        try {
            $content = $view->renderFile('errors/error', ['status' => $status, 'title' => $titles[$status] ?? 'خطا']);
            return $view->renderFile('layouts/storefront', [
                'content' => $content,
                'meta'    => ['title' => ($titles[$status] ?? 'خطا') . ' | بهنام', 'robots' => 'noindex'],
            ]);
        } catch (Throwable) {
            $label = $status === 404 ? 'صفحه مورد نظر یافت نشد' : 'خطایی رخ داد';
            return '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8">'
                . '<title>' . $status . '</title><body style="font-family:Tahoma;text-align:center;padding:80px;">'
                . '<h1 style="font-size:48px;color:#5C2D46;">' . $status . '</h1><p>' . $label . '</p>'
                . '<a href="/" style="color:#5C2D46;">بازگشت به خانه</a></body></html>';
        }
    }

    /** @param array<string,mixed> $data */
    private function renderFile(string $template, array $data): string
    {
        $file = self::$viewsPath . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$template}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        try {
            include $file;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }

    /** Set page metadata (title, description, og:*, canonical…). @param array<string,mixed> $meta */
    public function meta(array $meta): void
    {
        $this->meta = array_merge($this->meta, $meta);
    }

    /** Render a partial in the current data context. @param array<string,mixed> $data */
    public function partial(string $name, array $data = []): void
    {
        echo $this->renderFile('partials/' . $name, array_merge($this->data, $data));
    }

    public function push(string $stack, string $content): void
    {
        $this->stacks[$stack][] = $content;
    }

    public function stack(string $stack): string
    {
        return implode("\n", $this->stacks[$stack] ?? []);
    }
}
