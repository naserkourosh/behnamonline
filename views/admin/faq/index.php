<?php
/** @var list<array<string,mixed>> $items @var array<string,mixed>|null $edit */
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
?>
<div class="grid gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl border border-line2 bg-white">
            <table class="w-full text-[12.5px]">
                <thead><tr class="border-b border-line bg-surface text-[#888]"><th class="p-3 text-right font-semibold">پرسش</th><th class="p-3 font-semibold">دسته</th><th class="p-3 font-semibold">ترتیب</th><th class="p-3 font-semibold">وضعیت</th><th class="p-3 font-semibold">عملیات</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $f): ?>
                        <tr class="border-b border-line2 last:border-0 hover:bg-surface/50">
                            <td class="p-3 font-semibold text-[#333]"><?= e($f['question']) ?></td>
                            <td class="p-3 text-center text-[#666]"><?= e($f['category']) ?></td>
                            <td class="p-3 text-center nums text-[#888]"><?= fa((int) $f['sort']) ?></td>
                            <td class="p-3 text-center"><?= (int) $f['is_active'] ? '<span class="text-success">فعال</span>' : '<span class="text-[#999]">غیرفعال</span>' ?></td>
                            <td class="p-3">
                                <div class="flex items-center justify-center gap-2.5">
                                    <a href="<?= e(url('/admin/faq?edit=' . $f['id'])) ?>" class="text-secondary hover:underline">ویرایش</a>
                                    <form method="post" action="<?= e(url('/admin/faq/' . $f['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این سوال؟"><?= csrf_field() ?><button class="text-danger hover:underline">حذف</button></form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($items === []): ?><tr><td colspan="5" class="p-8 text-center text-[#999]">سوالی ثبت نشده است.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <form method="post" action="<?= e(url('/admin/faq' . ($edit ? '/' . $edit['id'] : ''))) ?>" class="rounded-2xl border border-line2 bg-white p-5">
            <?= csrf_field() ?>
            <h3 class="mb-3 text-[14px] font-bold text-[#333]"><?= $edit ? 'ویرایش سوال' : 'سوال جدید' ?></h3>
            <div class="mb-3"><label class="<?= $lbl ?>">دسته</label><input name="category" value="<?= e($edit['category'] ?? 'عمومی') ?>" class="<?= $inp ?>" list="faq-cats"></div>
            <datalist id="faq-cats"><?php foreach (array_unique(array_map(fn ($x) => $x['category'], $items)) as $cat): ?><option value="<?= e($cat) ?>"><?php endforeach; ?></datalist>
            <div class="mb-3"><label class="<?= $lbl ?>">پرسش *</label><input name="question" value="<?= e($edit['question'] ?? '') ?>" class="<?= $inp ?>" required></div>
            <div class="mb-3"><label class="<?= $lbl ?>">پاسخ *</label><textarea name="answer" rows="4" class="<?= $inp ?>" required><?= e($edit['answer'] ?? '') ?></textarea></div>
            <div class="mb-3"><label class="<?= $lbl ?>">ترتیب</label><input name="sort" value="<?= e($edit['sort'] ?? '0') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
            <label class="mb-4 flex items-center justify-between"><span class="text-[12.5px] text-[#555]">فعال</span><input type="checkbox" name="is_active" value="1" class="h-5 w-5 accent-secondary" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?>></label>
            <button class="btn-primary w-full py-2.5 text-[13px]"><?= $edit ? 'ذخیره' : 'افزودن' ?></button>
            <?php if ($edit): ?><a href="<?= e(url('/admin/faq')) ?>" class="mt-2 block text-center text-[12px] text-[#999]">انصراف</a><?php endif; ?>
        </form>
    </div>
</div>
