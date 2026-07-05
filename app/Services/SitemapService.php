<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BlogPostRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;

/**
 * Builds the XML sitemap (sitemaps.org 0.9) and the robots.txt body for the
 * storefront. The sitemap lists the static hubs plus every active product,
 * active category, and published blog post so Google/ترب crawl the catalog
 * efficiently. robots.txt allows the public store, blocks private/functional
 * paths, and points crawlers at the absolute sitemap URL.
 */
final class SitemapService
{
    /** @return list<array{loc:string,lastmod:?string,changefreq:string,priority:string}> */
    public function urls(): array
    {
        $urls = [
            $this->url('', null, 'daily', '1.0'),
            $this->url('category', null, 'daily', '0.9'),
            $this->url('blog', null, 'weekly', '0.6'),
            $this->url('faq', null, 'monthly', '0.4'),
        ];

        foreach ((new CategoryRepository())->sitemapList() as $c) {
            $urls[] = $this->url('category/' . $c['slug'], $c['created_at'] ?? null, 'weekly', '0.8');
        }
        foreach ((new ProductRepository())->sitemapList() as $p) {
            $urls[] = $this->url('product/' . $p['slug'], ($p['updated_at'] ?: $p['created_at']) ?? null, 'weekly', '0.7');
        }
        foreach ((new BlogPostRepository())->sitemapList() as $b) {
            $urls[] = $this->url('blog/' . $b['slug'], ($b['updated_at'] ?: $b['published_at']) ?? null, 'monthly', '0.5');
        }

        return $urls;
    }

    /** @return array{loc:string,lastmod:?string,changefreq:string,priority:string} */
    private function url(string $path, ?string $lastmod, string $changefreq, string $priority): array
    {
        $ts = $lastmod !== null && $lastmod !== '' ? strtotime((string) $lastmod) : false;
        return [
            'loc'        => abs_url($path),
            'lastmod'    => $ts ? date('Y-m-d', $ts) : null,
            'changefreq' => $changefreq,
            'priority'   => $priority,
        ];
    }

    public function xml(): string
    {
        $x  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $x .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($this->urls() as $u) {
            $x .= "  <url>\n";
            $x .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
            if ($u['lastmod'] !== null) {
                $x .= '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
            }
            $x .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
            $x .= '    <priority>' . $u['priority'] . "</priority>\n";
            $x .= "  </url>\n";
        }
        return $x . '</urlset>';
    }

    public function robots(): string
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /account',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /pay',
            'Disallow: /login',
            'Disallow: /logout',
            'Disallow: /api/',
            'Disallow: /*?*sort=',
            '',
            'Sitemap: ' . abs_url('sitemap.xml'),
        ];
        return implode("\n", $lines) . "\n";
    }
}
