<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;

/**
 * Base controller with shared response helpers. Controllers stay thin —
 * business logic lives in Services, data access in Repositories.
 */
abstract class Controller
{
    /** @param array<string,mixed> $data */
    protected function view(string $template, array $data = [], ?string $layout = 'storefront', int $status = 200): Response
    {
        return Response::html(View::render($template, $data, $layout), $status);
    }

    /** @param array<string,mixed>|list<mixed> $data */
    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function notFound(): Response
    {
        return Response::html(View::renderError(404), 404);
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }
}
