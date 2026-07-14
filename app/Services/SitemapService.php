<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BlogPostRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\PageRepository;
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
            $this->url('about', null, 'monthly', '0.4'),
            $this->url('contact', null, 'monthly', '0.4'),
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
        try {
            foreach ((new PageRepository())->sitemapList() as $pg) {
                $urls[] = $this->url('page/' . $pg['slug'], ($pg['updated_at'] ?: $pg['created_at']) ?? null, 'monthly', '0.4');
            }
        } catch (\Throwable) {
            // pages table not migrated yet — sitemap must keep working.
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
        $disallow = [
            'Disallow: /admin',
            'Disallow: /account',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /pay',
            'Disallow: /login',
            'Disallow: /logout',
            'Disallow: /api/',
            'Disallow: /*?*sort=',
        ];

        $lines = array_merge(['User-agent: *', 'Allow: /'], $disallow);

        // خزنده‌های هوش مصنوعی (ChatGPT، Claude، Perplexity، Gemini) صریحاً
        // مجازند تا فروشگاه در پاسخ‌های آن‌ها دیده شود؛ همان مسیرهای خصوصی بسته است.
        $aiBots = ['GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-Web', 'PerplexityBot', 'Google-Extended', 'Applebot-Extended'];
        foreach ($aiBots as $bot) {
            $lines[] = '';
            $lines[] = 'User-agent: ' . $bot;
            $lines[] = 'Allow: /';
            $lines = array_merge($lines, $disallow);
        }

        $lines[] = '';
        $lines[] = 'Sitemap: ' . abs_url('sitemap.xml');
        return implode("\n", $lines) . "\n";
    }

    /**
     * llms.txt — راهنمای ساخت‌یافتهٔ سایت برای دستیارهای هوش مصنوعی
     * (llmstxt.org). خلاصهٔ فروشگاه + لینک بخش‌های اصلی و دسته‌بندی‌ها.
     */
    public function llms(): string
    {
        $brand = (string) setting('brand_name', 'بهنام');
        $lines = [
            '# ' . $brand,
            '',
            '> فروشگاه اینترنتی محصولات آرایشی، بهداشتی و شویندهٔ اصل در ایران؛ با ضمانت اصالت کالا و ارسال به سراسر کشور. قیمت‌ها به تومان است.',
            '',
            '## بخش‌های اصلی',
            '',
            '- [همهٔ محصولات](' . abs_url('category') . '): فهرست کامل محصولات با فیلتر برند و قیمت',
            '- [مجلهٔ زیبایی](' . abs_url('blog') . '): مقالات آموزشی مراقبت از پوست و مو',
            '- [سوالات متداول](' . abs_url('faq') . ')',
            '- [درباره ما](' . abs_url('about') . ')',
            '- [تماس با ما](' . abs_url('contact') . ')',
            '',
            '## دسته‌بندی‌های محصولات',
            '',
        ];
        foreach ((new CategoryRepository())->sitemapList() as $c) {
            $lines[] = '- [' . (string) ($c['name'] ?? $c['slug']) . '](' . abs_url('category/' . $c['slug']) . ')';
        }
        try {
            $pages = (new PageRepository())->footerPages();
            if ($pages !== []) {
                $lines[] = '';
                $lines[] = '## صفحات اطلاعاتی';
                $lines[] = '';
                foreach ($pages as $pg) {
                    $lines[] = '- [' . (string) $pg['title'] . '](' . abs_url('page/' . $pg['slug']) . ')';
                }
            }
        } catch (\Throwable) {
        }
        $lines[] = '';
        $lines[] = '## داده‌های ساخت‌یافته';
        $lines[] = '';
        $lines[] = '- [نقشهٔ سایت XML](' . abs_url('sitemap.xml') . ')';
        $lines[] = '- صفحات محصول دارای Schema.org Product (قیمت، موجودی، امتیاز) هستند.';
        return implode("\n", $lines) . "\n";
    }
}
