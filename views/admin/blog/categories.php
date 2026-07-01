<?php
/** @var list<array<string,mixed>> $items */
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
?>
<div class="mb-4"><a href="<?= e(url('/admin/blog')) ?>" class="text-[12px] text-mauve">‹ بازگشت به مجله</a></div>
<div class="grid gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl border border-line2 bg-white">
            <table class="w-full text-[12.5px]">
                <thead><tr class="border-b border-line bg-surface text-[#888]"><th class="p-3 text-right font-semibold">نام</th><th class="p-3 font-semibold">نامک</th><th class="p-3 font-semibold">مطالب</th><th class="p-3 font-semibold">وضعیت</th><th class="p-3 font-semibold">عملیات</th></tr></thead>
            <tbody>
                <?php foreach ($items as $c): ?>
                    <tr class="border-b border-line2 last:border-0">
                        <td class="p-3 font-semibold text-[#333]"><?= e($c['name']) ?></td>
                        <td class="p-3 text-center text-[#888]" dir="ltr"><?= e($c['slug']) ?></td>
                        <td class="p-3 text-center nums text-[#666]"><?= fa((int) $c['post_count']) ?></td>
                        <td class="p-3 text-center"><?= (int) $c['is_active'] ? '<span class="text-success">فعال</span>' : '<span class="text-[#999]">غیرفعال</span>' ?></td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2.5">
                                <a href="<?= e(url('/admin/blog/categories?edit=' . $c['id'])) ?>" class="text-secondary hover:underline">ویرایش</a>
                                <form method="post" action="<?= e(url('/admin/blog/categories/' . $c['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این دسته؟"><?= csrf_field() ?><button class="text-danger hover:underline">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="5" class="p-8 text-center text-[#999]">دسته‌ای وجود ندارد.</td></tr><?php endif; ?>
            </tbody>
            </table>
        </div>
    </div>
    <div>
        <?php $editId = (int) ($_GET['edit'] ?? 0); $ed = null; foreach ($items as $c) { if ((int) $c['id'] === $editId) { $ed = $c; } } ?>
        <form method="post" action="<?= e(url('/admin/blog/categories' . ($ed ? '/' . $ed['id'] : ''))) ?>" class="rounded-2xl border border-line2 bg-white p-5">
            <?= csrf_field() ?>
            <h3 class="mb-3 text-[14px] font-bold text-[#333]"><?= $ed ? 'ویرایش دسته' : 'دسته جدید' ?></h3>
            <div class="mb-3"><label class="<?= $lbl ?>">نام *</label><input name="name" value="<?= e($ed['name'] ?? '') ?>" class="<?= $inp ?>" required></div>
            <div class="mb-3"><label class="<?= $lbl ?>">نامک</label><input name="slug" value="<?= e($ed['slug'] ?? '') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
            <div class="mb-3"><label class="<?= $lbl ?>">ترتیب</label><input name="sort" value="<?= e($ed['sort'] ?? '0') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
            <label class="mb-4 flex items-center justify-between"><span class="text-[12.5px] text-[#555]">فعال</span><input type="checkbox" name="is_active" value="1" class="h-5 w-5 accent-secondary" <?= ($ed['is_active'] ?? 1) ? 'checked' : '' ?>></label>
            <button class="btn-primary w-full py-2.5 text-[13px]"><?= $ed ? 'ذخیره' : 'افزودن' ?></button>
            <?php if ($ed): ?><a href="<?= e(url('/admin/blog/categories')) ?>" class="mt-2 block text-center text-[12px] text-[#999]">انصراف</a><?php endif; ?>
        </form>
    </div>
</div>
