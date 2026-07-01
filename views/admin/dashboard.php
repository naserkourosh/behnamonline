<?php
/** @var int $todaySales @var int $ordersToday @var int $customers @var int $products @var int $pending @var int $revenue */
/** @var list<array<string,mixed>> $recentOrders @var list<array<string,mixed>> $lowStock @var list<array<string,mixed>> $bars */
$maxBar = max(1, ...array_map(static fn ($b) => (int) $b['value'], $bars ?: [['value' => 1]]));

$kpis = [
    ['فروش امروز (ت)', money($todaySales), 'bg-[#E7F7F0]', '💰'],
    ['سفارش امروز', fa($ordersToday), 'bg-pink', '🛍️'],
    ['مشتریان', fa($customers), 'bg-[#EEF2FF]', '👤'],
    ['محصولات', fa($products), 'bg-[#FFF6E6]', '📦'],
];
?>
<!-- KPI cards -->
<div class="grid grid-cols-2 gap-3.5 lg:grid-cols-4">
    <?php foreach ($kpis as [$label, $value, $tint, $icon]): ?>
        <div class="rounded-2xl border border-line2 bg-white p-4">
            <div class="mb-2.5 flex h-9 w-9 items-center justify-center rounded-xl2 <?= $tint ?> text-[16px]"><?= $icon ?></div>
            <div class="text-[19px] font-extrabold text-[#333] nums"><?= $value ?></div>
            <div class="mt-0.5 text-[11px] text-[#999]"><?= e($label) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="mt-5 grid gap-5 lg:grid-cols-3">
    <!-- Weekly chart -->
    <div class="rounded-2xl border border-line2 bg-white p-5 lg:col-span-2">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <div class="text-[14px] font-bold text-[#333]">فروش هفته</div>
                <div class="mt-0.5 text-[11px] text-[#999]">۷ روز اخیر</div>
            </div>
            <div class="text-left">
                <div class="text-[16px] font-extrabold text-secondary nums"><?= money($revenue) ?></div>
                <div class="text-[10px] text-[#999]">مجموع فروش پرداخت‌شده</div>
            </div>
        </div>
        <div class="flex h-40 items-end justify-between gap-2">
            <?php foreach ($bars as $i => $b): $h = (int) round((int) $b['value'] / $maxBar * 100); ?>
                <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                    <div class="w-full rounded-t-lg <?= $i === count($bars) - 1 ? 'bg-gradient-to-b from-secondary to-secondary-light' : 'bg-[#EAD9DF]' ?>" style="height: <?= max(3, $h) ?>%" title="<?= money((int) $b['value']) ?> تومان"></div>
                    <span class="text-[10px] text-[#aaa]"><?= e($b['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Low stock -->
    <div class="rounded-2xl border border-line2 bg-white p-5">
        <div class="mb-4 flex items-center gap-2 text-[14px] font-bold text-[#333]"><span>⚠️</span> هشدار موجودی کم</div>
        <?php if ($lowStock === []): ?>
            <p class="text-[12px] text-[#999]">موجودی همه محصولات کافی است. ✓</p>
        <?php else: ?>
            <div class="space-y-2.5">
                <?php foreach ($lowStock as $p): ?>
                    <a href="<?= e(url('/admin/products/' . $p['id'] . '/edit')) ?>" class="flex items-center justify-between gap-2">
                        <span class="truncate text-[12px] text-[#444]"><?= e($p['name']) ?></span>
                        <span class="flex-none rounded-lg px-2 py-1 text-[10.5px] font-bold text-white <?= (int) $p['stock'] <= 2 ? 'bg-danger' : 'bg-warning' ?> nums"><?= fa((int) $p['stock']) ?> عدد</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent orders -->
<div class="mt-5 overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="flex items-center justify-between px-5 py-4">
        <div class="text-[14px] font-bold text-[#333]">سفارش‌های اخیر</div>
        <a href="<?= e(url('/admin/orders')) ?>" class="text-[12px] font-semibold text-secondary">مشاهده همه ›</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[560px] text-[12.5px]">
            <thead>
                <tr class="border-y border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">سفارش</th>
                    <th class="p-3 text-right font-semibold">مشتری</th>
                    <th class="p-3 font-semibold">مبلغ</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">تاریخ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $o): ?>
                    <tr class="border-b border-line2 last:border-0">
                        <td class="p-3"><a href="<?= e(url('/admin/orders/' . $o['id'])) ?>" class="font-bold text-secondary nums"><?= e($o['order_number']) ?></a></td>
                        <td class="p-3 text-[#444]"><?= e($o['receiver_name'] ?: '—') ?></td>
                        <td class="p-3 text-center font-bold text-[#333] nums"><?= money((int) $o['total']) ?></td>
                        <td class="p-3 text-center"><?php $this->partial('admin/order-badge', ['status' => $o['status'], 'payment' => $o['payment_status']]); ?></td>
                        <td class="p-3 text-center text-[#999] nums"><?= jdate((string) $o['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($recentOrders === []): ?>
                    <tr><td colspan="5" class="p-6 text-center text-[#999]">هنوز سفارشی ثبت نشده است.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
