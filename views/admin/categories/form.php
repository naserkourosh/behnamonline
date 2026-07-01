<?php
/** @var array<string,mixed>|null $item @var list<array<string,mixed>> $categories */
$isEdit = $item !== null;
$action = $isEdit ? url('/admin/categories/' . $item['id']) : url('/admin/categories');
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$v = static fn (string $k, $d = '') => e($item[$k] ?? $d);
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="max-w-2xl">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <a href="<?= e(url('/admin/categories')) ?>" class="text-[12px] text-mauve">‹ بازگشت</a>
        <button class="btn-primary px-6 py-2.5 text-[13px]"><?= $isEdit ? 'ذخیره' : 'ایجاد' ?></button>
    </div>
    <div class="space-y-4 rounded-2xl border border-line2 bg-white p-5">
        <div><label class="<?= $lbl ?>">نام *</label><input name="name" value="<?= $v('name') ?>" class="<?= $inp ?>" required></div>
        <div><label class="<?= $lbl ?>">نامک</label><input name="slug" value="<?= $v('slug') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="خودکار از نام"></div>
        <div><label class="<?= $lbl ?>">دسته والد</label>
            <select name="parent_id" class="<?= $inp ?>"><option value="">— بدون والد —</option>
                <?php foreach ($categories as $c): if ($isEdit && (int) $c['id'] === (int) $item['id']) { continue; } ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) ($item['parent_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="<?= $lbl ?>">ترتیب</label><input name="sort" value="<?= $v('sort', '0') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
            <label class="flex items-end gap-2 pb-2.5"><input type="checkbox" name="is_active" value="1" class="h-5 w-5 accent-secondary" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?>><span class="text-[13px] text-[#555]">فعال</span></label>
        </div>
        <div><label class="<?= $lbl ?>">تصویر دسته</label>
            <?php if (!empty($item['image'])): ?><img src="<?= e(asset((string) $item['image'])) ?>" alt="" class="mb-2 h-16 w-16 rounded-full object-cover"><?php endif; ?>
            <input type="file" name="image" accept="image/*" class="w-full text-[12px] text-[#666] file:mr-2 file:rounded-lg file:border-0 file:bg-pink file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-secondary">
        </div>
        <div><label class="<?= $lbl ?>">عنوان سئو</label><input name="seo_title" value="<?= $v('seo_title') ?>" class="<?= $inp ?>"></div>
        <div><label class="<?= $lbl ?>">توضیحات متا</label><textarea name="seo_description" rows="2" class="<?= $inp ?>"><?= e($item['seo_description'] ?? '') ?></textarea></div>
    </div>
</form>
