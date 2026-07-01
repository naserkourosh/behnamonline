<?php
/** @var array<string,mixed> $summary */
/** @var list<array<string,mixed>> $recently */

$this->meta(['title' => 'سبد خرید | بهنام', 'robots' => 'noindex, follow']);
$items = $summary['items'];
?>

<div class="container-page py-5 md:py-8">
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-[18px] font-bold text-secondary md:text-[24px]">سبد خرید <span class="js-cart-title-count nums">(<?= fa((int) $summary['count']) ?>)</span></h1>
        <?php if ($items !== []): ?><span class="text-[11.5px] text-mauve">پاک کردن همه</span><?php endif; ?>
    </div>

    <?php if ($items === []): ?>
        <div class="rounded-3xl border border-line2 bg-surface py-20 text-center">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-white text-secondary">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg>
            </div>
            <p class="text-[14px] text-[#666]">سبد خرید شما خالی است.</p>
            <a href="<?= e(url('/category')) ?>" class="btn-primary mt-5 px-7 py-3 text-[13px]">شروع خرید</a>
        </div>
    <?php else: ?>
        <div class="md:flex md:items-start md:gap-8">
            <div class="md:flex-1">
                <!-- free shipping progress -->
                <div class="mb-4 rounded-2xl border border-line bg-white p-4">
                    <div class="js-ship-msg mb-2.5 text-[12px] text-[#444]">
                        <?php if ($summary['qualifies_free']): ?>
                            🎉 سفارش شما شامل ارسال رایگان است
                        <?php else: ?>
                            <?= money((int) $summary['free_remaining']) ?> تومان تا ارسال رایگان باقی مانده
                        <?php endif; ?>
                    </div>
                    <div class="h-[7px] overflow-hidden rounded-full bg-line">
                        <div class="js-ship-bar h-full rounded-full bg-gradient-to-l from-primary to-secondary transition-all" style="width: <?= (int) $summary['free_progress'] ?>%"></div>
                    </div>
                </div>

                <!-- items -->
                <div id="js-cart-items" class="flex flex-col gap-3">
                    <?php foreach ($items as $it): ?>
                        <div class="js-cart-row flex gap-3.5 rounded-2xl border border-line2 bg-white p-3" data-id="<?= (int) $it['id'] ?>">
                            <a href="<?= e(url('/product/' . $it['slug'])) ?>" class="aspect-[7/8] w-20 flex-none overflow-hidden rounded-xl2 bg-[#F3EBE2]">
                                <img src="<?= e(asset((string) $it['image'])) ?>" alt="<?= e($it['image_alt']) ?>" loading="lazy" class="h-full w-full object-cover">
                            </a>
                            <div class="flex flex-1 flex-col">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="mb-1 text-[9.5px] text-mauve"><?= e($it['brand_name']) ?></div>
                                        <a href="<?= e(url('/product/' . $it['slug'])) ?>" class="text-[12.5px] font-semibold leading-6 text-[#333]"><?= e($it['name']) ?></a>
                                    </div>
                                    <button type="button" class="js-cart-remove text-[#ccc] transition hover:text-danger" aria-label="حذف">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7h16M9 7V5h6v2M6 7l1 13h10l1-13" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                <?php if (!empty($it['variant_label'])): ?>
                                    <div class="mt-1 text-[10px] text-[#999]"><?= e($it['variant_label']) ?></div>
                                <?php endif; ?>
                                <div class="mt-auto flex items-center justify-between pt-2">
                                    <div class="flex items-center overflow-hidden rounded-xl2 bg-surface">
                                        <button type="button" class="js-cart-dec flex h-8 w-8 items-center justify-center text-[17px] text-secondary">−</button>
                                        <span class="js-row-qty w-7 text-center text-[13px] font-bold text-[#333] nums" data-qty="<?= (int) $it['qty'] ?>"><?= fa((int) $it['qty']) ?></span>
                                        <button type="button" class="js-cart-inc flex h-8 w-8 items-center justify-center text-[17px] text-secondary">+</button>
                                    </div>
                                    <div><span class="js-line-total text-[14px] font-extrabold text-secondary nums"><?= money((int) $it['line_total']) ?></span> <span class="text-[9px] text-[#999]">تومان</span></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- coupon -->
                <form class="js-coupon mt-4 flex gap-2.5 rounded-2xl border border-line2 bg-white p-3">
                    <input class="flex-1 rounded-xl border border-line bg-surface px-3.5 py-2.5 text-[12px] outline-none" placeholder="کد تخفیف یا کارت هدیه">
                    <button type="submit" class="rounded-xl bg-secondary px-5 text-[12.5px] font-bold text-white">ثبت</button>
                </form>
            </div>

            <!-- summary -->
            <aside class="mt-5 md:mt-0 md:w-80 md:flex-none">
                <div class="rounded-2xl border border-line2 bg-white p-4 md:sticky md:top-44">
                    <div class="mb-3 flex justify-between text-[12.5px] text-[#666]"><span>جمع کالاها</span><span class="js-sum-gross nums"><?= money((int) $summary['gross']) ?> تومان</span></div>
                    <div class="mb-3 flex justify-between text-[12.5px] text-success"><span>تخفیف</span><span class="js-sum-savings nums">− <?= money((int) $summary['savings']) ?> تومان</span></div>
                    <div class="mb-3 flex justify-between text-[12.5px] text-[#666]"><span>هزینه ارسال</span><span class="js-sum-shipping"><?= ((int) $summary['shipping']) === 0 ? '<span class="text-success">رایگان</span>' : money((int) $summary['shipping']) . ' تومان' ?></span></div>
                    <div class="my-3 h-px bg-line"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-[14px] font-bold text-[#333]">مبلغ قابل پرداخت</span>
                        <span><span class="js-sum-total text-[18px] font-extrabold text-secondary nums"><?= money((int) $summary['total']) ?></span> <span class="text-[11px] text-[#999]">تومان</span></span>
                    </div>
                    <a href="<?= e(url('/checkout')) ?>" class="btn-primary mt-5 hidden w-full py-3.5 text-[14px] md:flex">ادامه و ثبت سفارش</a>
                </div>
            </aside>
        </div>
    <?php endif; ?>

    <!-- recently viewed -->
    <?php if ($recently !== []): ?>
        <section class="mt-9">
            <h2 class="section-title mb-4">بازدیدهای اخیر</h2>
            <div class="hscroll -mx-4 flex gap-3.5 overflow-x-auto px-4 pb-2 md:mx-0 md:px-0">
                <?php foreach ($recently as $product): ?>
                    <div class="w-[140px] flex-none md:w-[190px]"><?php $this->partial('product-card', ['product' => $product]); ?></div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<!-- mobile sticky checkout -->
<?php if ($items !== []): ?>
<div class="fixed bottom-0 left-1/2 z-50 flex w-full max-w-mobile -translate-x-1/2 items-center gap-3 border-t border-line bg-white px-4 pb-4 pt-3 shadow-nav md:hidden">
    <div>
        <div class="text-[9.5px] text-[#999]">قابل پرداخت</div>
        <div class="js-sum-total-mobile text-[16px] font-extrabold text-secondary nums"><?= money((int) $summary['total']) ?></div>
    </div>
    <a href="<?= e(url('/checkout')) ?>" class="btn-primary flex-1 py-3.5 text-[14px]">ادامه و ثبت سفارش</a>
</div>
<?php endif; ?>
