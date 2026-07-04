<?php
/** @var array<string,mixed> $summary */
/** @var array<string,mixed> $provinces */
/** @var array<string,mixed> $prefill */

$this->meta(['title' => 'تکمیل سفارش | بهنام', 'robots' => 'noindex']);

$checkoutConfig = [
    'geo'          => $provinces,
    'net'          => (int) $summary['subtotal'],
    'freeThreshold'=> (int) $summary['free_threshold'],
    'defaultCost'  => (int) config('shipping.default_cost', 45000),
    'cityRules'    => config('shipping.city_rules', []),
    'prefillCity'  => (string) ($prefill['city'] ?? ''),
];
$field = static fn (string $k): string => e((string) ($prefill[$k] ?? ''));
?>
<section id="checkout-page" class="container-page py-6 md:py-10" data-net="<?= (int) $summary['subtotal'] ?>">
    <!-- stepper -->
    <div class="mb-7 flex items-center justify-center gap-2">
        <?php $steps = ['اطلاعات ارسال', 'پرداخت']; foreach ($steps as $i => $label): ?>
            <div class="flex items-center gap-2">
                <div class="js-step-dot flex h-7 w-7 items-center justify-center rounded-full border text-[12px] font-bold nums <?= $i === 0 ? 'border-transparent bg-secondary text-white' : 'border-[#E0CDD3] bg-white text-[#bbb]' ?>" data-step="<?= $i ?>"><?= fa($i + 1) ?></div>
                <span class="text-[11px] font-semibold text-[#bbb]"><?= e($label) ?></span>
            </div>
            <?php if ($i < count($steps) - 1): ?><span class="h-px w-5 bg-[#E0CDD3]"></span><?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- ── STEP 0: shipping info ─────────────────────────────── -->
    <div id="ck-step-info" class="md:flex md:items-start md:gap-8">
        <form id="ck-form" class="space-y-3.5 md:flex-1" onsubmit="return false">
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <div class="mb-4 text-[14px] font-bold text-secondary">اطلاعات ارسال</div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-[11px] text-[#888]">نام</label>
                        <input name="first_name" value="<?= $field('first_name') ?>" class="ck-input" placeholder="نام">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] text-[#888]">نام خانوادگی</label>
                        <input name="last_name" value="<?= $field('last_name') ?>" class="ck-input" placeholder="نام خانوادگی">
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-[11px] text-[#888]">استان</label>
                        <select name="province" id="ck-province" class="ck-input">
                            <option value="">انتخاب استان</option>
                            <?php foreach (array_keys((array) $provinces) as $prov): ?>
                                <option value="<?= e($prov) ?>" <?= ($prefill['province'] ?? '') === $prov ? 'selected' : '' ?>><?= e($prov) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] text-[#888]">شهر</label>
                        <select name="city" id="ck-city" class="ck-input"><option value="">ابتدا استان</option></select>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="mb-1.5 block text-[11px] text-[#888]">آدرس کامل</label>
                    <textarea name="address" rows="2" class="ck-input resize-none" placeholder="خیابان، کوچه، پلاک، واحد"><?= $field('address') ?></textarea>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-[11px] text-[#888]">کد پستی <span class="text-[#bbb]">(اختیاری)</span></label>
                        <input name="postal_code" inputmode="numeric" value="<?= $field('postal_code') ?>" class="ck-input" placeholder="۱۰ رقم">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] text-[#888]">تلفن همراه</label>
                        <input name="mobile" inputmode="numeric" dir="ltr" value="<?= $field('mobile') ?>" class="ck-input text-left" placeholder="09xxxxxxxxx">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="mb-1.5 block text-[11px] text-[#888]">توضیحات <span class="text-[#bbb]">(اختیاری)</span></label>
                    <textarea name="note" rows="2" class="ck-input resize-none" placeholder="یادداشت برای سفارش یا ارسال (اختیاری)"><?= $field('note') ?></textarea>
                </div>
            </div>

            <div class="rounded-2xl border border-line2 bg-white p-5">
                <div class="mb-3.5 text-[14px] font-bold text-secondary">روش ارسال</div>
                <div id="ck-shipping" class="space-y-2.5">
                    <p class="text-[12px] text-[#999]">برای نمایش روش‌های ارسال، استان و شهر را انتخاب کنید.</p>
                </div>
            </div>

            <div class="rounded-2xl border border-line2 bg-white p-5">
                <div class="mb-3.5 text-[14px] font-bold text-secondary">روش پرداخت</div>
                <div class="grid grid-cols-2 gap-2.5">
                    <?php foreach (['zarinpal' => 'زرین‌پال', 'card' => 'کارت به کارت', 'snappay' => 'اسنپ‌پی', 'digipay' => 'دیجی‌پی'] as $k => $label): ?>
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-xl2 border border-line p-3 text-[12px] font-semibold text-[#444] has-[:checked]:border-secondary has-[:checked]:bg-pink">
                            <input type="radio" name="payment_method" value="<?= $k ?>" class="accent-secondary" <?= $k === 'zarinpal' ? 'checked' : '' ?>>
                            <?= e($label) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <p class="px-4 text-center text-[10.5px] leading-7 text-[#999]">با ثبت سفارش، کد تایید به شماره موبایل شما پیامک می‌شود و حساب کاربری‌تان به‌صورت خودکار ساخته خواهد شد.</p>
        </form>

        <!-- order summary (desktop) -->
        <aside class="mt-4 md:mt-0 md:w-80 md:flex-none">
            <div class="rounded-2xl border border-line2 bg-white p-5 md:sticky md:top-44">
                <div class="mb-3 text-[14px] font-bold text-secondary">خلاصه سفارش</div>
                <div class="mb-2.5 flex justify-between text-[12.5px] text-[#666]"><span>جمع کالاها</span><span class="nums"><?= money((int) $summary['gross']) ?> تومان</span></div>
                <div class="mb-2.5 flex justify-between text-[12.5px] text-success"><span>تخفیف</span><span class="nums">− <?= money((int) $summary['savings']) ?> تومان</span></div>
                <div class="mb-2.5 flex justify-between text-[12.5px] text-[#666]"><span>هزینه ارسال</span><span class="js-ck-ship-cost text-[11px] text-mauve">محاسبه پس از ثبت آدرس</span></div>
                <div class="my-3 h-px bg-line"></div>
                <div class="flex items-center justify-between">
                    <span class="text-[14px] font-bold">قابل پرداخت</span>
                    <span><span class="js-ck-total text-[18px] font-extrabold text-secondary nums"><?= money((int) $summary['subtotal']) ?></span> <span class="text-[11px] text-[#999]">تومان</span></span>
                </div>
                <button type="button" class="js-ck-send btn-primary mt-5 hidden w-full py-3.5 text-[14px] md:flex">ثبت سفارش و ادامه پرداخت</button>
            </div>
        </aside>
    </div>

    <!-- mobile sticky send bar -->
    <div id="ck-info-bar" class="fixed bottom-0 left-1/2 z-50 flex w-full max-w-mobile -translate-x-1/2 items-center gap-3 border-t border-line bg-white px-4 pb-4 pt-3 shadow-nav md:hidden">
        <div>
            <div class="text-[9.5px] text-[#999]">قابل پرداخت</div>
            <div class="js-ck-total text-[16px] font-extrabold text-secondary nums"><?= money((int) $summary['subtotal']) ?></div>
        </div>
        <button type="button" class="js-ck-send btn-primary flex-1 py-3.5 text-[14px]">ثبت و ادامه پرداخت</button>
    </div>

    <script type="application/json" id="checkout-config"><?= json_encode($checkoutConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</section>
