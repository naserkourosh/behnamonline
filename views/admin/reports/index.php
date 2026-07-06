<?php
/** @var string $from @var string $to */
/** @var int $revenue @var int $paidOrders @var int $allOrders @var int $itemsSold @var int $couponGiven @var int $shipping @var int $newCustomers @var int $aov */
/** @var float $conversion */
/** @var list<array<string,mixed>> $series @var list<array<string,mixed>> $topProducts @var list<array<string,mixed>> $topCategories @var list<array<string,mixed>> $statusRows @var list<array<string,mixed>> $paymentRows */
/** @var array<string,string> $presets @var string $preset */

$statusLabels = [
    'pending'    => ['در انتظار', 'bg-[#FFF6E6] text-warning'],
    'processing' => ['در حال پردازش', 'bg-[#EEF2FF] text-secondary'],
    'shipped'    => ['ارسال شده', 'bg-[#E6F0FF] text-[#2563eb]'],
    'delivered'  => ['تحویل شده', 'bg-[#E7F7F0] text-success'],
    'canceled'   => ['لغو شده', 'bg-[#FDECEC] text-danger'],
];

$maxBar   = max(1, ...array_map(static fn ($s) => (int) $s['revenue'], $series ?: [['revenue' => 1]]));
$maxCat   = max(1, ...array_map(static fn ($c) => (int) $c['revenue'], $topCategories ?: [['revenue' => 1]]));
$maxProd  = max(1, ...array_map(static fn ($p) => (int) $p['revenue'], $topProducts ?: [['revenue' => 1]]));
$statusN  = max(1, array_sum(array_map(static fn ($s) => (int) $s['n'], $statusRows)));

$kpis = [
    ['فروش کل (ت)', money($revenue), 'bg-[#E7F7F0]', '💰'],
    ['سفارش موفق', fa($paidOrders), 'bg-pink', '✅'],
    ['میانگین سبد (ت)', money($aov), 'bg-[#EEF2FF]', '🧾'],
    ['اقلام فروخته‌شده', fa($itemsSold), 'bg-[#FFF6E6]', '📦'],
    ['مشتری جدید', fa($newCustomers), 'bg-[#F0E9FF]', '👤'],
    ['نرخ پرداخت', fa((string) $conversion) . '٪', 'bg-[#E6F7FF]', '📈'],
    ['تخفیف اعمال‌شده (ت)', money($couponGiven), 'bg-[#FDECEC]', '🎟️'],
    ['هزینه ارسال (ت)', money($shipping), 'bg-surface', '🚚'],
];
$link = 'rounded-xl2 border px-3.5 py-2 text-[12px] font-semibold transition';
?>
<!-- Range filter -->
<form method="get" action="<?= e(url('/admin/reports')) ?>" class="mb-5 rounded-2xl border border-line2 bg-white p-4">
    <div class="flex flex-wrap items-end gap-2.5">
        <div class="flex flex-wrap gap-2">
            <?php foreach ($presets as $days => $label):
                $active = empty($_GET['from']) && (($preset === (string) $days) || (!isset($_GET['range']) && $days === '30')); ?>
                <a href="<?= e(url('/admin/reports?range=' . $days)) ?>" class="<?= $link ?> <?= $active ? 'border-secondary bg-secondary text-white' : 'border-line text-[#666] hover:border-secondary' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>
        <div class="ms-auto flex flex-wrap items-end gap-2">
            <div><label class="mb-1 block text-[11px] text-[#888]">از تاریخ</label><input name="from" value="<?= e($from) ?>" dir="ltr" class="js-jdate rounded-xl2 border border-line px-3 py-2 text-[12px] outline-none focus:border-secondary"></div>
            <div><label class="mb-1 block text-[11px] text-[#888]">تا تاریخ</label><input name="to" value="<?= e($to) ?>" dir="ltr" class="js-jdate rounded-xl2 border border-line px-3 py-2 text-[12px] outline-none focus:border-secondary"></div>
            <button class="btn-primary px-5 py-2 text-[12.5px]">اعمال</button>
        </div>
    </div>
    <div class="mt-2.5 text-[11px] text-[#999] nums">بازه: <?= jdate($from . ' 00:00:00') ?> تا <?= jdate($to . ' 00:00:00') ?></div>
</form>

<!-- KPI cards -->
<div class="grid grid-cols-2 gap-3.5 md:grid-cols-4">
    <?php foreach ($kpis as [$label, $value, $tint, $icon]): ?>
        <div class="rounded-2xl border border-line2 bg-white p-4">
            <div class="mb-2.5 flex h-9 w-9 items-center justify-center rounded-xl2 <?= $tint ?> text-[15px]"><?= $icon ?></div>
            <div class="text-[18px] font-extrabold text-[#333] nums"><?= $value ?></div>
            <div class="mt-0.5 text-[11px] text-[#999]"><?= e($label) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Sales trend -->
<div class="mt-5 rounded-2xl border border-line2 bg-white p-5">
    <div class="mb-5 text-[14px] font-bold text-[#333]">روند فروش</div>
    <?php if ($series === []): ?>
        <p class="py-8 text-center text-[12px] text-[#999]">در این بازه فروش پرداخت‌شده‌ای ثبت نشده است.</p>
    <?php else: ?>
        <div class="hscroll overflow-x-auto">
            <div class="flex h-44 items-end gap-1.5" style="min-width: <?= max(100, count($series) * 26) ?>px">
                <?php foreach ($series as $s): $h = (int) round((int) $s['revenue'] / $maxBar * 100); ?>
                    <div class="flex h-full min-w-[20px] flex-1 flex-col items-center justify-end gap-1.5">
                        <div class="w-full rounded-t-md bg-gradient-to-b from-secondary to-secondary-light" style="height: <?= max(2, $h) ?>%" title="<?= jdate((string) $s['d'] . ' 00:00:00') ?> — <?= money((int) $s['revenue']) ?> ت (<?= fa((int) $s['orders']) ?> سفارش)"></div>
                        <span class="whitespace-nowrap text-[8.5px] text-[#bbb] nums"><?= jdate((string) $s['d'], 'n/j') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="mt-5 grid gap-5 lg:grid-cols-2">
    <!-- Top products -->
    <div class="overflow-hidden rounded-2xl border border-line2 bg-white">
        <div class="px-5 py-4 text-[14px] font-bold text-[#333]">پرفروش‌ترین محصولات</div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[420px] text-[12.5px]">
                <thead><tr class="border-y border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">محصول</th>
                    <th class="p-3 font-semibold">تعداد</th>
                    <th class="p-3 font-semibold">فروش (ت)</th>
                </tr></thead>
                <tbody>
                <?php foreach ($topProducts as $p): ?>
                    <tr class="border-b border-line2 last:border-0">
                        <td class="max-w-[220px] truncate p-3 text-[#444]"><?php if ($p['product_id'] && admin_can('products')): ?><a href="<?= e(url('/admin/products/' . $p['product_id'] . '/edit')) ?>" class="hover:text-secondary"><?= e($p['name']) ?></a><?php else: ?><?= e($p['name']) ?><?php endif; ?></td>
                        <td class="p-3 text-center font-semibold text-[#333] nums"><?= fa((int) $p['qty']) ?></td>
                        <td class="p-3 text-center font-bold text-secondary nums"><?= money((int) $p['revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($topProducts === []): ?><tr><td colspan="3" class="p-6 text-center text-[#999]">داده‌ای نیست.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top categories -->
    <div class="rounded-2xl border border-line2 bg-white p-5">
        <div class="mb-4 text-[14px] font-bold text-[#333]">فروش بر اساس دسته</div>
        <?php if ($topCategories === []): ?>
            <p class="py-6 text-center text-[12px] text-[#999]">داده‌ای نیست.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($topCategories as $c): $w = (int) round((int) $c['revenue'] / $maxCat * 100); ?>
                    <div>
                        <div class="mb-1 flex items-center justify-between text-[12px]">
                            <span class="text-[#555]"><?= e($c['name']) ?></span>
                            <span class="font-bold text-[#333] nums"><?= money((int) $c['revenue']) ?> ت</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-surface"><div class="h-full rounded-full bg-primary" style="width: <?= max(3, $w) ?>%"></div></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-5 grid gap-5 lg:grid-cols-2">
    <!-- Status breakdown -->
    <div class="rounded-2xl border border-line2 bg-white p-5">
        <div class="mb-4 text-[14px] font-bold text-[#333]">وضعیت سفارش‌ها</div>
        <div class="space-y-2.5">
            <?php foreach ($statusRows as $s):
                [$label, $cls] = $statusLabels[(string) $s['status']] ?? [(string) $s['status'], 'bg-surface text-[#666]'];
                $pct = (int) round((int) $s['n'] / $statusN * 100); ?>
                <div class="flex items-center justify-between">
                    <span class="rounded-lg px-2 py-1 text-[11px] font-bold <?= $cls ?>"><?= e($label) ?></span>
                    <span class="text-[12px] text-[#666] nums"><?= fa((int) $s['n']) ?> سفارش · <?= fa($pct) ?>٪</span>
                </div>
            <?php endforeach; ?>
            <?php if ($statusRows === []): ?><p class="text-center text-[12px] text-[#999]">داده‌ای نیست.</p><?php endif; ?>
        </div>
    </div>

    <!-- Payment methods -->
    <div class="rounded-2xl border border-line2 bg-white p-5">
        <div class="mb-4 text-[14px] font-bold text-[#333]">روش پرداخت (سفارش‌های موفق)</div>
        <div class="space-y-2.5">
            <?php foreach ($paymentRows as $p): ?>
                <div class="flex items-center justify-between text-[12.5px]">
                    <span class="text-[#555]"><?= e($p['method']) ?></span>
                    <span class="text-[#666] nums"><?= fa((int) $p['n']) ?> سفارش · <span class="font-bold text-secondary"><?= money((int) $p['revenue']) ?> ت</span></span>
                </div>
            <?php endforeach; ?>
            <?php if ($paymentRows === []): ?><p class="text-center text-[12px] text-[#999]">داده‌ای نیست.</p><?php endif; ?>
        </div>
    </div>
</div>
