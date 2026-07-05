<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\TorobService;

/**
 * Public Torob product feed. Disabled → 404. The feed URL is registered in
 * the Torob merchant panel so Torob's crawler can index the catalog.
 */
final class TorobController extends Controller
{
    public function feedJson(Request $request): Response
    {
        if (!$this->enabled()) {
            return $this->notFound();
        }
        return (new Response())
            ->header('Content-Type', 'application/json; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=1800')
            ->body((new TorobService())->json());
    }

    public function feedXml(Request $request): Response
    {
        if (!$this->enabled()) {
            return $this->notFound();
        }
        return (new Response())
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=1800')
            ->body((new TorobService())->xml());
    }

    private function enabled(): bool
    {
        return (bool) setting('torob_enabled', true);
    }
}
