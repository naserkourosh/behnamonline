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
}
