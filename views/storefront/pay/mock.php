<?php
/** @var array<string,mixed> $order */
/** @var string $authority */
/** @var string $gateway */
$this->meta(['title' => 'درگاه پرداخت آزمایشی | بهنام', 'robots' => 'noindex']);
$ok  = url('/pay/callback?order=' . $order['id'] . '&authority=' . urlencode($authority) . '&status=OK');
$nok = url('/pay/callback?order=' . $order['id'] . '&authority=' . urlencode($authority) . '&status=NOK');
?>
<section class="container-page py-10 md:py-16">
    <div class="mx-auto max-w-md overflow-hidden rounded-3xl border border-line2 bg-white">
        <div class="flex items-center justify-between bg-gradient-to-l from-secondary to-secondary-light px-5 py-4 text-white">
            <div class="text-[14px] font-bold">درگاه پرداخت آزمایشی</div>
            <div class="text-[11px] opacity-80">حالت توسعه</div>
        </div>
        <div class="p-6 text-center">
            <div class="mb-1 text-[12px] text-[#999]">مبلغ قابل پرداخت</div>
            <div class="text-[28px] font-extrabold text-secondary nums"><?= money((int) $order['total']) ?> <span class="text-[13px] text-[#999]">تومان</span></div>
            <div class="mt-3 rounded-xl2 bg-surface px-4 py-2 text-[12px] text-[#666]">سفارش <span class="font-bold text-[#333] nums"><?= e($order['order_number']) ?></span></div>
            <p class="mt-4 text-[11.5px] leading-6 text-[#aaa]">این یک درگاه شبیه‌سازی‌شده برای آزمایش است. در محیط واقعی، اینجا صفحه‌ی درگاه بانکی نمایش داده می‌شود.</p>
            <a href="<?= e($ok) ?>" class="btn-primary mt-6 w-full py-3.5 text-[14px]">پرداخت موفق</a>
            <a href="<?= e($nok) ?>" class="mt-2 block w-full rounded-2xl border border-line py-3 text-[13px] font-semibold text-[#888]">انصراف از پرداخت</a>
        </div>
    </div>
</section>
