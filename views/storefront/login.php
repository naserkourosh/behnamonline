<?php
/** @var string $redirect */
$this->meta(['title' => 'ورود / ثبت‌نام | بهنام', 'robots' => 'noindex']);
$brand = (string) setting('brand_name', 'بهنام');
?>
<section id="login-page" class="container-page py-10 md:py-16" data-redirect="<?= e($redirect) ?>">
    <div class="mx-auto max-w-md rounded-3xl border border-line2 bg-white p-7 md:p-9">
        <div class="text-center">
            <div class="text-[24px] font-extrabold text-secondary"><?= e($brand) ?><span class="text-gold">.</span></div>
            <p class="mt-2 text-[13px] text-[#888]">ورود و ثبت‌نام تنها با شماره موبایل</p>
        </div>

        <!-- step: mobile -->
        <div id="lg-step-mobile" class="mt-7">
            <label class="mb-1.5 block text-[12px] text-[#888]">شماره موبایل</label>
            <input id="lg-mobile" inputmode="numeric" dir="ltr" class="ck-input text-left text-[15px]" placeholder="09xxxxxxxxx">
            <button type="button" class="js-lg-send btn-primary mt-4 w-full py-3.5 text-[14px]">ارسال کد تایید</button>
            <p class="mt-4 text-center text-[11px] leading-6 text-[#aaa]">با ورود، <a href="#" class="text-secondary">قوانین و مقررات</a> فروشگاه را می‌پذیرید.</p>
        </div>

        <!-- step: otp -->
        <div id="lg-step-otp" class="mt-7 hidden text-center">
            <p class="text-[12px] text-[#888]">کد ۵ رقمی به شماره زیر ارسال شد</p>
            <div class="js-lg-mobile mt-1 text-[14px] font-bold text-secondary" dir="ltr"></div>
            <div class="my-5 flex justify-center gap-2.5" dir="ltr">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <input class="js-otp-box h-14 w-12 rounded-xl2 border-2 border-line bg-white text-center text-[22px] font-bold text-secondary outline-none focus:border-secondary nums" inputmode="numeric" maxlength="1">
                <?php endfor; ?>
            </div>
            <div class="js-lg-resend text-[12px] text-[#999]"></div>
            <button type="button" class="js-lg-verify btn-primary mt-4 w-full py-3.5 text-[14px]">ورود</button>
            <button type="button" class="js-lg-change mt-2 block w-full py-2 text-[12px] text-mauve">تغییر شماره موبایل</button>
        </div>
    </div>
</section>
