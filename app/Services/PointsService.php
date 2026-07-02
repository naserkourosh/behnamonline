<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PointTransactionRepository;
use App\Repositories\UserRepository;

/**
 * Reward-points (loyalty club). Points are earned when an order is paid,
 * at a configurable percentage of the order subtotal. One point = one Toman
 * of future credit. Awarding is idempotent per order.
 */
final class PointsService
{
    public function enabled(): bool
    {
        return (bool) \setting('points_enabled', false);
    }

    public function earnPercent(): float
    {
        return max(0.0, (float) \setting('points_earn_percent', 0));
    }

    /**
     * Award earning points for a freshly-paid order. Safe to call more than
     * once — it no-ops if this order already earned points.
     * @param array<string,mixed> $order
     */
    public function awardForOrder(array $order): int
    {
        $userId  = (int) ($order['user_id'] ?? 0);
        $orderId = (int) ($order['id'] ?? 0);
        if ($userId === 0 || $orderId === 0 || !$this->enabled() || $this->earnPercent() <= 0) {
            return 0;
        }

        $ledger = new PointTransactionRepository();
        if ($ledger->existsForOrder($orderId)) {
            return 0;
        }

        $subtotal = (int) ($order['subtotal'] ?? 0);
        $points   = (int) floor($subtotal * $this->earnPercent() / 100);
        if ($points <= 0) {
            return 0;
        }

        (new UserRepository())->addPoints($userId, $points);
        $ledger->add($userId, $orderId, $points, 'earn', 'خرید سفارش ' . (string) ($order['order_number'] ?? ''));

        // Record the awarded amount on the order for reporting.
        (new \App\Repositories\OrderRepository())->setPointsEarned($orderId, $points);

        return $points;
    }
}
