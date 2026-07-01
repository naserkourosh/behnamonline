<?php
/** @var list<array<string,mixed>> $orders */
$this->meta(['title' => 'سفارش‌های من | بهنام', 'robots' => 'noindex']);
?>
<div class="container-page py-6 md:py-8">
    <div class="mb-4 flex items-center gap-2">
        <a href="<?= e(url('/account')) ?>" class="text-secondary"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <h1 class="text-[18px] font-bold text-secondary md:text-[22px]">سفارش‌های من</h1>
    </div>

    <div class="md:flex md:items-start md:gap-8">
        <div class="md:flex-1">
            <?php if ($orders === []): ?>
                <div class="rounded-2xl border border-line2 bg-surface py-16 text-center">
                    <p class="text-[14px] text-[#666]">هنوز سفارشی ثبت نکرده‌اید.</p>
                    <a href="<?= e(url('/category')) ?>" class="btn-primary mt-5 px-7 py-3 text-[13px]">شروع خرید</a>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($orders as $o): $st = order_status((string) $o['status']); ?>
                        <a href="<?= e(url('/account/orders/' . $o['id'])) ?>" class="block rounded-2xl border border-line2 bg-white p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-[12px] text-[#999]">سفارش <span class="font-bold text-[#333] nums"><?= e($o['order_number']) ?></span></div>
                                <span class="badge <?= $st['bg'] ?> <?= $st['text'] ?>"><?= e($st['label']) ?></span>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <div class="text-[10.5px] text-[#999]"><?= jdate((string) $o['created_at']) ?> · <?= fa((int) $o['item_count']) ?> قلم کالا</div>
                                <div class="text-[14px] font-extrabold text-secondary nums"><?= money((int) $o['total']) ?> <span class="text-[9px] text-[#999]">تومان</span></div>
                            </div>
                            <?php if (!empty($o['tracking_code'])): ?>
                                <div class="mt-3 rounded-xl2 bg-surface px-3 py-2 text-[10.5px] text-[#888]">کد رهگیری: <span class="font-bold text-secondary nums" dir="ltr"><?= e($o['tracking_code']) ?></span></div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <aside class="mt-6 hidden md:mt-0 md:block md:w-72 md:flex-none"><?php $this->partial('account-nav'); ?></aside>
    </div>
</div>
