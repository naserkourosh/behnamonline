<?php
/** @var array<string,mixed> $product */
$p          = $product;
$price      = (int) $p['price'];
$old        = isset($p['old_price']) ? (int) $p['old_price'] : 0;
$discount   = discount_percent($old, $price);
$isNew      = !empty($p['is_new']);
$available  = (int) $p['stock'] - (int) ($p['reserved'] ?? 0);
$showQty    = (bool) setting('show_stock_qty', true);
$lowAt      = (int) setting('low_stock_threshold', 5);
$img        = (string) ($p['image'] ?: 'assets/images/placeholder-product.svg');
$imgAlt     = (string) ($p['image_alt'] ?: $p['name']);
$href       = url('/product/' . $p['slug']);
?>
<div class="card-rise flex h-full flex-col overflow-hidden rounded-2xl border border-line2 bg-white">
    <a href="<?= e($href) ?>" class="relative block aspect-square overflow-hidden bg-[#F3EBE2]">
        <img src="<?= e(asset($img)) ?>" alt="<?= e($imgAlt) ?>" title="<?= e($p['name']) ?>" loading="lazy" decoding="async"
             class="h-full w-full object-cover transition duration-500 hover:scale-105">
        <?php if ($discount > 0): ?>
            <span class="badge-discount absolute right-2.5 top-2.5">٪<?= fa($discount) ?></span>
        <?php elseif ($isNew): ?>
            <span class="badge-new absolute right-2.5 top-2.5">جدید</span>
        <?php endif; ?>
        <button type="button" class="js-wishlist absolute left-2.5 top-2.5 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-secondary" aria-label="افزودن به علاقه‌مندی">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 21s-7-4.4-9.4-8.6C1 9.3 2.6 5.6 6 5.6c2 0 3 1.1 4 2.6 1-1.5 2-2.6 4-2.6 3.4 0 5 3.7 3.4 6.8C19 16.6 12 21 12 21z"/></svg>
        </button>
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
                <span class="me-1 text-[9.5px] text-danger">ناموجود</span>
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
