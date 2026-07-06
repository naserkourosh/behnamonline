<?php
/** @var array<string,mixed>|null $item */
$isEdit = $item !== null;
$action = $isEdit ? url('/admin/banners/' . $item['id']) : url('/admin/banners');
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$v = static fn (string $k, $d = '') => e($item[$k] ?? $d);
$ltr = ' dir="ltr" ';
$place = (string) ($item['placement'] ?? 'hero');
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <a href="<?= e(url('/admin/banners')) ?>" class="text-[12px] text-mauve">‹ بازگشت</a>
        <button class="btn-primary px-6 py-2.5 text-[13px]"><?= $isEdit ? 'ذخیره تغییرات' : 'ایجاد بنر' ?></button>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-line2 bg-white p-5">
            <h3 class="mb-4 text-[14px] font-bold text-[#333]">محتوا</h3>
            <div class="mb-3"><label class="<?= $lbl ?>">عنوان *</label><input name="title" value="<?= $v('title') ?>" class="<?= $inp ?>" required></div>
            <div class="mb-3"><label class="<?= $lbl ?>">زیرعنوان</label><input name="subtitle" value="<?= $v('subtitle') ?>" class="<?= $inp ?>"></div>
            <div class="mb-3"><label class="<?= $lbl ?>">برچسب بالا (kicker)</label><input name="kicker" value="<?= $v('kicker') ?>" class="<?= $inp ?>" placeholder="مثلاً مجموعه جدید بهار"></div>
            <div class="mb-3 grid grid-cols-2 gap-3">
                <div><label class="<?= $lbl ?>">متن دکمه</label><input name="cta_label" value="<?= $v('cta_label') ?>" class="<?= $inp ?>" placeholder="خرید اکنون"></div>
                <div><label class="<?= $lbl ?>">لینک مقصد</label><input name="link_url" value="<?= $v('link_url', '/category') ?>" <?= $ltr ?> class="<?= $inp ?> text-left" placeholder="/category/perfume"></div>
            </div>
        </div>

        <div class="rounded-2xl border border-line2 bg-white p-5">
            <h3 class="mb-4 text-[14px] font-bold text-[#333]">نمایش</h3>
            <div class="mb-3"><label class="<?= $lbl ?>">جایگاه</label>
                <select name="placement" class="<?= $inp ?>">
                    <option value="hero"   <?= $place === 'hero'   ? 'selected' : '' ?>>اسلایدر اصلی صفحه اول</option>
                    <option value="promo"  <?= $place === 'promo'  ? 'selected' : '' ?>>باکس تبلیغاتی کنار اسلایدر</option>
                    <option value="strip"  <?= $place === 'strip'  ? 'selected' : '' ?>>نوار تبلیغاتی</option>
                    <option value="inline" <?= $place === 'inline' ? 'selected' : '' ?>>بنر تصویری میان صفحه (بین ردیف‌های محصولات)</option>
                </select>
                <p class="mt-1 text-[10.5px] leading-5 text-[#aaa]">اندازه پیشنهادی تصویر: اسلایدر ۱۶۰۰×۶۰۰ · باکس کنار اسلایدر ۷۲۰×۴۳۰ · نوار ۱۲۰۰×۲۰۰ · بنر میان صفحه ۱۶۰۰×۳۵۰</p>
            </div>
            <div class="mb-3"><label class="<?= $lbl ?>">تصویر</label>
                <div class="js-banner-img-box mb-2 <?= empty($item['image']) ? 'hidden' : '' ?>">
                    <img src="<?= !empty($item['image']) ? e(asset((string) $item['image'])) : '' ?>" alt="پیش‌نمایش بنر" class="js-banner-img-preview max-h-40 w-full rounded-xl border border-line2 object-cover">
                    <?php if (!empty($item['image'])): ?>
                        <label class="mt-2 flex items-center gap-1.5 text-[11px] text-danger"><input type="checkbox" name="remove_image" value="1" class="h-4 w-4 accent-danger"> حذف تصویر فعلی (بدون جایگزین)</label>
                    <?php endif; ?>
                </div>
                <input type="file" name="image" accept="image/*" class="js-banner-img-input w-full text-[12px] text-[#666] file:mr-2 file:rounded-lg file:border-0 file:bg-pink file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-secondary">
                <p class="mt-1 text-[10.5px] text-[#aaa]"><?= $isEdit ? 'انتخاب فایل جدید، تصویر فعلی را جایگزین می‌کند (پیش‌نمایش بلافاصله نمایش داده می‌شود).' : 'JPG/PNG/WebP تا ۳ مگابایت.' ?></p>
            </div>
            <div class="mb-3"><label class="<?= $lbl ?>">رنگ/گرادیان پس‌زمینه (بدون تصویر)</label><input name="bg_color" value="<?= $v('bg_color') ?>" <?= $ltr ?> class="<?= $inp ?> text-left" placeholder="linear-gradient(155deg,#F4E4E6,#E8C5C8)"></div>
            <div class="mb-3 grid grid-cols-3 gap-3">
                <div><label class="<?= $lbl ?>">ترتیب</label><input name="sort" value="<?= $v('sort', '0') ?>" <?= $ltr ?> class="<?= $inp ?> text-left"></div>
                <div><label class="<?= $lbl ?>">شروع</label><input name="starts_at" value="<?= $v('starts_at') ?>" <?= $ltr ?> class="js-jdate <?= $inp ?> text-left"></div>
                <div><label class="<?= $lbl ?>">پایان</label><input name="ends_at" value="<?= $v('ends_at') ?>" <?= $ltr ?> class="js-jdate <?= $inp ?> text-left"></div>
            </div>
            <label class="flex items-center justify-between pt-1"><span class="text-[12.5px] text-[#555]">فعال</span><input type="checkbox" name="is_active" value="1" class="h-5 w-5 accent-secondary" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?>></label>
        </div>
    </div>
</form>
