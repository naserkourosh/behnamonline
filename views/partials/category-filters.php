<?php
/** @var list<array<string,mixed>> $brands */
/** @var array<string,mixed> $filters */
/** @var string $action */
/** @var string $sort */
/** @var list<array<string,mixed>> $allCategories */
$allCategories  = $allCategories ?? [];
$selectedBrands = array_map('intval', (array) ($filters['brand_ids'] ?? []));
$minRating      = (string) ($filters['min_rating'] ?? '');
$currentPath    = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
$sectionTitle   = 'mb-3 text-[13px] font-bold text-[#333]';
$checkRow       = 'flex cursor-pointer items-center gap-2.5 py-1.5';
$checkBox       = 'h-[18px] w-[18px] flex-none rounded accent-secondary';
$selectCls      = 'w-full cursor-pointer rounded-xl border border-line bg-surface px-3 py-2.5 text-[12.5px] text-[#444] outline-none focus:border-secondary';
?>
<form method="get" action="<?= e($action) ?>" class="space-y-6">
    <?php if (!empty($filters['search'])): ?>
        <input type="hidden" name="q" value="<?= e($filters['search']) ?>">
    <?php endif; ?>
    <input type="hidden" name="sort" value="<?= e($sort) ?>">

    <?php if ($allCategories !== []): ?>
    <div>
        <div class="<?= $sectionTitle ?>">دسته‌بندی</div>
        <select class="js-filter-cat <?= $selectCls ?>" aria-label="انتخاب دسته‌بندی">
            <option value="<?= e(url('/category')) ?>" <?= $currentPath === '/category' ? 'selected' : '' ?>>همه دسته‌بندی‌ها</option>
            <?php foreach ($allCategories as $c): $href = url('/category/' . $c['slug']); ?>
                <option value="<?= e($href) ?>" <?= $currentPath === $href ? 'selected' : '' ?>><?= e($c['name']) ?> (<?= fa((int) $c['product_count']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <div>
        <div class="<?= $sectionTitle ?>">محدوده قیمت <span class="text-[10.5px] font-normal text-[#aaa]">(تومان)</span></div>
        <div class="flex items-center gap-2">
            <input type="number" name="min_price" inputmode="numeric" value="<?= e($filters['min_price'] ?? '') ?>" placeholder="از" aria-label="حداقل قیمت" class="w-full rounded-xl border border-line bg-surface px-3 py-2 text-[12px] outline-none focus:border-secondary">
            <span class="text-mauve">—</span>
            <input type="number" name="max_price" inputmode="numeric" value="<?= e($filters['max_price'] ?? '') ?>" placeholder="تا" aria-label="حداکثر قیمت" class="w-full rounded-xl border border-line bg-surface px-3 py-2 text-[12px] outline-none focus:border-secondary">
        </div>
    </div>

    <?php if ($brands !== []): ?>
    <div>
        <div class="<?= $sectionTitle ?>">برند <span class="text-[10.5px] font-normal text-[#aaa] nums">(<?= fa(count($brands)) ?>)</span></div>
        <?php if (count($brands) > 6): ?>
            <input type="text" placeholder="جستجوی برند…" aria-label="جستجوی برند" class="js-brand-search mb-2 w-full rounded-xl border border-line bg-surface px-3 py-2 text-[11.5px] outline-none focus:border-secondary">
        <?php endif; ?>
        <div class="max-h-52 space-y-0.5 overflow-y-auto pe-1">
            <?php foreach ($brands as $b): $on = in_array((int) $b['id'], $selectedBrands, true); ?>
                <label class="js-brand-item <?= $checkRow ?>" data-name="<?= e(mb_strtolower((string) $b['name'])) ?>">
                    <input type="checkbox" name="brand[]" value="<?= (int) $b['id'] ?>" class="<?= $checkBox ?>" <?= $on ? 'checked' : '' ?>>
                    <span class="text-[12.5px] <?= $on ? 'font-bold text-secondary' : 'text-[#555]' ?>"><?= e($b['name']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div>
        <div class="<?= $sectionTitle ?>">وضعیت کالا</div>
        <label class="<?= $checkRow ?>">
            <input type="checkbox" name="in_stock" value="1" class="<?= $checkBox ?>" <?= !empty($filters['in_stock']) ? 'checked' : '' ?>>
            <span class="text-[12.5px] text-[#555]">فقط کالاهای موجود</span>
        </label>
        <label class="<?= $checkRow ?>">
            <input type="checkbox" name="on_sale" value="1" class="<?= $checkBox ?>" <?= !empty($filters['on_sale']) ? 'checked' : '' ?>>
            <span class="text-[12.5px] text-[#555]">فقط کالاهای تخفیف‌دار</span>
        </label>
    </div>

    <div>
        <div class="<?= $sectionTitle ?>">حداقل امتیاز خریداران</div>
        <select name="min_rating" class="<?= $selectCls ?>" aria-label="حداقل امتیاز">
            <option value="">همه امتیازها</option>
            <option value="4" <?= $minRating === '4' ? 'selected' : '' ?>>★ ۴ و بالاتر</option>
            <option value="3" <?= $minRating === '3' ? 'selected' : '' ?>>★ ۳ و بالاتر</option>
        </select>
    </div>

    <div class="flex gap-2.5 pt-1">
        <a href="<?= e($action) ?>" class="btn-outline flex-1 px-4 py-3 text-[13px]">حذف فیلترها</a>
        <button type="submit" class="btn-primary flex-[2] px-4 py-3 text-[13px]">اعمال فیلترها</button>
    </div>
</form>
