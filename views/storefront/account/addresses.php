<?php
/** @var list<array<string,mixed>> $addresses */
/** @var array<string,mixed> $provinces */
$this->meta(['title' => 'آدرس‌های من | بهنام', 'robots' => 'noindex']);
?>
<div class="container-page py-6 md:py-8" id="addresses-page">
    <div class="mb-4 flex items-center gap-2">
        <a href="<?= e(url('/account')) ?>" class="text-secondary"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <h1 class="text-[18px] font-bold text-secondary md:text-[22px]">آدرس‌های من</h1>
    </div>

    <div class="md:flex md:items-start md:gap-8">
        <div class="md:flex-1">
            <!-- list -->
            <div class="space-y-3">
                <?php foreach ($addresses as $a): ?>
                    <div class="rounded-2xl border border-line2 bg-white p-4"
                         data-addr='<?= e(json_encode($a, JSON_UNESCAPED_UNICODE)) ?>'>
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-[13px] font-bold text-[#333]"><?= e($a['receiver_name']) ?></span>
                                <?php if ((int) $a['is_default'] === 1): ?><span class="badge bg-pink text-secondary">پیش‌فرض</span><?php endif; ?>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" class="js-addr-edit text-[11.5px] text-secondary">ویرایش</button>
                                <form method="post" action="<?= e(url('/account/addresses/' . $a['id'] . '/delete')) ?>" onsubmit="return confirm('حذف این آدرس؟')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-[11.5px] text-danger">حذف</button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-2 text-[12px] leading-7 text-[#666]">
                            <?= e($a['province']) ?>، <?= e($a['city']) ?> — <?= e($a['address']) ?>
                            <span class="block text-[11px] text-[#999] nums" dir="ltr"><?= e($a['mobile']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($addresses === []): ?>
                    <div class="rounded-2xl border border-line2 bg-surface py-10 text-center text-[13px] text-[#999]">هنوز آدرسی ثبت نکرده‌اید.</div>
                <?php endif; ?>
            </div>

            <!-- add / edit form -->
            <form method="post" action="<?= e(url('/account/addresses')) ?>" id="addr-form" class="mt-5 rounded-2xl border border-line2 bg-white p-5">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="addr-id" value="">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-[14px] font-bold text-secondary" id="addr-form-title">افزودن آدرس جدید</span>
                    <button type="button" class="js-addr-reset hidden text-[11.5px] text-mauve">انصراف از ویرایش</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="mb-1.5 block text-[11px] text-[#888]">نام تحویل‌گیرنده</label><input name="receiver_name" id="addr-receiver" class="ck-input" placeholder="نام و نام خانوادگی"></div>
                    <div><label class="mb-1.5 block text-[11px] text-[#888]">تلفن همراه</label><input name="mobile" id="addr-mobile" dir="ltr" inputmode="numeric" class="ck-input text-left" placeholder="09xxxxxxxxx"></div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-[11px] text-[#888]">استان</label>
                        <select name="province" id="addr-province" class="ck-input">
                            <option value="">انتخاب استان</option>
                            <?php foreach (array_keys((array) $provinces) as $prov): ?><option value="<?= e($prov) ?>"><?= e($prov) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div><label class="mb-1.5 block text-[11px] text-[#888]">شهر</label><select name="city" id="addr-city" class="ck-input"><option value="">ابتدا استان</option></select></div>
                </div>
                <div class="mt-3"><label class="mb-1.5 block text-[11px] text-[#888]">آدرس کامل</label><textarea name="address" id="addr-address" rows="2" class="ck-input resize-none" placeholder="خیابان، کوچه، پلاک، واحد"></textarea></div>
                <div class="mt-3 grid grid-cols-2 items-end gap-3">
                    <div><label class="mb-1.5 block text-[11px] text-[#888]">کد پستی <span class="text-[#bbb]">(اختیاری)</span></label><input name="postal_code" id="addr-postal" inputmode="numeric" class="ck-input" placeholder="۱۰ رقم"></div>
                    <label class="flex items-center gap-2 pb-2.5 text-[12px] text-[#555]"><input type="checkbox" name="is_default" id="addr-default" value="1" class="h-4 w-4 accent-secondary"> آدرس پیش‌فرض</label>
                </div>
                <button type="submit" class="btn-primary mt-4 w-full py-3 text-[13px]">ذخیره آدرس</button>
            </form>
        </div>
        <aside class="mt-6 hidden md:mt-0 md:block md:w-72 md:flex-none"><?php $this->partial('account-nav'); ?></aside>
    </div>

    <script type="application/json" id="addr-geo"><?= json_encode($provinces, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</div>
