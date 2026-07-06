<?php
/** @var list<array<string,mixed>> $brands */
/** @var array<string,mixed> $filters */
/** @var string $action */
/** @var string $sort */
$selectedBrands = array_map('intval', (array) ($filters['brand_ids'] ?? []));
$minRating      = (string) ($filters['min_rating'] ?? '');
?>
<form method="get" action="<?= e($action) ?>" class="space-y-6">
    <?php if (!empty($filters['search'])): ?>
        <input type="hidden" name="q" value="<?= e($filters['search']) ?>">
    <?php endif; ?>
    <input type="hidden" name="sort" value="<?= e($sort) ?>">

    <div>
        <div class="mb-3 text-[13px] font-bold text-[#333]">محدوده قیمت (تومان)</div>
        <div class="flex items-center gap-2">
            <input type="number" name="min_price" inputmode="numeric" value="<?= e($filters['min_price'] ?? '') ?>" placeholder="از" class="w-full rounded-xl border border-line bg-surface px-3 py-2 text-[12px] outline-none">
            <span class="text-mauve">—</span>
            <input type="number" name="max_price" inputmode="numeric" value="<?= e($filters['max_price'] ?? '') ?>" placeholder="تا" class="w-full rounded-xl border border-line bg-surface px-3 py-2 text-[12px] outline-none">
        </div>
    </div>

    <?php if ($brands !== []): ?>
    <div>
        <div class="mb-3 text-[13px] font-bold text-[#333]">برند</div>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($brands as $b): $on = in_array((int) $b['id'], $selectedBrands, true); ?>
                <label class="cursor-pointer">
                    <input type="checkbox" name="brand[]" value="<?= (int) $b['id'] ?>" class="peer sr-only" <?= $on ? 'checked' : '' ?>>
                    <span class="inline-block rounded-xl border px-3.5 py-2 text-[11.5px] font-medium transition peer-checked:border-secondary peer-checked:bg-pink peer-checked:text-secondary <?= $on ? 'border-secondary bg-pink text-secondary' : 'border-line text-[#777]' ?>"><?= e($b['name']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div>
        <div class="mb-3 text-[13px] font-bold text-[#333]">وضعیت</div>
        <label class="mb-2.5 flex cursor-pointer items-center justify-between">
            <span class="text-[12.5px] text-[#555]">فقط کالاهای موجود</span>
            <input type="checkbox" name="in_stock" value="1" class="h-5 w-5 accent-secondary" <?= !empty($filters['in_stock']) ? 'checked' : '' ?>>
        </label>
        <label class="flex cursor-pointer items-center justify-between">
            <span class="text-[12.5px] text-[#555]">فقط تخفیف‌دار 🏷️</span>
            <input type="checkbox" name="on_sale" value="1" class="h-5 w-5 accent-secondary" <?= !empty($filters['on_sale']) ? 'checked' : '' ?>>
        </label>
    </div>

    <div>
        <div class="mb-3 text-[13px] font-bold text-[#333]">حداقل امتیاز</div>
        <div class="flex gap-2">
            <?php foreach (['4' => '۴ ★ و بالاتر', '3' => '۳ ★ و بالاتر'] as $val => $label): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="min_rating" value="<?= $val ?>" class="peer sr-only" <?= $minRating === $val ? 'checked' : '' ?>>
                    <span class="inline-block rounded-xl border px-3.5 py-2 text-[11.5px] transition peer-checked:border-secondary peer-checked:bg-secondary peer-checked:text-white <?= $minRating === $val ? 'border-secondary bg-secondary text-white' : 'border-line text-secondary' ?>"><?= e($label) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex gap-2.5 pt-1">
        <a href="<?= e($action) ?>" class="btn-outline flex-1 px-4 py-3 text-[13px]">حذف فیلترها</a>
        <button type="submit" class="btn-primary flex-[2] px-4 py-3 text-[13px]">نمایش نتایج</button>
    </div>
</form>
