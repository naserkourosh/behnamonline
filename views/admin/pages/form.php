<?php
/** @var array<string,mixed>|null $item */
$isEdit = $item !== null;
$action = $isEdit ? url('/admin/pages/' . $item['id']) : url('/admin/pages');
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$v = static fn (string $k, $d = '') => e($item[$k] ?? $d);
?>
<form method="post" action="<?= e($action) ?>" class="js-guard-unsaved">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <a href="<?= e(url('/admin/pages')) ?>" class="text-[12px] text-mauve">‹ بازگشت به صفحات</a>
        <div class="flex items-center gap-3">
            <?php if ($isEdit): ?>
                <a href="<?= e(url('/page/' . $item['slug'])) ?>" target="_blank" class="rounded-xl2 border border-line px-4 py-2.5 text-[12px] font-semibold text-[#666] hover:border-secondary hover:text-secondary">نمایش در سایت</a>
            <?php endif; ?>
            <button class="btn-primary px-6 py-2.5 text-[13px]"><?= $isEdit ? 'ذخیره تغییرات' : 'ایجاد صفحه' ?></button>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1fr_320px]">
        <div class="rounded-2xl border border-line2 bg-white p-5">
            <div class="mb-3"><label class="<?= $lbl ?>">عنوان *</label><input name="title" value="<?= $v('title') ?>" class="<?= $inp ?>" required></div>
            <div class="mb-3"><label class="<?= $lbl ?>">نامک (آدرس صفحه)</label>
                <div class="flex items-center gap-2" dir="ltr">
                    <span class="flex-none text-[12px] text-[#aaa]">/page/</span>
                    <input name="slug" value="<?= $v('slug') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="terms">
                </div>
                <p class="mt-1 text-[10.5px] text-[#aaa]">خالی بگذارید تا از روی عنوان ساخته شود.</p>
            </div>
            <div><label class="<?= $lbl ?>">متن صفحه <span class="font-normal text-[#aaa]">(ویرایشگر متن)</span></label>
                <textarea name="body" rows="14" class="js-wysiwyg <?= $inp ?>"><?= $v('body') ?></textarea>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">انتشار</h3>
                <label class="mb-3 flex items-center justify-between"><span class="text-[12.5px] text-[#555]">فعال (نمایش در سایت)</span><input type="checkbox" name="is_active" value="1" class="h-5 w-5 accent-secondary" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?>></label>
                <label class="mb-3 flex items-center justify-between"><span class="text-[12.5px] text-[#555]">نمایش لینک در فوتر</span><input type="checkbox" name="show_in_footer" value="1" class="h-5 w-5 accent-secondary" <?= !empty($item['show_in_footer']) ? 'checked' : '' ?>></label>
                <div><label class="<?= $lbl ?>">ترتیب</label><input name="sort" value="<?= $v('sort', '0') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
            </div>

            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">سئو</h3>
                <div class="mb-3"><label class="<?= $lbl ?>">عنوان سئو</label><input name="seo_title" value="<?= $v('seo_title') ?>" class="<?= $inp ?>" placeholder="خالی = عنوان صفحه"></div>
                <div><label class="<?= $lbl ?>">توضیحات متا (حداکثر ۳۰۰ حرف)</label><textarea name="seo_description" rows="3" class="<?= $inp ?>"><?= $v('seo_description') ?></textarea></div>
            </div>
        </div>
    </div>
</form>
