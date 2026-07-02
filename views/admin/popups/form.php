<?php
/** @var array<string,mixed>|null $popup */
$isEdit = $popup !== null;
$action = $isEdit ? url('/admin/popups/' . $popup['id']) : url('/admin/popups');
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$v = static fn (string $k, $d = '') => e($popup[$k] ?? $d);
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <a href="<?= e(url('/admin/popups')) ?>" class="text-[12px] text-mauve">‹ بازگشت</a>
        <button class="btn-primary px-6 py-2.5 text-[13px]"><?= $isEdit ? 'ذخیره تغییرات' : 'ایجاد پاپ‌آپ' ?></button>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <div class="mb-3"><label class="<?= $lbl ?>">عنوان *</label><input name="title" value="<?= $v('title') ?>" class="<?= $inp ?>" required></div>
                <div class="mb-3"><label class="<?= $lbl ?>">متن <span class="font-normal text-[#aaa]">(HTML مجاز)</span></label><textarea name="body" rows="4" class="<?= $inp ?>"><?= e($popup['body'] ?? '') ?></textarea></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="<?= $lbl ?>">متن دکمه</label><input name="cta_label" value="<?= $v('cta_label') ?>" class="<?= $inp ?>" placeholder="مثلاً مشاهده حراج"></div>
                    <div><label class="<?= $lbl ?>">لینک دکمه</label><input name="cta_url" value="<?= $v('cta_url') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="/category"></div>
                </div>
            </div>
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">تصویر</h3>
                <?php if (!empty($popup['image'])): ?><img src="<?= e(asset((string) $popup['image'])) ?>" alt="" class="mb-3 max-h-40 rounded-xl2 object-cover"><?php endif; ?>
                <input type="file" name="image" accept="image/*" class="w-full text-[12px] text-[#666] file:mr-2 file:rounded-lg file:border-0 file:bg-pink file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-secondary">
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">نمایش</h3>
                <div class="mb-3"><label class="<?= $lbl ?>">موقعیت</label>
                    <select name="position" class="<?= $inp ?>">
                        <option value="center" <?= ($popup['position'] ?? 'center') === 'center' ? 'selected' : '' ?>>وسط صفحه (مودال)</option>
                        <option value="corner" <?= ($popup['position'] ?? '') === 'corner' ? 'selected' : '' ?>>گوشه صفحه</option>
                    </select>
                </div>
                <div class="mb-3"><label class="<?= $lbl ?>">تکرار نمایش</label>
                    <select name="frequency" class="<?= $inp ?>">
                        <option value="once_session" <?= ($popup['frequency'] ?? 'once_session') === 'once_session' ? 'selected' : '' ?>>یک‌بار در هر نشست</option>
                        <option value="once_day" <?= ($popup['frequency'] ?? '') === 'once_day' ? 'selected' : '' ?>>روزی یک‌بار</option>
                        <option value="always" <?= ($popup['frequency'] ?? '') === 'always' ? 'selected' : '' ?>>در هر بازدید</option>
                    </select>
                </div>
                <div class="mb-3"><label class="<?= $lbl ?>">تأخیر نمایش (ثانیه)</label><input name="delay_seconds" value="<?= $v('delay_seconds', '3') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
                <div><label class="<?= $lbl ?>">نمایش در صفحه</label><input name="target" value="<?= $v('target', 'all') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="all | home | /category"><p class="mt-1 text-[11px] text-[#aaa]">all=همه، home=صفحهٔ اصلی، یا آدرس مثل /category</p></div>
            </div>
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">زمان‌بندی</h3>
                <div class="mb-3"><label class="<?= $lbl ?>">شروع</label><input name="starts_at" value="<?= $v('starts_at') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="YYYY-MM-DD"></div>
                <div class="mb-3"><label class="<?= $lbl ?>">پایان</label><input name="ends_at" value="<?= $v('ends_at') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="YYYY-MM-DD"></div>
                <div class="mb-3"><label class="<?= $lbl ?>">ترتیب</label><input name="sort" value="<?= $v('sort', '0') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
                <label class="flex items-center justify-between"><span class="text-[12.5px] text-[#555]">فعال</span><input type="checkbox" name="is_active" value="1" class="h-5 w-5 accent-secondary" <?= ($popup['is_active'] ?? 1) ? 'checked' : '' ?>></label>
            </div>
        </div>
    </div>
</form>
