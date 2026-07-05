<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Repositories\ProductRepository;

/**
 * Builds the Torob (ترب) product feed. Torob crawls a public feed URL and
 * indexes the shop's products for its price-comparison search. The feed lists
 * every active product with the fields Torob expects (page_url, title, price,
 * availability, image, category). Serialized as JSON (primary) or XML.
 *
 * Register the feed URL (e.g. https://yoursite/torob.json) in the Torob
 * merchant panel; prices default to Toman (set TOROB_PRICE_RIAL=true for Rial).
 */
final class TorobService
{
    /** @return list<array<string,mixed>> */
    public function items(): array
    {
        $rial = (bool) Config::get('integrations.torob.price_in_rial', false);
        $mult = $rial ? 10 : 1;

        $out = [];
        foreach ((new ProductRepository())->feedList() as $r) {
            $available = (int) $r['stock'] - (int) $r['reserved'];
            $out[] = [
                'product_id'    => (string) $r['id'],
                'page_url'      => abs_url('product/' . $r['slug']),
                'title'         => (string) $r['name'],
                'subtitle'      => trim((string) ($r['brand_name'] ?? '')),
                'price'         => (int) $r['price'] * $mult,
                'old_price'     => $r['old_price'] ? (int) $r['old_price'] * $mult : 0,
                'availability'  => $available > 0 ? 'instock' : 'outofstock',
                'category_name' => (string) ($r['category_name'] ?? ''),
                'image_link'    => !empty($r['image']) ? base_url() . asset((string) $r['image']) : '',
                'short_desc'    => trim(strip_tags((string) ($r['short_desc'] ?? ''))),
                'guarantee'     => '',
            ];
        }
        return $out;
    }

    public function json(): string
    {
        return (string) json_encode(
            ['count' => count($items = $this->items()), 'products' => $items],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }

    public function xml(): string
    {
        $x  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n<products>\n";
        foreach ($this->items() as $p) {
            $x .= "  <product>\n";
            foreach ($p as $k => $v) {
                $x .= '    <' . $k . '>' . htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</' . $k . ">\n";
            }
            $x .= "  </product>\n";
        }
        return $x . '</products>';
    }
}
