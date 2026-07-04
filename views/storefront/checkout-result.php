<?php
/** @var array<string,mixed> $order */
/** @var bool $success */
$this->meta(['title' => ($success ? 'پرداخت موفق' : 'پرداخت ناموفق') . ' | بهنام', 'robots' => 'noindex']);
?>
<section class="container-page py-8 md:py-14">
    <div class="mx-auto max-w-md text-center">
        <?php if ($success): ?>
            <div class="mx-auto mb-5 flex h-20 w-20 animate-pop items-center justify-center rounded-full bg-[#E7F7F0]">
                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <h1 class="text-[20px] font-extrabold text-[#333]">سفارش شما ثبت شد!</h1>
            <p class="mt-2.5 text-[12.5px] leading-8 text-[#888]">پرداخت با موفقیت انجام شد و سفارش شما در حال پردازش است. ✨</p>

            <div class="mt-5 rounded-2xl border border-line2 bg-white p-5 text-right">
                <div class="mb-3 flex justify-between text-[12.5px]"><span class="text-[#999]">شماره سفارش</span><span class="font-bold text-[#333] nums"><?= e($order['order_number']) ?></span></div>
                <div class="mb-3 flex justify-between text-[12.5px]"><span class="text-[#999]">مبلغ پرداختی</span><span class="font-bold text-secondary nums"><?= money((int) $order['total']) ?> تومان</span></div>
                <div class="flex justify-between text-[12.5px]"><span class="text-[#999]">کد رهگیری پستی</span>
                    <?php if (!empty($order['tracking_code'])): ?>
                        <span class="font-bold text-secondary nums" dir="ltr"><?= e($order['tracking_code']) ?></span>
                    <?php else: ?>
                        <span class="text-[11px] font-semibold text-warning">پس از ارسال مرسوله پیامک می‌شود</span>
                    <?php endif; ?>
                </div>
            </div>
            <a href="<?= e(url('/account/orders/' . $order['id'])) ?>" class="btn-primary mt-5 w-full py-4 text-[14px]">مشاهده جزئیات سفارش</a>
            <a href="<?= e(url('/account/orders/' . $order['id'] . '/invoice')) ?>" class="mt-2 block py-2 text-[13px] font-semibold text-secondary">چاپ فاکتور</a>
            <a href="<?= e(url('/')) ?>" class="mt-1 block py-2 text-[13px] text-[#888]">بازگشت به فروشگاه</a>
        <?php else: ?>
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-[#FDECEC]">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </div>
            <h1 class="text-[20px] font-extrabold text-[#333]">پرداخت ناموفق بود</h1>
            <p class="mt-2.5 text-[12.5px] leading-8 text-[#888]">پرداخت شما تکمیل نشد. سفارش شما ثبت شده و می‌توانید دوباره تلاش کنید.</p>
            <a href="<?= e(url('/pay/' . $order['id'])) ?>" class="btn-primary mt-5 w-full py-4 text-[14px]">تلاش مجدد برای پرداخت</a>
            <a href="<?= e(url('/account/orders/' . $order['id'])) ?>" class="mt-2 block py-2 text-[13px] font-semibold text-secondary">مشاهده سفارش</a>
        <?php endif; ?>
    </div>
</section>
