<?php
$brand    = (string) setting('brand_name', 'بهنام');
$wordmark = (string) config('app.wordmark', 'BEHNAM');
?>
<!-- Trust badges -->
<div class="container-page flex items-center justify-around gap-3 py-6 md:py-10">
    <?php
    $trust = [
        ['ارسال سریع', '<path d="M3 7h11v8H3zM14 10h4l3 3v2h-7z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>'],
        ['ضمانت اصالت', '<path d="M12 3l8 3v6c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V6z"/><path d="M9 12l2 2 4-4" stroke-linecap="round"/>'],
        ['پرداخت امن', '<rect x="3" y="10" width="18" height="11" rx="2"/><path d="M7 10V7a5 5 0 0 1 10 0v3"/>'],
        ['۷ روز بازگشت', '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 4v4h4" stroke-linecap="round"/>'],
    ];
    foreach ($trust as $t): ?>
        <div class="text-center text-secondary">
            <svg class="mx-auto" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><?= $t[1] ?></svg>
            <div class="mt-1.5 text-[10px] text-[#666] md:text-[12px]"><?= e($t[0]) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<footer class="border-t border-line bg-surface">
    <div class="container-page py-8 md:py-12">
        <div class="grid gap-8 md:grid-cols-4">
            <div class="text-center md:text-right">
                <div class="text-[18px] font-extrabold text-secondary"><?= e($brand) ?><span class="text-gold">.</span></div>
                <div class="pr-[0.3em] text-[9px] tracking-[0.4em] text-gold"><?= e($wordmark) ?></div>
                <p class="mx-auto mt-3 max-w-xs text-[12px] leading-7 text-[#999] md:mx-0">
                    فروشگاه اینترنتی محصولات آرایشی، بهداشتی و شوینده اصل با ضمانت اصالت و ارسال سریع به سراسر کشور.
                </p>
            </div>
            <?php
            $cols = [
                'دسترسی سریع' => ['درباره ما', 'تماس با ما', 'سوالات متداول', 'وبلاگ'],
                'خدمات مشتریان' => ['پیگیری سفارش', 'شرایط بازگشت کالا', 'حریم خصوصی', 'قوانین فروشگاه'],
                'دسته‌بندی‌ها' => ['مراقبت پوست', 'آرایش', 'عطر و ادکلن', 'مراقبت مو'],
            ];
            foreach ($cols as $title => $links): ?>
                <div class="text-center md:text-right">
                    <div class="mb-3 text-[13px] font-bold text-secondary"><?= e($title) ?></div>
                    <ul class="space-y-2.5">
                        <?php foreach ($links as $l): ?>
                            <li><a href="#" class="text-[12px] text-[#777] transition hover:text-secondary"><?= e($l) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-8 border-t border-line pt-5 text-center text-[10px] text-[#c4b3a2]">
            © ۱۴۰۴ <?= e($brand) ?> — تمام حقوق محفوظ است
        </div>
    </div>
</footer>
