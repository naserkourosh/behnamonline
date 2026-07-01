<?php /** @var list<array<string,mixed>> $items @var int $total @var int $page @var int $pages @var string $search */ ?>
<form method="get" action="<?= e(url('/admin/customers')) ?>" class="mb-4 flex items-center gap-2">
    <input name="q" value="<?= e($search) ?>" placeholder="نام یا موبایل…" class="w-56 rounded-xl2 border border-line bg-white px-4 py-2 text-[13px] outline-none focus:border-secondary">
    <button class="rounded-xl2 bg-surface px-4 py-2 text-[13px] font-semibold text-secondary">جستجو</button>
    <span class="text-[12px] text-[#999] nums"><?= fa($total) ?> مشتری</span>
</form>
<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">نام</th>
                    <th class="p-3 font-semibold">موبایل</th>
                    <th class="p-3 font-semibold">سفارش‌ها</th>
                    <th class="p-3 font-semibold">مجموع خرید</th>
                    <th class="p-3 font-semibold">عضویت</th>
                    <th class="p-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $u): $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')); ?>
                    <tr class="border-b border-line2 last:border-0">
                        <td class="p-3 font-semibold text-[#333]"><?= e($name ?: 'کاربر') ?></td>
                        <td class="p-3 text-center text-[#666] nums" dir="ltr"><?= e($u['mobile']) ?></td>
                        <td class="p-3 text-center nums"><?= fa((int) $u['order_count']) ?></td>
                        <td class="p-3 text-center font-bold text-secondary nums"><?= money((int) $u['total_spent']) ?></td>
                        <td class="p-3 text-center text-[#999] nums"><?= jdate((string) $u['created_at']) ?></td>
                        <td class="p-3 text-center"><a href="<?= e(url('/admin/customers/' . $u['id'])) ?>" class="text-secondary hover:underline">مشاهده</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="6" class="p-8 text-center text-[#999]">مشتری‌ای یافت نشد.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->partial('admin/pagination', ['page' => $page, 'pages' => $pages, 'baseUrl' => url('/admin/customers') . '?q=' . urlencode($search) . '&']); ?>
