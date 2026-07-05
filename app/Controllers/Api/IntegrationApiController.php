<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;

/**
 * Token-protected API for accounting / inventory software (هلو / محک and
 * similar). Lets external systems read the catalog and orders, and push stock
 * + price updates back into the shop. Auth is enforced by RequireApiKey.
 */
final class IntegrationApiController extends Controller
{
    /** GET /api/integration/products?page=&limit= — catalog with stock+price. */
    public function products(Request $request): Response
    {
        $repo   = new ProductRepository();
        $limit  = (int) $request->query('limit', 100);
        $page   = max(1, (int) $request->query('page', 1));
        $total  = $repo->countAll();
        $rows   = $repo->apiList($limit, ($page - 1) * max(1, $limit));

        $items = array_map(static fn (array $p): array => [
            'id'         => (int) $p['id'],
            'sku'        => $p['sku'],
            'barcode'    => $p['barcode'],
            'name'       => $p['name'],
            'price'      => (int) $p['price'],
            'old_price'  => $p['old_price'] !== null ? (int) $p['old_price'] : null,
            'stock'      => (int) $p['stock'] - (int) $p['reserved'],
            'is_active'  => (int) $p['is_active'] === 1,
            'category'   => $p['category'],
            'brand'      => $p['brand'],
            'updated_at' => $p['updated_at'],
        ], $rows);

        return $this->json(['ok' => true, 'total' => $total, 'page' => $page, 'products' => $items]);
    }

    /** GET /api/integration/orders?since=YYYY-MM-DD&all=1&with_items=1 */
    public function orders(Request $request): Response
    {
        $repo     = new OrderRepository();
        $limit    = (int) $request->query('limit', 100);
        $page     = max(1, (int) $request->query('page', 1));
        $since    = ($s = trim((string) $request->query('since', ''))) !== '' ? $s : null;
        $paidOnly = $request->query('all') !== '1';
        $withItems = $request->query('with_items') === '1';

        $rows = $repo->apiOrders($limit, ($page - 1) * max(1, $limit), $since, $paidOnly);
        $orders = array_map(function (array $o) use ($repo, $withItems): array {
            $row = [
                'order_number'   => $o['order_number'],
                'created_at'     => $o['created_at'],
                'customer'       => $o['receiver_name'],
                'mobile'         => $o['mobile'],
                'province'       => $o['province'],
                'city'           => $o['city'],
                'subtotal'       => (int) $o['subtotal'],
                'discount'       => (int) $o['discount'] + (int) $o['coupon_discount'],
                'shipping_cost'  => (int) $o['shipping_cost'],
                'total'          => (int) $o['total'],
                'payment_method' => $o['payment_method'],
                'payment_status' => $o['payment_status'],
                'status'         => $o['status'],
                'tracking_code'  => $o['tracking_code'],
                'item_count'     => (int) $o['item_count'],
            ];
            if ($withItems) {
                $row['items'] = array_map(static fn (array $it): array => [
                    'sku'        => $it['sku'] ?? null,
                    'name'       => $it['name'],
                    'qty'        => (int) $it['qty'],
                    'unit_price' => (int) $it['unit_price'],
                    'line_total' => (int) $it['line_total'],
                ], $repo->items((int) $o['id']));
            }
            return $row;
        }, $rows);

        return $this->json(['ok' => true, 'page' => $page, 'orders' => $orders]);
    }

    /**
     * POST /api/integration/stock — push stock (and optional price) updates.
     * Body: {"items":[{"code":"SKU|barcode","stock":12,"price":150000}, ...]}
     * `code` is matched against SKU then barcode.
     */
    public function stock(Request $request): Response
    {
        $items = $request->input('items', []);
        if (!is_array($items) || $items === []) {
            return $this->json(['ok' => false, 'error' => 'فهرست items خالی است.'], 422);
        }

        $repo    = new ProductRepository();
        $updated = 0;
        $results = [];
        foreach ($items as $row) {
            if (!is_array($row)) {
                continue;
            }
            $code  = trim((string) ($row['code'] ?? $row['sku'] ?? $row['barcode'] ?? ''));
            $stock = array_key_exists('stock', $row) && $row['stock'] !== null ? (int) $row['stock'] : null;
            $price = array_key_exists('price', $row) && $row['price'] !== null ? (int) $row['price'] : null;

            if ($code === '' || ($stock === null && $price === null)) {
                $results[] = ['code' => $code, 'ok' => false, 'error' => 'کد یا مقدار نامعتبر'];
                continue;
            }
            $product = $repo->findByCode($code);
            if ($product === null) {
                $results[] = ['code' => $code, 'ok' => false, 'error' => 'محصول یافت نشد'];
                continue;
            }
            $repo->setStockPrice((int) $product['id'], $stock, $price);
            $updated++;
            $results[] = ['code' => $code, 'ok' => true, 'id' => (int) $product['id']];
        }

        return $this->json(['ok' => true, 'updated' => $updated, 'results' => $results]);
    }
}
