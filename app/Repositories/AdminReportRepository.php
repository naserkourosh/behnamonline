<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class AdminReportRepository extends BaseRepository
{
    /** @return array<string,mixed> */
    public function dashboard(): array
    {
        $todayStart = date('Y-m-d 00:00:00');

        $todaySales = (int) $this->scalar(
            "SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid' AND created_at >= ?",
            [$todayStart]
        );
        $ordersToday = (int) $this->scalar('SELECT COUNT(*) FROM orders WHERE created_at >= ?', [$todayStart]);
        $customers   = (int) $this->scalar('SELECT COUNT(*) FROM users');
        $products    = (int) $this->scalar('SELECT COUNT(*) FROM products');
        $pending     = (int) $this->scalar("SELECT COUNT(*) FROM orders WHERE payment_status <> 'paid'");
        $revenue     = (int) $this->scalar("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid'");

        $recentOrders = $this->selectAll(
            "SELECT o.id, o.order_number, o.total, o.status, o.payment_status, o.created_at, o.receiver_name
               FROM orders o ORDER BY o.id DESC LIMIT 8"
        );

        $lowStock = $this->selectAll(
            'SELECT id, name, stock, low_stock_threshold FROM products
              WHERE is_active = 1 AND stock <= low_stock_threshold
              ORDER BY stock ASC LIMIT 8'
        );

        // Sales for the last 7 days (for the bar chart).
        $bars = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStart = date('Y-m-d 00:00:00', strtotime("-{$i} days"));
            $dayEnd   = date('Y-m-d 23:59:59', strtotime("-{$i} days"));
            $sum = (int) $this->scalar(
                "SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid' AND created_at BETWEEN ? AND ?",
                [$dayStart, $dayEnd]
            );
            $bars[] = ['label' => self::WEEKDAYS[(int) date('w', strtotime($dayStart))], 'value' => $sum];
        }

        return [
            'todaySales'   => $todaySales,
            'ordersToday'  => $ordersToday,
            'customers'    => $customers,
            'products'     => $products,
            'pending'      => $pending,
            'revenue'      => $revenue,
            'recentOrders' => $recentOrders,
            'lowStock'     => $lowStock,
            'bars'         => $bars,
        ];
    }

    private const WEEKDAYS = ['ی', 'د', 'س', 'چ', 'پ', 'ج', 'ش'];

    /**
     * Full analytics report for a date range (inclusive, `Y-m-d`).
     * Every figure is scoped to paid orders unless noted otherwise.
     *
     * @return array<string,mixed>
     */
    public function report(string $from, string $to): array
    {
        $start = $from . ' 00:00:00';
        $end   = $to . ' 23:59:59';
        $range = [$start, $end];

        // Head-line totals (paid orders only).
        $revenue     = (int) $this->scalar("SELECT COALESCE(SUM(total),0)           FROM orders WHERE payment_status='paid' AND created_at BETWEEN ? AND ?", $range);
        $paidOrders  = (int) $this->scalar("SELECT COUNT(*)                          FROM orders WHERE payment_status='paid' AND created_at BETWEEN ? AND ?", $range);
        $allOrders   = (int) $this->scalar("SELECT COUNT(*)                          FROM orders WHERE created_at BETWEEN ? AND ?", $range);
        $itemsSold   = (int) $this->scalar(
            "SELECT COALESCE(SUM(oi.qty),0) FROM order_items oi
               JOIN orders o ON o.id = oi.order_id
              WHERE o.payment_status='paid' AND o.created_at BETWEEN ? AND ?",
            $range
        );
        $couponGiven = (int) $this->scalar("SELECT COALESCE(SUM(coupon_discount),0)  FROM orders WHERE payment_status='paid' AND created_at BETWEEN ? AND ?", $range);
        $shipping    = (int) $this->scalar("SELECT COALESCE(SUM(shipping_cost),0)    FROM orders WHERE payment_status='paid' AND created_at BETWEEN ? AND ?", $range);
        $newCustomers = (int) $this->scalar("SELECT COUNT(*)                         FROM users WHERE created_at BETWEEN ? AND ?", $range);
        $aov         = $paidOrders > 0 ? (int) round($revenue / $paidOrders) : 0;
        $conversion  = $allOrders > 0 ? round($paidOrders / $allOrders * 100, 1) : 0.0;

        // Daily sales series across the range.
        $series = $this->selectAll(
            "SELECT DATE(created_at) AS d,
                    COALESCE(SUM(total),0) AS revenue,
                    COUNT(*) AS orders
               FROM orders
              WHERE payment_status='paid' AND created_at BETWEEN ? AND ?
              GROUP BY DATE(created_at) ORDER BY d",
            $range
        );

        // Top products by revenue.
        $topProducts = $this->selectAll(
            "SELECT oi.product_id, oi.name,
                    SUM(oi.qty) AS qty,
                    SUM(oi.line_total) AS revenue
               FROM order_items oi
               JOIN orders o ON o.id = oi.order_id
              WHERE o.payment_status='paid' AND o.created_at BETWEEN ? AND ?
              GROUP BY oi.product_id, oi.name
              ORDER BY revenue DESC LIMIT 10",
            $range
        );

        // Top categories by revenue (via the product↔category pivot).
        $topCategories = $this->selectAll(
            "SELECT c.id, c.name,
                    SUM(oi.line_total) AS revenue,
                    SUM(oi.qty) AS qty
               FROM order_items oi
               JOIN orders o ON o.id = oi.order_id
               JOIN product_categories pc ON pc.product_id = oi.product_id
               JOIN categories c ON c.id = pc.category_id
              WHERE o.payment_status='paid' AND o.created_at BETWEEN ? AND ?
              GROUP BY c.id, c.name
              ORDER BY revenue DESC LIMIT 8",
            $range
        );

        // Order status + payment-method breakdowns.
        $statusRows = $this->selectAll(
            "SELECT status, COUNT(*) AS n FROM orders WHERE created_at BETWEEN ? AND ? GROUP BY status",
            $range
        );
        $paymentRows = $this->selectAll(
            "SELECT COALESCE(NULLIF(payment_method,''),'—') AS method, COUNT(*) AS n, COALESCE(SUM(total),0) AS revenue
               FROM orders WHERE payment_status='paid' AND created_at BETWEEN ? AND ? GROUP BY payment_method",
            $range
        );

        return [
            'from'          => $from,
            'to'            => $to,
            'revenue'       => $revenue,
            'paidOrders'    => $paidOrders,
            'allOrders'     => $allOrders,
            'itemsSold'     => $itemsSold,
            'couponGiven'   => $couponGiven,
            'shipping'      => $shipping,
            'newCustomers'  => $newCustomers,
            'aov'           => $aov,
            'conversion'    => $conversion,
            'series'        => $series,
            'topProducts'   => $topProducts,
            'topCategories' => $topCategories,
            'statusRows'    => $statusRows,
            'paymentRows'   => $paymentRows,
        ];
    }
}
