<?php
/** @var array<string,mixed>|null $post @var list<array<string,mixed>> $categories */
$isEdit = $post !== null;
$action = $isEdit ? url('/admin/blog/' . $post['id']) : url('/admin/blog');
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$v = static fn (string $k, $d = '') => e($post[$k] ?? $d);
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <a href="<?= e(url('/admin/blog')) ?>" class="text-[12px] text-mauve">‹ بازگشت به مجله</a>
        <button class="btn-primary px-6 py-2.5 text-[13px]"><?= $isEdit ? 'ذخیره تغییرات' : 'ایجاد مطلب' ?></button>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <div class="mb-4"><label class="<?= $lbl ?>">عنوان *</label><input name="title" value="<?= $v('title') ?>" class="<?= $inp ?>" required></div>
                <div class="mb-4"><label class="<?= $lbl ?>">نامک (slug)</label><input name="slug" value="<?= $v('slug') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="به‌صورت خودکار ساخته می‌شود"></div>
                <div class="mb-4"><label class="<?= $lbl ?>">خلاصه</label><textarea name="excerpt" rows="2" class="<?= $inp ?>"><?= e($post['excerpt'] ?? '') ?></textarea></div>
                <div><label class="<?= $lbl ?>">متن کامل <span class="font-normal text-[#aaa]">(HTML مجاز)</span></label><textarea name="body" rows="12" class="<?= $inp ?>"><?= e($post['body'] ?? '') ?></textarea></div>
            </div>
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">سئو</h3>
                <div class="mb-3"><label class="<?= $lbl ?>">عنوان سئو</label><input name="seo_title" value="<?= $v('seo_title') ?>" class="<?= $inp ?>"></div>
                <div><label class="<?= $lbl ?>">توضیحات متا</label><textarea name="seo_description" rows="2" class="<?= $inp ?>"><?= e($post['seo_description'] ?? '') ?></textarea></div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">انتشار</h3>
                <div class="mb-3"><label class="<?= $lbl ?>">وضعیت</label>
                    <select name="status" class="<?= $inp ?>">
                        <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>پیش‌نویس</option>
                        <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>منتشرشده</option>
                    </select>
                </div>
                <div class="mb-3"><label class="<?= $lbl ?>">تاریخ انتشار <span class="font-normal text-[#aaa]">(خالی=اکنون)</span></label><input name="published_at" value="<?= $v('published_at') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="2026-07-02 10:00:00"></div>
                <label class="flex items-center justify-between">
                    <span class="text-[12.5px] text-[#555]">مطلب ویژه</span>
                    <input type="checkbox" name="is_featured" value="1" class="h-5 w-5 accent-secondary" <?= ($post['is_featured'] ?? 0) ? 'checked' : '' ?>>
                </label>
            </div>
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">دسته و نویسنده</h3>
                <div class="mb-3"><label class="<?= $lbl ?>">دسته</label>
                    <select name="category_id" class="<?= $inp ?>"><option value="">— بدون دسته —</option>
                        <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) ($post['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="<?= $lbl ?>">نویسنده</label><input name="author_name" value="<?= $v('author_name') ?>" class="<?= $inp ?>" placeholder="تیم بهنام"></div>
            </div>
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">تصویر شاخص</h3>
                <?php if (!empty($post['cover_image'])): ?><img src="<?= e(asset((string) $post['cover_image'])) ?>" alt="" class="mb-3 h-32 w-full rounded-xl2 object-cover"><?php endif; ?>
                <input type="file" name="cover" accept="image/*" class="w-full text-[12px] text-[#666] file:mr-2 file:rounded-lg file:border-0 file:bg-pink file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-secondary">
                <p class="mt-1.5 text-[11px] text-[#aaa]">JPG, PNG, WEBP — حداکثر ۳ مگابایت.</p>
            </div>
        </div>
    </div>
</form>
