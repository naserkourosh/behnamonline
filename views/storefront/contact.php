<?php
$brand   = (string) setting('brand_name', 'بهنام');
$phone   = trim((string) setting('contact_phone', ''));
$email   = trim((string) setting('contact_email', ''));
$insta   = trim((string) ltrim((string) setting('contact_instagram', ''), '@'));
$address = trim((string) setting('contact_address', ''));
$this->meta([
    'title'       => 'تماس با ما | ' . $brand,
    'description' => 'راه‌های ارتباط با فروشگاه ' . $brand . ' — تلفن، اینستاگرام، گفتگوی آنلاین و فرم تماس.',
]);
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
?>
<div class="container-page py-8 md:py-12">
    <nav class="mb-5 text-[11px] text-mauve"><a href="<?= e(url('/')) ?>" class="hover:text-secondary">خانه</a> <span class="mx-1">/</span> <span class="text-[#777]">تماس با ما</span></nav>

    <h1 class="section-title mb-6 text-[18px] md:text-[24px]">تماس با ما</h1>

    <div class="grid gap-6 md:grid-cols-2">
        <!-- contact info -->
        <div class="space-y-3.5">
            <?php if ($phone !== ''): ?>
                <a href="tel:<?= e(en_num($phone)) ?>" class="flex items-center gap-3.5 rounded-2xl border border-line2 bg-white p-4 transition hover:border-secondary">
                    <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl2 bg-pink text-[19px]">📞</span>
                    <span><span class="block text-[11px] text-[#999]">تلفن تماس</span><span class="text-[14px] font-bold text-secondary nums" dir="ltr"><?= e($phone) ?></span></span>
                </a>
            <?php endif; ?>
            <?php if ($insta !== ''): ?>
                <a href="https://instagram.com/<?= e($insta) ?>" target="_blank" rel="noopener" class="flex items-center gap-3.5 rounded-2xl border border-line2 bg-white p-4 transition hover:border-secondary">
                    <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl2 bg-pink text-[19px]">📷</span>
                    <span><span class="block text-[11px] text-[#999]">اینستاگرام</span><span class="text-[14px] font-bold text-secondary" dir="ltr">@<?= e($insta) ?></span></span>
                </a>
            <?php endif; ?>
            <?php if ($email !== ''): ?>
                <a href="mailto:<?= e($email) ?>" class="flex items-center gap-3.5 rounded-2xl border border-line2 bg-white p-4 transition hover:border-secondary">
                    <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl2 bg-pink text-[19px]">✉️</span>
                    <span><span class="block text-[11px] text-[#999]">ایمیل</span><span class="text-[14px] font-bold text-secondary" dir="ltr"><?= e($email) ?></span></span>
                </a>
            <?php endif; ?>
            <?php if ($address !== ''): ?>
                <div class="flex items-start gap-3.5 rounded-2xl border border-line2 bg-white p-4">
                    <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl2 bg-pink text-[19px]">📍</span>
                    <span><span class="block text-[11px] text-[#999]">آدرس</span><span class="text-[13px] font-semibold leading-7 text-[#555]"><?= nl2br(e($address)) ?></span></span>
                </div>
            <?php endif; ?>
            <div class="rounded-2xl border border-line2 bg-surface p-4 text-[12px] leading-7 text-[#777]">
                💬 سریع‌ترین راه پاسخ‌گویی، <b>گفتگوی آنلاین</b> است — از دکمهٔ شناور پایین صفحه استفاده کنید یا فرم روبه‌رو را بفرستید.
            </div>
        </div>

        <!-- contact form → live-chat inbox -->
        <form method="post" action="<?= e(url('/contact')) ?>" class="rounded-2xl border border-line2 bg-white p-5 md:p-6">
            <?= csrf_field() ?>
            <h2 class="mb-4 text-[15px] font-bold text-[#333]">ارسال پیام</h2>
            <div class="mb-3"><label class="<?= $lbl ?>">نام شما *</label><input name="name" required class="<?= $inp ?>"></div>
            <div class="mb-3"><label class="<?= $lbl ?>">شماره موبایل</label><input name="mobile" dir="ltr" placeholder="0912…" class="<?= $inp ?> text-left"></div>
            <div class="mb-4"><label class="<?= $lbl ?>">متن پیام *</label><textarea name="message" rows="5" required class="<?= $inp ?>" placeholder="پیام، سوال یا پیشنهاد شما…"></textarea></div>
            <button class="btn-primary w-full py-3 text-[13.5px]">ارسال پیام</button>
            <p class="mt-2 text-[10.5px] text-[#aaa]">پیام شما مستقیم به تیم پشتیبانی می‌رسد و از طریق گفتگوی آنلاین همین سایت پاسخ داده می‌شود.</p>
        </form>
    </div>
</div>
