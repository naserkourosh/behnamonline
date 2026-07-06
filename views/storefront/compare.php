<?php
/** @var list<array<string,mixed>> $products */
/** @var array<int,array<string,string>> $attrs */
/** @var list<string> $attrKeys */
$this->meta(['title' => 'مقایسه محصولات | بهنام', 'robots' => 'noindex']);

$cell  = 'border-b border-line2 p-3 align-middle text-center';
$label = 'border-b border-line2 bg-surface p-3 text-right text-[12px] font-bold text-[#555] whitespace-nowrap sticky right-0';
?>
<div class="container-page py-6 md:py-8">
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-[18px] font-bold text-secondary md:text-[22px]">مقایسه محصولات</h1>
        <?php if ($products !== []): ?>
            <button type="button" class="js-compare-clear text-[12px] font-semibold text-danger">حذف همه</button>
        <?php endif; ?>
    </div>

    <?php if ($products === []): ?>
        <div class="rounded-2xl border border-line2 bg-surface py-16 text-center">
            <div class="mb-2 text-[40px]">⚖️</div>
            <p class="mb-4 text-[13px] text-[#777]">هنوز محصولی برای مقایسه انتخاب نکرده‌اید.</p>
            <a href="<?= e(url('/category')) ?>" class="btn-primary inline-block px-6 py-3 text-[13px]">مشاهده محصولات</a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto rounded-2xl border border-line2 bg-white">
            <table class="w-full border-collapse text-[12.5px]">
                <tbody>
                    <!-- product header -->
                    <tr>
                        <td class="<?= $label ?>"></td>
                        <?php foreach ($products as $p): $img = (string) ($p['image'] ?: 'assets/images/placeholder-product.svg'); ?>
                            <td class="<?= $cell ?> min-w-[160px]">
                                <button type="button" class="js-compare-remove mb-2 text-[11px] font-semibold text-danger" data-id="<?= (int) $p['id'] ?>">✕ حذف</button>
                                <a href="<?= e(url('/product/' . $p['slug'])) ?>" class="block">
                                    <img src="<?= e(asset($img)) ?>" alt="<?= e((string) ($p['image_alt'] ?: $p['name'])) ?>" loading="lazy" decoding="async" class="mx-auto mb-2 h-24 w-24 rounded-xl object-cover">
                                    <span class="clamp-2 text-[12px] font-semibold leading-6 text-[#333]"><?= e($p['name']) ?></span>
                                </a>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <!-- price -->
                    <tr>
                        <td class="<?= $label ?>">قیمت</td>
                        <?php foreach ($products as $p): $price = (int) $p['price']; $old = (int) ($p['old_price'] ?? 0); $dc = discount_percent($old, $price); ?>
                            <td class="<?= $cell ?>">
                                <?php if ($dc > 0): ?><div class="text-[10px] text-[#bbb] line-through nums"><?= money($old) ?></div><?php endif; ?>
                                <div class="font-extrabold text-secondary nums"><?= money($price) ?> <span class="text-[9px] font-normal text-[#999]">ت</span></div>
                                <?php if (flash_active($p)): ?><div class="mt-1 text-[9.5px] font-bold text-danger">⚡ شگفت‌انگیز</div><?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <!-- brand -->
                    <tr>
                        <td class="<?= $label ?>">برند</td>
                        <?php foreach ($products as $p): ?>
                            <td class="<?= $cell ?> text-[#555]"><?= e((string) ($p['brand_name'] ?? '—')) ?: '—' ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <!-- rating -->
                    <tr>
                        <td class="<?= $label ?>">امتیاز</td>
                        <?php foreach ($products as $p): ?>
                            <td class="<?= $cell ?>"><span class="text-star">★</span> <span class="nums text-[#555]"><?= fa(number_format((float) $p['rating_avg'], 1)) ?></span></td>
                        <?php endforeach; ?>
                    </tr>
                    <!-- availability -->
                    <tr>
                        <td class="<?= $label ?>">موجودی</td>
                        <?php foreach ($products as $p): $avail = (int) $p['stock'] - (int) ($p['reserved'] ?? 0); ?>
                            <td class="<?= $cell ?>">
                                <?php if ($avail > 0): ?><span class="font-semibold text-success">موجود</span><?php else: ?><span class="font-semibold text-danger">ناموجود</span><?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <!-- attributes (union) -->
                    <?php foreach ($attrKeys as $key): ?>
                        <tr>
                            <td class="<?= $label ?>"><?= e($key) ?></td>
                            <?php foreach ($products as $p): ?>
                                <td class="<?= $cell ?> text-[#555]"><?= e($attrs[(int) $p['id']][$key] ?? '—') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <!-- add to cart -->
                    <tr>
                        <td class="<?= $label ?>"></td>
                        <?php foreach ($products as $p): $avail = (int) $p['stock'] - (int) ($p['reserved'] ?? 0); ?>
                            <td class="<?= $cell ?>">
                                <button type="button" class="js-add-cart btn-primary w-full px-3 py-2.5 text-[12px] disabled:opacity-40" data-id="<?= (int) $p['id'] ?>" <?= $avail <= 0 ? 'disabled' : '' ?>>افزودن به سبد</button>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
