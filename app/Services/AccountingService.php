<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Accounting export bridge for Holoo / Mahak.
 *
 * These systems import catalog and sales data from delimited files. This
 * service produces UTF-8 (BOM-prefixed, Excel/Holoo-friendly) CSV strings for
 * products and orders. The column layout is intentionally generic and can be
 * remapped to a specific Holoo/Mahak template without touching the data layer.
 */
final class AccountingService
{
    /** Product catalog → CSV (code, name, group, brand, sale price, stock, barcode). */
    public function productsCsv(): string
    {
        $rows = Database::connection()->query(
            "SELECT p.id, p.sku, p.barcode, p.name, p.price, p.stock,
                    c.name AS category, b.name AS brand
               FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
          LEFT JOIN brands b ON b.id = p.brand_id
              ORDER BY p.id"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $header = ['کد کالا', 'بارکد', 'نام کالا', 'گروه', 'برند', 'قیمت فروش (تومان)', 'موجودی'];
        $out    = [$header];
        foreach ($rows as $r) {
            $out[] = [
                $r['sku'] ?: (string) $r['id'],
                $r['barcode'] ?: '',
                $r['name'],
                $r['category'] ?: '',
                $r['brand'] ?: '',
                (int) $r['price'],
                (int) $r['stock'],
            ];
        }
        return $this->toCsv($out);
    }

    /**
     * Sales invoices → CSV. One row per order (paid orders by default).
     * @param bool $paidOnly Restrict to settled (paid) invoices.
     */
    public function ordersCsv(bool $paidOnly = true): string
    {
        $where = $paidOnly ? "WHERE o.payment_status = 'paid'" : '';
        $rows  = Database::connection()->query(
            "SELECT o.order_number, o.created_at, o.subtotal, o.shipping_cost, o.discount, o.total,
                    o.payment_method, o.payment_status, o.status, o.mobile,
                    CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')) AS customer
               FROM orders o
          LEFT JOIN users u ON u.id = o.user_id
               {$where}
              ORDER BY o.id"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $header = ['شماره فاکتور', 'تاریخ', 'مشتری', 'موبایل', 'جمع کالا', 'حمل', 'تخفیف', 'مبلغ کل', 'روش پرداخت', 'وضعیت پرداخت', 'وضعیت سفارش'];
        $out    = [$header];
        foreach ($rows as $r) {
            $out[] = [
                $r['order_number'],
                $r['created_at'],
                trim((string) $r['customer']) ?: 'مهمان',
                $r['mobile'],
                (int) $r['subtotal'],
                (int) $r['shipping_cost'],
                (int) $r['discount'],
                (int) $r['total'],
                $r['payment_method'],
                $r['payment_status'],
                $r['status'],
            ];
        }
        return $this->toCsv($out);
    }

    /** @return array{products:int,orders_paid:int,orders_total:int} */
    public function counts(): array
    {
        $pdo = Database::connection();
        return [
            'products'     => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
            'orders_paid'  => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'")->fetchColumn(),
            'orders_total' => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
        ];
    }

    /** @param list<list<mixed>> $rows */
    private function toCsv(array $rows): string
    {
        $fh = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = (string) stream_get_contents($fh);
        fclose($fh);
        // UTF-8 BOM so Excel / Holoo import renders Persian correctly.
        return "\xEF\xBB\xBF" . $csv;
    }
}
