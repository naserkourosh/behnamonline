<?php
/** @var array<string,mixed>|null $item */
$isEdit = $item !== null;
$action = $isEdit ? url('/admin/brands/' . $item['id']) : url('/admin/brands');
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$v = static fn (string $k, $d = '') => e($item[$k] ?? $d);
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="max-w-xl">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <a href="<?= e(url('/admin/brands')) ?>" class="text-[12px] text-mauve">‹ بازگشت</a>
        <button class="btn-primary px-6 py-2.5 text-[13px]"><?= $isEdit ? 'ذخیره' : 'ایجاد' ?></button>
    </div>
    <div class="space-y-4 rounded-2xl border border-line2 bg-white p-5">
        <div><label class="<?= $lbl ?>">نام *</label><input name="name" value="<?= $v('name') ?>" class="<?= $inp ?>" required></div>
        <div><label class="<?= $lbl ?>">نامک</label><input name="slug" value="<?= $v('slug') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="خودکار از نام"></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="<?= $lbl ?>">ترتیب</label><input name="sort" value="<?= $v('sort', '0') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
            <label class="flex items-end gap-2 pb-2.5"><input type="checkbox" name="is_active" value="1" class="h-5 w-5 accent-secondary" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?>><span class="text-[13px] text-[#555]">فعال</span></label>
        </div>
        <div><label class="<?= $lbl ?>">لوگو</label>
            <?php if (!empty($item['logo'])): ?><img src="<?= e(asset((string) $item['logo'])) ?>" alt="" class="mb-2 h-14 w-14 rounded-lg object-contain"><?php endif; ?>
            <input type="file" name="logo" accept="image/*" class="w-full text-[12px] text-[#666] file:mr-2 file:rounded-lg file:border-0 file:bg-pink file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-secondary">
        </div>
    </div>
</form>
