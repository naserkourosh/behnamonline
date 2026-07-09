<?php
/** @var array<string,mixed> $product */
$p          = $product;
$price      = (int) $p['price'];
$old        = isset($p['old_price']) ? (int) $p['old_price'] : 0;
$discount   = discount_percent($old, $price);
$isNew      = !empty($p['is_new']);
// Per-product stock model: اتمام موجودی is absolute; otherwise کنترل موجودی
// makes the numeric count govern (shown + zero blocks); else free selling.
$oos        = !empty($p['is_out_of_stock']);
$tracked    = !$oos && !empty($p['track_stock']);
$available  = $oos ? 0 : ($tracked ? (int) $p['stock'] - (int) ($p['reserved'] ?? 0) : 9999);
$showQty    = $tracked;
$lowAt      = (int) setting('low_stock_threshold', 5);
$img        = (string) ($p['image'] ?: 'assets/images/placeholder-product.svg');
$imgAlt     = (string) ($p['image_alt'] ?: $p['name']);
$href       = url('/product/' . $p['slug']);
$inCompare  = (new \App\Services\CompareService())->has((int) $p['id']);
?>
<div class="card-rise flex h-full flex-col overflow-hidden rounded-2xl border border-line2 bg-white">
    <a href="<?= e($href) ?>" class="relative block aspect-square overflow-hidden bg-white">
        <img src="<?= e(asset($img)) ?>" alt="<?= e($imgAlt) ?>" title="<?= e($p['name']) ?>" loading="lazy" decoding="async"
             class="h-full w-full object-contain transition duration-500 hover:scale-105">
        <?php if ($discount > 0): ?>
            <span class="badge-discount absolute right-2.5 top-2.5">٪<?= fa($discount) ?></span>
        <?php elseif ($isNew): ?>
            <span class="badge-new absolute right-2.5 top-2.5">جدید</span>
        <?php endif; ?>
        <button type="button" class="js-wishlist absolute left-2.5 top-2.5 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-secondary" data-id="<?= (int) $p['id'] ?>" aria-label="افزودن به علاقه‌مندی">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <button type="button" class="js-compare absolute left-2.5 top-[42px] flex h-7 w-7 items-center justify-center rounded-full bg-white/90 <?= $inCompare ? 'text-white bg-secondary' : 'text-secondary' ?>" data-id="<?= (int) $p['id'] ?>" aria-label="افزودن به مقایسه" title="مقایسه">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 16V4M7 4L3 8M7 4l4 4M17 8v12M17 20l4-4M17 20l-4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <?php if (flash_active($p)): ?>
            <div class="js-flash-cd absolute inset-x-2 bottom-2 flex items-center justify-center gap-1 rounded-lg bg-secondary/90 py-1 text-[10px] font-bold text-white backdrop-blur" data-countdown="<?= (int) strtotime((string) $p['flash_sale_ends_at']) ?>">
                <span>⚡</span><span class="js-cd-text nums" dir="ltr">--:--:--</span>
            </div>
        <?php endif; ?>
    </a>
    <div class="flex flex-1 flex-col p-3">
        <div class="mb-1 text-[9.5px] text-mauve md:text-[10.5px]"><?= e($p['brand_name'] ?? '') ?></div>
        <a href="<?= e($href) ?>" class="clamp-2 min-h-[34px] text-[12px] font-semibold leading-6 text-[#333] md:min-h-[40px] md:text-[13.5px]"><?= e($p['name']) ?></a>
        <div class="my-2 flex items-center gap-1.5">
            <span class="text-[11px] text-star">★</span>
            <span class="text-[10px] text-[#888] nums md:text-[11.5px]"><?= fa(number_format((float) $p['rating_avg'], 1)) ?></span>
            <?php if ($available > 0 && $showQty && $available <= $lowAt): ?>
                <span class="me-1 text-[9.5px] text-warning">تنها <?= fa($available) ?> عدد</span>
            <?php elseif ($available <= 0): ?>
                <span class="me-1 text-[9.5px] text-danger">اتمام موجودی</span>
            <?php endif; ?>
        </div>
        <div class="mt-auto">
            <?php if ($discount > 0): ?>
                <div class="text-[10px] text-[#bbb] line-through nums"><?= money($old) ?></div>
            <?php endif; ?>
            <div class="flex items-end justify-between">
                <div>
                    <span class="text-[13.5px] font-extrabold text-secondary nums md:text-[16px]"><?= money($price) ?></span>
                    <span class="text-[8px] text-[#999] md:text-[10px]">تومان</span>
                </div>
                <button type="button"
                        class="js-add-cart flex h-8 w-8 items-center justify-center rounded-xl bg-pink text-[17px] leading-none text-secondary transition hover:bg-secondary hover:text-white disabled:opacity-40 md:h-9 md:w-9"
                        data-id="<?= (int) $p['id'] ?>" <?= $available <= 0 ? 'disabled' : '' ?> aria-label="افزودن به سبد">+</button>
            </div>
        </div>
    </div>
</div>
