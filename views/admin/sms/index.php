<?php
/** @var list<array<string,mixed>> $items @var list<array<string,mixed>> $templates */
/** @var array{kind:string,search:string} $filters @var string $driver */
/** @var int $total @var int $page @var int $pages */
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$kinds = ['otp' => 'کد تایید', 'order' => 'سفارش', 'manual' => 'دستی', 'system' => 'سیستمی'];
$kindBadge = static function (string $k) use ($kinds): string {
    $map = ['otp' => 'bg-[#EEF2FF] text-[#4F46E5]', 'order' => 'bg-[#E7F7F0] text-success', 'manual' => 'bg-pink text-secondary', 'system' => 'bg-surface text-[#888]'];
    $cls = $map[$k] ?? 'bg-surface text-[#888]';
    return '<span class="rounded-lg px-2 py-0.5 text-[10.5px] font-bold ' . $cls . '">' . e($kinds[$k] ?? $k) . '</span>';
};
?>
<div class="grid gap-5 lg:grid-cols-3">
    <!-- Send + templates -->
    <div class="space-y-5">
        <div class="rounded-2xl border border-line2 bg-white p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-[14px] font-bold text-[#333]">ارسال پیامک</h3>
                <span class="rounded-lg bg-surface px-2 py-1 text-[10.5px] font-bold text-[#888]">درگاه: <?= e($driver) ?></span>
            </div>
            <form method="post" action="<?= e(url('/admin/sms/send')) ?>">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="<?= $lbl ?>">موبایل گیرنده</label><input name="mobile" dir="ltr" placeholder="09121234567" class="<?= $inp ?> text-left"></div>
                <div class="mb-3"><label class="<?= $lbl ?>">متن پیامک</label><textarea name="message" rows="4" class="<?= $inp ?>" placeholder="متن پیامک…"></textarea></div>
                <button class="btn-primary w-full py-2.5 text-[13px]">ارسال</button>
            </form>
            <?php if ($driver === 'mock'): ?>
                <p class="mt-2.5 text-[11px] text-[#aaa]">حالت آزمایشی: پیامک‌ها به‌جای ارسال واقعی در <code dir="ltr">storage/logs/sms.log</code> ثبت می‌شوند.</p>
            <?php endif; ?>
        </div>

        <div class="rounded-2xl border border-line2 bg-white p-5">
            <h3 class="mb-3 text-[14px] font-bold text-[#333]">قالب‌های خودکار</h3>
            <form method="post" action="<?= e(url('/admin/sms/templates')) ?>">
                <?= csrf_field() ?>
                <?php foreach ($templates as $t): ?>
                    <div class="mb-4">
                        <label class="mb-1.5 flex items-center justify-between">
                            <span class="text-[12px] font-semibold text-[#555]"><?= e($t['title']) ?></span>
                            <label class="flex items-center gap-1.5 text-[11px] text-[#888]"><input type="checkbox" name="active_<?= e($t['tkey']) ?>" value="1" class="h-4 w-4 accent-secondary" <?= (int) $t['is_active'] ? 'checked' : '' ?>> فعال</label>
                        </label>
                        <textarea name="body_<?= e($t['tkey']) ?>" rows="3" class="<?= $inp ?> text-[12px]"><?= e($t['body']) ?></textarea>
                    </div>
                <?php endforeach; ?>
                <p class="mb-3 text-[11px] text-[#aaa]">متغیرها: <code dir="ltr">{order}</code> شمارهٔ سفارش، <code dir="ltr">{tracking}</code> کد رهگیری.</p>
                <button class="w-full rounded-xl2 bg-surface py-2.5 text-[13px] font-semibold text-secondary">ذخیره قالب‌ها</button>
            </form>
        </div>
    </div>

    <!-- History -->
    <div class="lg:col-span-2">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-[14px] font-bold text-[#333]">تاریخچهٔ پیامک‌ها <span class="text-[12px] font-normal text-[#999] nums">(<?= fa($total) ?>)</span></h3>
            <form method="get" action="<?= e(url('/admin/sms')) ?>" class="flex items-center gap-2">
                <select name="kind" class="rounded-xl2 border border-line bg-white px-3 py-2 text-[12.5px] outline-none focus:border-secondary">
                    <option value="">همهٔ انواع</option>
                    <?php foreach ($kinds as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= $filters['kind'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <input name="q" value="<?= e($filters['search']) ?>" placeholder="موبایل یا متن…" class="w-40 rounded-xl2 border border-line bg-white px-3 py-2 text-[12.5px] outline-none focus:border-secondary">
                <button class="rounded-xl2 bg-surface px-4 py-2 text-[12.5px] font-semibold text-secondary">فیلتر</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-line2 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-[12.5px]">
                    <thead>
                        <tr class="border-b border-line bg-surface text-[#888]">
                            <th class="p-3 text-right font-semibold">موبایل</th>
                            <th class="p-3 text-right font-semibold">متن</th>
                            <th class="p-3 font-semibold">نوع</th>
                            <th class="p-3 font-semibold">وضعیت</th>
                            <th class="p-3 font-semibold">زمان</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $m): ?>
                            <tr class="border-b border-line2 last:border-0 hover:bg-surface/50 align-top">
                                <td class="p-3 nums" dir="ltr"><?= e($m['mobile']) ?></td>
                                <td class="p-3 text-[#555] whitespace-pre-line max-w-[280px]"><?= e($m['body']) ?></td>
                                <td class="p-3 text-center"><?= $kindBadge((string) $m['kind']) ?></td>
                                <td class="p-3 text-center">
                                    <?php if ((string) $m['status'] === 'sent'): ?>
                                        <span class="rounded-lg bg-[#E7F7F0] px-2 py-0.5 text-[10.5px] font-bold text-success">ارسال شد</span>
                                    <?php else: ?>
                                        <span class="rounded-lg bg-[#FDECEC] px-2 py-0.5 text-[10.5px] font-bold text-danger">ناموفق</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 text-center text-[11px] text-[#999] nums"><?= e(jdate((string) $m['created_at'], 'H:i Y/m/d')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($items === []): ?>
                            <tr><td colspan="5" class="p-8 text-center text-[#999]">پیامکی ثبت نشده است.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php $this->partial('admin/pagination', ['page' => $page, 'pages' => $pages, 'baseUrl' => url('/admin/sms') . '?kind=' . urlencode($filters['kind']) . '&q=' . urlencode($filters['search']) . '&']); ?>
    </div>
</div>
