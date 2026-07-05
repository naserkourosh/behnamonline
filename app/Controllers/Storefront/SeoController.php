<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\SitemapService;

/**
 * Public crawlability endpoints: the XML sitemap and robots.txt. Both are
 * generated from live catalog data and cached at the edge for an hour.
 */
final class SeoController extends Controller
{
    public function sitemap(Request $request): Response
    {
        return (new Response())
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600')
            ->body((new SitemapService())->xml());
    }

    public function robots(Request $request): Response
    {
        return (new Response())
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600')
            ->body((new SitemapService())->robots());
    }
}
