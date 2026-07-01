<?php
/** @var array<string,mixed>|null $product */
/** @var list<array<string,mixed>> $categories @var list<array<string,mixed>> $brands @var list<array<string,mixed>> $tags */
/** @var list<array<string,mixed>> $images @var list<array<string,mixed>> $attributes @var list<array<string,mixed>> $variants @var list<int> $tagIds */
$isEdit = $product !== null;
$action = $isEdit ? url('/admin/products/' . $product['id']) : url('/admin/products');
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$v = static fn (string $k, $d = '') => e($product[$k] ?? $d);
$flag = static fn (string $k, int $default = 0): bool => (bool) ($product[$k] ?? $default);
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <a href="<?= e(url('/admin/products')) ?>" class="text-[12px] text-mauve">‹ بازگشت به محصولات</a>
        <button class="btn-primary px-6 py-2.5 text-[13px]"><?= $isEdit ? 'ذخیره تغییرات' : 'ایجاد محصول' ?></button>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <!-- Main -->
        <div class="space-y-5 lg:col-span-2">
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <div class="mb-4"><label class="<?= $lbl ?>">نام محصول *</label><input name="name" value="<?= $v('name') ?>" class="<?= $inp ?>" required></div>
                <div class="mb-4"><label class="<?= $lbl ?>">نامک (slug)</label><input name="slug" value="<?= $v('slug') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="به‌صورت خودکار از نام ساخته می‌شود"></div>
                <div class="mb-4"><label class="<?= $lbl ?>">توضیح کوتاه</label><input name="short_desc" value="<?= $v('short_desc') ?>" class="<?= $inp ?>"></div>
                <div><label class="<?= $lbl ?>">توضیحات کامل <span class="font-normal text-[#aaa]">(HTML مجاز + امبد آپارات)</span></label><textarea name="description" rows="6" class="<?= $inp ?>"><?= e($product['description'] ?? '') ?></textarea></div>
                <div class="mt-4"><label class="<?= $lbl ?>">لینک امبد ویدیو آپارات</label><input name="aparat_embed" value="<?= $v('aparat_embed') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="https://www.aparat.com/embed/..."></div>
            </div>

            <!-- Specs -->
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <div class="mb-3 flex items-center justify-between"><h3 class="text-[14px] font-bold text-[#333]">مشخصات فنی</h3><button type="button" class="js-add-spec rounded-lg bg-pink px-3 py-1.5 text-[12px] font-semibold text-secondary">+ افزودن</button></div>
                <div class="js-specs space-y-2">
                    <?php foreach ($attributes ?: [['attr_key' => '', 'attr_value' => '']] as $a): ?>
                        <div class="js-row flex gap-2">
                            <input name="attr_key[]" value="<?= e($a['attr_key']) ?>" placeholder="ویژگی (مثلاً حجم)" class="<?= $inp ?>">
                            <input name="attr_value[]" value="<?= e($a['attr_value']) ?>" placeholder="مقدار (مثلاً ۳۰ml)" class="<?= $inp ?>">
                            <button type="button" class="js-del-row flex-none rounded-lg px-2 text-danger">✕</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Variants -->
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <div class="mb-3 flex items-center justify-between"><h3 class="text-[14px] font-bold text-[#333]">تنوع‌ها (حجم/رنگ)</h3><button type="button" class="js-add-variant rounded-lg bg-pink px-3 py-1.5 text-[12px] font-semibold text-secondary">+ افزودن</button></div>
                <div class="js-variants space-y-2">
                    <?php foreach ($variants ?: [] as $vr): ?>
                        <div class="js-row grid grid-cols-[1fr_1fr_1fr_auto] gap-2">
                            <input name="var_label[]" value="<?= e($vr['label']) ?>" placeholder="عنوان" class="<?= $inp ?>">
                            <input name="var_sku[]" value="<?= e($vr['sku'] ?? '') ?>" placeholder="SKU" dir="ltr" class="<?= $inp ?> text-left">
                            <input name="var_price[]" value="<?= e($vr['price_override'] ?? '') ?>" placeholder="قیمت (خالی=قیمت اصلی)" dir="ltr" class="<?= $inp ?> text-left">
                            <div class="flex gap-1"><input name="var_stock[]" value="<?= e($vr['stock'] ?? 0) ?>" placeholder="موجودی" dir="ltr" class="<?= $inp ?> w-20 text-left"><button type="button" class="js-del-row rounded-lg px-2 text-danger">✕</button></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="mt-2 text-[11px] text-[#aaa]">اگر تنوع تعریف نشود، خرید بر اساس قیمت و موجودی اصلی انجام می‌شود.</p>
            </div>

            <!-- SEO -->
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">سئو</h3>
                <div class="mb-3"><label class="<?= $lbl ?>">عنوان سئو</label><input name="seo_title" value="<?= $v('seo_title') ?>" class="<?= $inp ?>"></div>
                <div><label class="<?= $lbl ?>">توضیحات متا</label><textarea name="seo_description" rows="2" class="<?= $inp ?>"><?= e($product['seo_description'] ?? '') ?></textarea></div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-5">
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">انتشار</h3>
                <?php foreach ([['is_active', 'فعال (نمایش در سایت)', 1], ['is_new', 'محصول جدید', 0], ['is_featured', 'ویژه', 0], ['on_flash_sale', 'پیشنهاد شگفت‌انگیز', 0]] as [$k, $label, $def]): ?>
                    <label class="mb-2.5 flex items-center justify-between">
                        <span class="text-[12.5px] text-[#555]"><?= e($label) ?></span>
                        <input type="checkbox" name="<?= $k ?>" value="1" class="h-5 w-5 accent-secondary" <?= $flag($k, $def) ? 'checked' : '' ?>>
                    </label>
                <?php endforeach; ?>
                <div class="mt-3 border-t border-line2 pt-3">
                    <label class="<?= $lbl ?>">ترتیب نمایش <span class="font-normal text-[#aaa]">(عدد بزرگ‌تر = بالاتر)</span></label>
                    <input name="sort" value="<?= $v('sort', '0') ?>" dir="ltr" class="<?= $inp ?> text-left">
                </div>
            </div>

            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">قیمت و انبار</h3>
                <div class="mb-3"><label class="<?= $lbl ?>">قیمت (تومان) *</label><input name="price" value="<?= $v('price', '0') ?>" dir="ltr" class="<?= $inp ?> text-left" required></div>
                <div class="mb-3"><label class="<?= $lbl ?>">قیمت قبل از تخفیف</label><input name="old_price" value="<?= $v('old_price') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
                <div class="mb-3"><label class="<?= $lbl ?>">موجودی</label><input name="stock" value="<?= $v('stock', '0') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
                <div class="mb-3"><label class="<?= $lbl ?>">آستانه هشدار موجودی</label><input name="low_stock_threshold" value="<?= $v('low_stock_threshold', '5') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
                <div class="mb-3"><label class="<?= $lbl ?>">SKU</label><input name="sku" value="<?= $v('sku') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
                <div class="mb-3"><label class="<?= $lbl ?>">بارکد</label><input name="barcode" value="<?= $v('barcode') ?>" dir="ltr" class="<?= $inp ?> text-left"></div>
                <div><label class="<?= $lbl ?>">تاریخ انقضا <span class="font-normal text-[#aaa]">(YYYY-MM-DD)</span></label><input name="expiration_date" value="<?= $v('expiration_date') ?>" dir="ltr" class="<?= $inp ?> text-left" placeholder="2027-08-01"></div>
            </div>

            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">دسته‌بندی و برند</h3>
                <div class="mb-3"><label class="<?= $lbl ?>">دسته‌بندی‌ها <span class="font-normal text-[#aaa]">(چند انتخابی)</span></label>
                    <div class="max-h-48 space-y-1 overflow-y-auto rounded-xl2 border border-line p-2.5">
                        <?php foreach ($categories as $c): $on = in_array((int) $c['id'], $categoryIds, true); ?>
                            <label class="flex items-center gap-2 rounded-lg px-2 py-1 hover:bg-surface">
                                <input type="checkbox" name="categories[]" value="<?= (int) $c['id'] ?>" class="h-4 w-4 accent-secondary" <?= $on ? 'checked' : '' ?>>
                                <span class="text-[12.5px] text-[#555]"><?= e($c['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="mt-1.5 text-[11px] text-[#aaa]">محصول در همهٔ دسته‌های انتخاب‌شده نمایش داده می‌شود. اولین دسته به‌عنوان دستهٔ اصلی (بردکرامب) استفاده می‌شود.</p>
                </div>
                <div><label class="<?= $lbl ?>">برند</label>
                    <select name="brand_id" class="<?= $inp ?>"><option value="">— انتخاب —</option>
                        <?php foreach ($brands as $b): ?><option value="<?= (int) $b['id'] ?>" <?= (int) ($product['brand_id'] ?? 0) === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if ($tags !== []): ?>
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">برچسب‌ها</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($tags as $t): $on = in_array((int) $t['id'], $tagIds, true); ?>
                        <label class="cursor-pointer">
                            <input type="checkbox" name="tags[]" value="<?= (int) $t['id'] ?>" class="peer sr-only" <?= $on ? 'checked' : '' ?>>
                            <span class="inline-block rounded-lg border px-3 py-1.5 text-[11.5px] transition peer-checked:border-secondary peer-checked:bg-pink peer-checked:text-secondary <?= $on ? 'border-secondary bg-pink text-secondary' : 'border-line text-[#777]' ?>"><?= e($t['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Images -->
            <div class="rounded-2xl border border-line2 bg-white p-5">
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">تصاویر</h3>
                <?php if ($images !== []): ?>
                    <div class="mb-3 space-y-3">
                        <?php foreach ($images as $img): ?>
                            <div class="js-image-tile flex gap-3 rounded-xl2 border border-line p-2.5" data-id="<?= (int) $img['id'] ?>">
                                <img src="<?= e(asset((string) $img['path'])) ?>" alt="" class="h-16 w-16 flex-none rounded-lg object-cover">
                                <div class="flex-1 space-y-1.5">
                                    <input name="img_alt[<?= (int) $img['id'] ?>]" value="<?= e($img['alt']) ?>" placeholder="متن جایگزین (alt)" class="w-full rounded-lg border border-line bg-surface px-2 py-1.5 text-[11.5px] outline-none">
                                    <input name="img_title[<?= (int) $img['id'] ?>]" value="<?= e($img['title']) ?>" placeholder="عنوان (title)" class="w-full rounded-lg border border-line bg-surface px-2 py-1.5 text-[11.5px] outline-none">
                                    <div class="flex items-center justify-between">
                                        <label class="flex items-center gap-1.5 text-[11px] text-[#666]"><input type="radio" name="primary_image" value="<?= (int) $img['id'] ?>" class="accent-secondary" <?= (int) $img['is_primary'] ? 'checked' : '' ?>> تصویر اصلی</label>
                                        <button type="button" class="js-del-image text-[11px] text-danger" data-url="<?= e(url('/admin/products/images/' . $img['id'] . '/delete')) ?>">حذف</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <label class="<?= $lbl ?>">افزودن تصویر</label>
                <input type="file" name="images[]" accept="image/*" multiple class="w-full text-[12px] text-[#666] file:mr-2 file:rounded-lg file:border-0 file:bg-pink file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-secondary">
                <p class="mt-1.5 text-[11px] text-[#aaa]">فرمت‌های مجاز: JPG, PNG, WEBP, GIF — حداکثر ۳ مگابایت.</p>
            </div>
        </div>
    </div>

    <!-- Hidden template rows for JS cloning -->
    <template id="tpl-spec"><div class="js-row flex gap-2"><input name="attr_key[]" placeholder="ویژگی" class="<?= $inp ?>"><input name="attr_value[]" placeholder="مقدار" class="<?= $inp ?>"><button type="button" class="js-del-row flex-none rounded-lg px-2 text-danger">✕</button></div></template>
    <template id="tpl-variant"><div class="js-row grid grid-cols-[1fr_1fr_1fr_auto] gap-2"><input name="var_label[]" placeholder="عنوان" class="<?= $inp ?>"><input name="var_sku[]" placeholder="SKU" dir="ltr" class="<?= $inp ?> text-left"><input name="var_price[]" placeholder="قیمت" dir="ltr" class="<?= $inp ?> text-left"><div class="flex gap-1"><input name="var_stock[]" placeholder="موجودی" dir="ltr" class="<?= $inp ?> w-20 text-left"><button type="button" class="js-del-row rounded-lg px-2 text-danger">✕</button></div></div></template>
</form>
