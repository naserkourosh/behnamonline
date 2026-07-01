<?php
/** @var list<array<string,mixed>> $items @var int $total @var int $page @var int $pages @var string $search @var int $pending */
?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
        <a href="<?= e(url('/admin/blog/categories')) ?>" class="rounded-xl2 bg-surface px-4 py-2 text-[12.5px] font-semibold text-secondary">دسته‌ها</a>
        <a href="<?= e(url('/admin/blog/comments')) ?>" class="rounded-xl2 bg-surface px-4 py-2 text-[12.5px] font-semibold text-secondary">
            دیدگاه‌ها <?php if ($pending > 0): ?><span class="mr-1 rounded-full bg-danger px-1.5 py-0.5 text-[10px] font-bold text-white nums"><?= fa($pending) ?></span><?php endif; ?>
        </a>
    </div>
    <div class="flex items-center gap-2">
        <form method="get" action="<?= e(url('/admin/blog')) ?>" class="flex items-center gap-2">
            <input name="q" value="<?= e($search) ?>" placeholder="جستجوی عنوان…" class="w-48 rounded-xl2 border border-line bg-white px-3.5 py-2 text-[13px] outline-none focus:border-secondary">
            <button class="rounded-xl2 bg-surface px-4 py-2 text-[13px] font-semibold text-secondary">جستجو</button>
        </form>
        <a href="<?= e(url('/admin/blog/create')) ?>" class="btn-primary px-5 py-2.5 text-[13px]">+ مطلب جدید</a>
    </div>
</div>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">عنوان</th>
                    <th class="p-3 font-semibold">دسته</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">بازدید</th>
                    <th class="p-3 font-semibold">تاریخ</th>
                    <th class="p-3 font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $p): ?>
                    <tr class="border-b border-line2 last:border-0 hover:bg-surface/50">
                        <td class="p-3">
                            <a href="<?= e(url('/admin/blog/' . $p['id'] . '/edit')) ?>" class="font-semibold text-[#333] hover:text-secondary"><?= e($p['title']) ?></a>
                            <?php if ((int) $p['is_featured']): ?><span class="mr-1 rounded bg-gold/15 px-1.5 py-0.5 text-[9px] font-bold text-gold">ویژه</span><?php endif; ?>
                            <?php if ((int) $p['pending_comments'] > 0): ?><span class="mr-1 rounded bg-[#FDECEC] px-1.5 py-0.5 text-[9px] font-bold text-danger nums"><?= fa((int) $p['pending_comments']) ?> دیدگاه جدید</span><?php endif; ?>
                        </td>
                        <td class="p-3 text-center text-[#666]"><?= e($p['category_name'] ?: '—') ?></td>
                        <td class="p-3 text-center">
                            <?php if ((string) $p['status'] === 'published'): ?><span class="rounded-lg bg-[#E7F7F0] px-2 py-1 text-[10px] font-bold text-success">منتشرشده</span><?php else: ?><span class="rounded-lg bg-surface px-2 py-1 text-[10px] font-bold text-[#999]">پیش‌نویس</span><?php endif; ?>
                        </td>
                        <td class="p-3 text-center nums text-[#666]"><?= fa((int) $p['view_count']) ?></td>
                        <td class="p-3 text-center nums text-[11px] text-[#999]"><?= $p['published_at'] ? jdate((string) $p['published_at']) : '—' ?></td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2.5">
                                <?php if ((string) $p['status'] === 'published'): ?><a href="<?= e(url('/blog/' . $p['slug'])) ?>" target="_blank" rel="noopener" class="text-mauve hover:underline">نمایش ↗</a><?php endif; ?>
                                <a href="<?= e(url('/admin/blog/' . $p['id'] . '/edit')) ?>" class="text-secondary hover:underline">ویرایش</a>
                                <form method="post" action="<?= e(url('/admin/blog/' . $p['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این مطلب؟"><?= csrf_field() ?><button class="text-danger hover:underline">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="6" class="p-8 text-center text-[#999]">مطلبی یافت نشد.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->partial('admin/pagination', ['page' => $page, 'pages' => $pages, 'baseUrl' => url('/admin/blog') . '?q=' . urlencode($search) . '&']); ?>
