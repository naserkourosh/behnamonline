<?php
/** @var array<string,mixed> $order */
/** @var list<array<string,mixed>> $items */
$this->meta(['title' => 'سفارش ' . $order['order_number'] . ' | بهنام', 'robots' => 'noindex']);
$st = order_status((string) $order['status']);
?>
<div class="container-page py-6 md:py-8">
    <div class="mb-4 flex items-center gap-2">
        <a href="<?= e(url('/account/orders')) ?>" class="text-secondary"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <h1 class="text-[17px] font-bold text-secondary md:text-[21px]">سفارش <span class="nums"><?= e($order['order_number']) ?></span></h1>
        <span class="badge <?= $st['bg'] ?> <?= $st['text'] ?> ms-auto"><?= e($st['label']) ?></span>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="space-y-3 md:col-span-2">
            <?php foreach ($items as $it): ?>
                <div class="flex gap-3.5 rounded-2xl border border-line2 bg-white p-3">
                    <div class="aspect-square w-16 flex-none overflow-hidden rounded-xl2 bg-[#F3EBE2]">
                        <img src="<?= e(asset((string) ($it['image'] ?: 'assets/images/placeholder-product.svg'))) ?>" alt="<?= e($it['name']) ?>" loading="lazy" class="h-full w-full object-cover">
                    </div>
                    <div class="flex flex-1 flex-col justify-center">
                        <div class="text-[12.5px] font-semibold text-[#333]"><?= e($it['name']) ?></div>
                        <?php if (!empty($it['variant_label'])): ?><div class="mt-0.5 text-[10px] text-[#999]"><?= e($it['variant_label']) ?></div><?php endif; ?>
                        <div class="mt-1.5 flex items-center justify-between">
                            <span class="text-[11px] text-[#999] nums"><?= fa((int) $it['qty']) ?> عدد × <?= money((int) $it['unit_price']) ?></span>
                            <span class="text-[13px] font-bold text-secondary nums"><?= money((int) $it['line_total']) ?> ت</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="rounded-2xl border border-line2 bg-white p-4">
                <div class="mb-3 text-[13px] font-bold text-secondary">آدرس تحویل</div>
                <div class="text-[12.5px] leading-7 text-[#555]">
                    <div><?= e($order['receiver_name']) ?> · <span class="nums" dir="ltr"><?= e($order['mobile']) ?></span></div>
                    <div><?= e($order['province']) ?>، <?= e($order['city']) ?> — <?= e($order['address']) ?></div>
                    <?php if (!empty($order['postal_code'])): ?><div class="text-[#999]">کد پستی: <span class="nums"><?= e($order['postal_code']) ?></span></div><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="rounded-2xl border border-line2 bg-white p-4">
                <div class="mb-3 flex justify-between text-[12.5px] text-[#666]"><span>جمع کالاها</span><span class="nums"><?= money((int) $order['subtotal']) ?> ت</span></div>
                <?php if ((int) $order['discount'] > 0): ?>
                    <div class="mb-3 flex justify-between text-[12.5px] text-success"><span>تخفیف</span><span class="nums">− <?= money((int) $order['discount']) ?> ت</span></div>
                <?php endif; ?>
                <div class="mb-3 flex justify-between text-[12.5px] text-[#666]"><span>ارسال (<?= e($order['shipping_method']) ?>)</span><span class="nums"><?= (int) $order['shipping_cost'] === 0 ? 'رایگان' : money((int) $order['shipping_cost']) . ' ت' ?></span></div>
                <div class="my-3 h-px bg-line"></div>
                <div class="flex justify-between"><span class="text-[13px] font-bold">مبلغ کل</span><span class="text-[16px] font-extrabold text-secondary nums"><?= money((int) $order['total']) ?> <span class="text-[10px] text-[#999]">ت</span></span></div>
            </div>
            <div class="rounded-2xl border border-line2 bg-white p-4 text-[12px] text-[#666]">
                <div class="mb-2 flex justify-between"><span>وضعیت پرداخت</span><span class="font-bold <?= $order['payment_status'] === 'paid' ? 'text-success' : 'text-warning' ?>"><?= $order['payment_status'] === 'paid' ? 'پرداخت شده' : 'در انتظار پرداخت' ?></span></div>
                <div class="mb-2 flex justify-between"><span>روش پرداخت</span><span><?= e($order['payment_method']) ?></span></div>
                <div class="flex justify-between"><span>کد رهگیری</span><span class="<?= empty($order['tracking_code']) ? 'text-warning' : 'text-secondary font-bold nums' ?>" dir="ltr"><?= !empty($order['tracking_code']) ? e($order['tracking_code']) : 'پس از ارسال' ?></span></div>
            </div>
            <?php if ($order['payment_status'] !== 'paid'): ?>
                <a href="<?= e(url('/pay/' . $order['id'])) ?>" class="btn-primary w-full py-3 text-[13px]">پرداخت سفارش</a>
            <?php else: ?>
                <a href="<?= e(url('/account/orders/' . $order['id'] . '/invoice')) ?>" class="btn-outline w-full py-3 text-[13px]">چاپ فاکتور</a>
            <?php endif; ?>
        </div>
    </div>
</div>
