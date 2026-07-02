<?php
/** @var array<string,mixed>|null $item @var array<string,string> $roles @var array<string,string> $allCaps */
/** @var list<string> $checked @var bool $custom */
$isEdit = $item !== null;
$action = $isEdit ? url('/admin/staff/' . $item['id']) : url('/admin/staff');
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$v = static fn (string $k, $d = '') => e($item[$k] ?? $d);
$ltr = ' dir="ltr" ';
$role = (string) ($item['role'] ?? 'editor');
?>
<form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <a href="<?= e(url('/admin/staff')) ?>" class="text-[12px] text-mauve">‹ بازگشت</a>
        <button class="btn-primary px-6 py-2.5 text-[13px]"><?= $isEdit ? 'ذخیره تغییرات' : 'ایجاد کاربر' ?></button>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-line2 bg-white p-5">
            <h3 class="mb-4 text-[14px] font-bold text-[#333]">مشخصات</h3>
            <div class="mb-3"><label class="<?= $lbl ?>">نام نمایشی *</label><input name="name" value="<?= $v('name') ?>" class="<?= $inp ?>" required></div>
            <div class="mb-3"><label class="<?= $lbl ?>">نام کاربری *</label><input name="username" value="<?= $v('username') ?>" <?= $ltr ?> class="<?= $inp ?> text-left" required></div>
            <div class="mb-3"><label class="<?= $lbl ?>">ایمیل</label><input name="email" value="<?= $v('email') ?>" <?= $ltr ?> class="<?= $inp ?> text-left"></div>
            <div class="mb-3"><label class="<?= $lbl ?>">رمز عبور <?= $isEdit ? '(برای تغییر پر کنید)' : '*' ?></label><input name="password" type="password" <?= $ltr ?> class="<?= $inp ?> text-left" placeholder="حداقل ۶ کاراکتر" <?= $isEdit ? '' : 'required' ?>></div>
            <label class="flex items-center justify-between pt-1"><span class="text-[12.5px] text-[#555]">حساب فعال</span><input type="checkbox" name="is_active" value="1" class="h-5 w-5 accent-secondary" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?>></label>
        </div>

        <div class="rounded-2xl border border-line2 bg-white p-5">
            <h3 class="mb-4 text-[14px] font-bold text-[#333]">نقش و دسترسی</h3>
            <div class="mb-4"><label class="<?= $lbl ?>">نقش</label>
                <select name="role" class="<?= $inp ?>">
                    <?php foreach ($roles as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $role === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-[10.5px] text-[#999]">مدیر کل به همه بخش‌ها دسترسی کامل دارد.</p>
            </div>

            <input type="checkbox" id="custom_caps" name="custom_caps" value="1" class="peer sr-only" <?= $custom ? 'checked' : '' ?>>
            <label for="custom_caps" class="mb-3 flex cursor-pointer items-center gap-2 text-[12.5px] font-semibold text-[#777] before:h-4 before:w-4 before:flex-none before:rounded before:border before:border-line before:content-[''] peer-checked:text-secondary peer-checked:before:border-secondary peer-checked:before:bg-secondary">
                محدود کردن دسترسی به بخش‌های انتخاب‌شده
            </label>

            <div class="grid grid-cols-2 gap-x-3 gap-y-2 rounded-xl2 border border-line2 bg-surface p-3 opacity-40 transition peer-checked:pointer-events-auto peer-checked:opacity-100 pointer-events-none">
                <?php foreach ($allCaps as $cap => $label): if ($cap === 'staff') { continue; } ?>
                    <label class="flex items-center gap-2 text-[12px] text-[#555]">
                        <input type="checkbox" name="caps[]" value="<?= e($cap) ?>" class="h-4 w-4 accent-secondary" <?= in_array($cap, $checked, true) ? 'checked' : '' ?>>
                        <?= e($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="mt-2 text-[10.5px] text-[#999]">اگر تیک بالا فعال نباشد، دسترسی‌های پیش‌فرض نقش اعمال می‌شود.</p>
        </div>
    </div>
</form>
