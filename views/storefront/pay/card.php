<?php
/** @var array<string,mixed> $order */
/** @var array<string,mixed> $card */
$this->meta(['title' => 'پرداخت کارت به کارت | بهنام', 'robots' => 'noindex']);
?>
<section class="container-page py-8 md:py-12">
    <div class="mx-auto max-w-md">
        <div class="rounded-3xl border border-line2 bg-white p-6">
            <h1 class="text-[16px] font-bold text-secondary">پرداخت کارت به کارت</h1>
            <p class="mt-2 text-[12px] leading-7 text-[#888]">مبلغ <span class="font-bold text-secondary nums"><?= money((int) $order['total']) ?> تومان</span> را به کارت زیر واریز کنید و سپس شماره پیگیری/مرجع تراکنش را وارد نمایید.</p>

            <div class="mt-4 rounded-2xl bg-gradient-to-br from-secondary to-secondary-light p-5 text-white">
                <div class="text-[11px] opacity-80"><?= e($card['bank'] ?? '') ?></div>
                <div class="mt-3 text-[20px] font-bold tracking-widest nums" dir="ltr"><?= e($card['number'] ?? '') ?></div>
                <div class="mt-3 text-[12px] opacity-90">به نام: <?= e($card['holder'] ?? '') ?></div>
            </div>

            <form method="post" action="<?= e(url('/pay/card/' . $order['id'])) ?>" class="mt-5">
                <?= csrf_field() ?>
                <label class="mb-1.5 block text-[12px] text-[#888]">شماره پیگیری / مرجع تراکنش</label>
                <input name="reference" inputmode="numeric" dir="ltr" class="ck-input text-left" placeholder="مثلاً ۱۲۳۴۵۶">
                <button type="submit" class="btn-primary mt-4 w-full py-3.5 text-[14px]">ثبت رسید پرداخت</button>
            </form>
            <p class="mt-4 text-center text-[11px] leading-6 text-[#aaa]">پس از بررسی و تایید پرداخت توسط پشتیبانی، سفارش شما پردازش و ارسال خواهد شد.</p>
        </div>
    </div>
</section>
