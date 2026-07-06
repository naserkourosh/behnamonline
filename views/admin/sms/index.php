<?php
/** @var list<array<string,mixed>> $items @var list<array<string,mixed>> $templates */
/** @var list<array<string,mixed>> $campaigns @var array<string,string> $audiences */
/** @var array{kind:string,search:string} $filters @var string $driver */
/** @var array{username:string,password:bool,from:string,body_id:string} $config */
/** @var int $total @var int $page @var int $pages */
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$kinds = ['otp' => 'کد تایید', 'order' => 'سفارش', 'manual' => 'دستی', 'campaign' => 'کمپین', 'system' => 'سیستمی'];
$kindBadge = static function (string $k) use ($kinds): string {
    $map = ['otp' => 'bg-[#EEF2FF] text-[#4F46E5]', 'order' => 'bg-[#E7F7F0] text-success', 'manual' => 'bg-pink text-secondary', 'campaign' => 'bg-[#FFF4E5] text-[#B45309]', 'system' => 'bg-surface text-[#888]'];
    $cls = $map[$k] ?? 'bg-surface text-[#888]';
    return '<span class="rounded-lg px-2 py-0.5 text-[10.5px] font-bold ' . $cls . '">' . e($kinds[$k] ?? $k) . '</span>';
};
$isReal = $driver === 'melipayamak';
?>

<!-- Status strip -->
<div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-4">
    <div class="rounded-2xl border border-line2 bg-white p-4">
        <div class="mb-1 text-[11px] font-semibold text-[#999]">درگاه ارسال</div>
        <?php if ($isReal): ?>
            <div class="text-[13.5px] font-bold text-success">ملی‌پیامک (واقعی)</div>
            <div class="mt-1 text-[10.5px] text-[#aaa]" dir="ltr">rest.payamak-panel.com</div>
        <?php else: ?>
            <div class="text-[13.5px] font-bold text-[#B45309]">آزمایشی (mock)</div>
            <div class="mt-1 text-[10.5px] text-[#aaa]">بدون ارسال واقعی — ثبت در sms.log</div>
        <?php endif; ?>
    </div>
    <div class="rounded-2xl border border-line2 bg-white p-4">
        <div class="mb-1 text-[11px] font-semibold text-[#999]">خط فرستنده</div>
        <?php if ($config['from'] !== ''): ?>
            <div class="text-[13.5px] font-bold text-[#333] nums" dir="ltr"><?= e($config['from']) ?></div>
        <?php else: ?>
            <div class="text-[13.5px] font-bold text-danger">تنظیم نشده</div>
            <div class="mt-1 text-[10.5px] text-[#aaa]">از فرم «اتصال به پنل» وارد کنید</div>
        <?php endif; ?>
    </div>
    <div class="rounded-2xl border border-line2 bg-white p-4">
        <div class="mb-1 flex items-center justify-between">
            <span class="text-[11px] font-semibold text-[#999]">اعتبار پنل</span>
            <?php if ($isReal): ?>
                <button type="button" id="js-sms-credit-btn" class="rounded-lg bg-surface px-2 py-0.5 text-[10.5px] font-bold text-secondary hover:bg-pink">بروزرسانی</button>
            <?php endif; ?>
        </div>
        <div id="js-sms-credit" class="text-[13.5px] font-bold text-[#333] nums"><?= $isReal ? '…' : '—' ?></div>
        <?php if (!$isReal): ?><div class="mt-1 text-[10.5px] text-[#aaa]">در حالت آزمایشی اعتبار ندارد</div><?php endif; ?>
    </div>
    <div class="rounded-2xl border border-line2 bg-white p-4">
        <div class="mb-1 text-[11px] font-semibold text-[#999]">پترن کد تایید (خط خدماتی)</div>
        <?php if ($config['body_id'] !== ''): ?>
            <div class="text-[13.5px] font-bold text-success">فعال <span class="nums text-[11px] text-[#999]" dir="ltr">(bodyId: <?= e($config['body_id']) ?>)</span></div>
        <?php else: ?>
            <div class="text-[13.5px] font-bold text-[#B45309]">تنظیم نشده</div>
            <div class="mt-1 text-[10.5px] text-[#aaa]">کد تایید از خط عادی ارسال می‌شود</div>
        <?php endif; ?>
    </div>
</div>

<div class="grid gap-5 lg:grid-cols-3">
    <!-- Connection + send + templates -->
    <div class="space-y-5">
        <!-- Panel connection settings (stored in DB, overrides .env) -->
        <div class="rounded-2xl border border-line2 bg-white p-5">
            <h3 class="mb-3 text-[14px] font-bold text-[#333]">اتصال به پنل ملی‌پیامک</h3>
            <form method="post" action="<?= e(url('/admin/sms/settings')) ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="<?= $lbl ?>">درگاه ارسال</label>
                    <select name="sms_driver" class="<?= $inp ?>">
                        <option value="mock" <?= !$isReal ? 'selected' : '' ?>>آزمایشی (بدون ارسال واقعی)</option>
                        <option value="melipayamak" <?= $isReal ? 'selected' : '' ?>>ملی‌پیامک (واقعی)</option>
                    </select>
                </div>
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="<?= $lbl ?>">نام کاربری پنل</label>
                        <input name="sms_username" dir="ltr" value="<?= e($config['username']) ?>" class="<?= $inp ?> text-left" autocomplete="off">
                    </div>
                    <div>
                        <label class="<?= $lbl ?>">رمز عبور پنل</label>
                        <input type="password" name="sms_password" dir="ltr" placeholder="<?= $config['password'] ? '●●●●● (بدون تغییر)' : '' ?>" class="<?= $inp ?> text-left" autocomplete="new-password">
                    </div>
                </div>
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="<?= $lbl ?>">شماره خط فرستنده</label>
                        <input name="sms_from" dir="ltr" value="<?= e($config['from']) ?>" placeholder="50004001…" class="<?= $inp ?> text-left">
                    </div>
                    <div>
                        <label class="<?= $lbl ?>">کد پترن کد تایید</label>
                        <input name="sms_otp_body_id" dir="ltr" value="<?= e($config['body_id']) ?>" placeholder="bodyId" class="<?= $inp ?> text-left">
                    </div>
                </div>
                <p class="mb-3 text-[11px] leading-5 text-[#aaa]">نام کاربری و رمز، همان ورودِ melipayamak.com است (کلید API لازم نیست). «کد پترن کد تایید» شمارهٔ متن خدماتیِ تاییدشده برای ارسال کد ورود از خط خدماتی است. مقادیر اینجا بر <code dir="ltr">.env</code> اولویت دارند.</p>
                <button class="btn-primary w-full py-2.5 text-[13px]">ذخیره تنظیمات اتصال</button>
            </form>
        </div>

        <div class="rounded-2xl border border-line2 bg-white p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-[14px] font-bold text-[#333]">ارسال تکی</h3>
                <span class="rounded-lg bg-surface px-2 py-1 text-[10.5px] font-bold text-[#888]">درگاه: <?= e($driver) ?></span>
            </div>
            <form method="post" action="<?= e(url('/admin/sms/send')) ?>">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="<?= $lbl ?>">موبایل گیرنده</label><input name="mobile" dir="ltr" placeholder="09121234567" class="<?= $inp ?> text-left"></div>
                <div class="mb-3"><label class="<?= $lbl ?>">متن پیامک</label><textarea name="message" rows="4" class="<?= $inp ?> js-sms-body" placeholder="متن پیامک…"></textarea>
                    <div class="mt-1 text-left text-[10.5px] text-[#aaa] nums js-sms-counter"></div></div>
                <button class="btn-primary w-full py-2.5 text-[13px]">ارسال</button>
            </form>
            <?php if (!$isReal): ?>
                <p class="mt-2.5 text-[11px] text-[#aaa]">حالت آزمایشی: پیامک‌ها به‌جای ارسال واقعی در <code dir="ltr">storage/logs/sms.log</code> ثبت می‌شوند.</p>
            <?php endif; ?>
        </div>

        <div class="rounded-2xl border border-line2 bg-white p-5">
            <h3 class="mb-3 text-[14px] font-bold text-[#333]">قالب‌های خودکار</h3>
            <?php
            // Variable order per template = the order the code passes them to
            // sendTemplate(); a Melipayamak pattern MUST list them the same way.
            $patternVars = [
                'order_ready'       => '{name} ؛ {order} ؛ {products}',
                'payment_confirmed' => '{order} ؛ {tracking}',
                'order_paid'        => '{order} ؛ {tracking}',
                'order_shipped'     => '{order} ؛ {tracking}',
            ];
            ?>
            <form method="post" action="<?= e(url('/admin/sms/templates')) ?>">
                <?= csrf_field() ?>
                <?php foreach ($templates as $t): ?>
                    <div class="mb-4 rounded-xl2 border border-line2 p-3">
                        <label class="mb-1.5 flex items-center justify-between">
                            <span class="text-[12px] font-semibold text-[#555]"><?= e($t['title']) ?></span>
                            <label class="flex items-center gap-1.5 text-[11px] text-[#888]"><input type="checkbox" name="active_<?= e($t['tkey']) ?>" value="1" class="h-4 w-4 accent-secondary" <?= (int) $t['is_active'] ? 'checked' : '' ?>> فعال</label>
                        </label>
                        <textarea name="body_<?= e($t['tkey']) ?>" rows="3" class="<?= $inp ?> text-[12px]"><?= e($t['body']) ?></textarea>
                        <div class="mt-2 flex items-center gap-2">
                            <label class="shrink-0 text-[11px] font-semibold text-[#888]">کد پترن (خدماتی):</label>
                            <input name="pattern_<?= e($t['tkey']) ?>" dir="ltr" value="<?= e((string) ($t['pattern_body_id'] ?? '')) ?>" placeholder="bodyId — خالی: ارسال از خط عادی" class="<?= $inp ?> !py-1.5 text-left text-[11.5px]">
                        </div>
                        <?php if (isset($patternVars[(string) $t['tkey']])): ?>
                            <p class="mt-1.5 text-[10.5px] leading-5 text-[#aaa]">ترتیب متغیرهای پترن در پنل ملی‌پیامک: <span class="nums" dir="rtl"><?= e($patternVars[(string) $t['tkey']]) ?></span></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <p class="mb-3 text-[11px] leading-5 text-[#aaa]">متغیرها: <code dir="ltr">{order}</code> شمارهٔ سفارش، <code dir="ltr">{tracking}</code> کد رهگیری، <code dir="ltr">{name}</code> نام مشتری، <code dir="ltr">{products}</code> اقلام سفارش. اگر «کد پترن» پر شود، پیام از خط خدماتی با همان الگوی تاییدشده ارسال می‌شود و متن بالا فقط برای حالت بدون پترن است.</p>
                <button class="w-full rounded-xl2 bg-surface py-2.5 text-[13px] font-semibold text-secondary">ذخیره قالب‌ها</button>
            </form>
        </div>
    </div>

    <!-- Campaign + history -->
    <div class="space-y-5 lg:col-span-2">
        <!-- Group send -->
        <div class="rounded-2xl border border-line2 bg-white p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-[14px] font-bold text-[#333]">ارسال گروهی (کمپین تبلیغاتی)</h3>
                <span id="js-aud-count" class="rounded-lg bg-pink px-2.5 py-1 text-[11px] font-bold text-secondary nums">…</span>
            </div>
            <form method="post" action="<?= e(url('/admin/sms/campaign')) ?>" id="js-campaign-form">
                <?= csrf_field() ?>
                <div class="mb-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="<?= $lbl ?>">عنوان کمپین (اختیاری)</label>
                        <input name="title" class="<?= $inp ?>" placeholder="مثلاً: تخفیف عید">
                    </div>
                    <div>
                        <label class="<?= $lbl ?>">گیرندگان</label>
                        <select name="audience" id="js-audience" class="<?= $inp ?>">
                            <?php foreach ($audiences as $k => $label): ?>
                                <option value="<?= e($k) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 hidden" id="js-custom-numbers">
                    <label class="<?= $lbl ?>">شماره‌ها (با کاما، فاصله یا خط جدید جدا کنید)</label>
                    <textarea name="numbers" rows="3" dir="ltr" class="<?= $inp ?> text-left" placeholder="09121234567, 09351234567"></textarea>
                </div>
                <div class="mb-3">
                    <label class="<?= $lbl ?>">متن پیام</label>
                    <textarea name="message" rows="4" class="<?= $inp ?> js-sms-body" placeholder="متن پیامک تبلیغاتی…"></textarea>
                    <div class="mt-1 flex items-center justify-between text-[10.5px] text-[#aaa]">
                        <span>برای امکان لغو ۱۱، «لغو۱۱» را در انتهای متن تبلیغاتی اضافه کنید.</span>
                        <span class="nums js-sms-counter"></span>
                    </div>
                </div>
                <div class="mb-3 rounded-xl2 bg-[#FFF9EC] border border-[#F2E3C4] p-3 text-[11px] leading-6 text-[#8a6d3b]">
                    ⚠️ پیامک تبلیغاتی از خط عادی به شماره‌هایی که «دریافت پیامک تبلیغاتی» را مسدود کرده‌اند <b>تحویل نمی‌شود</b> و اعتبار آن‌ها کسر می‌گردد. پیامک‌های ضروری (کد تایید، وضعیت سفارش) را از طریق خط خدماتی/پترن ارسال کنید.
                </div>
                <button class="btn-primary w-full py-2.5 text-[13px]" id="js-campaign-submit">ارسال به گروه انتخاب‌شده</button>
            </form>
        </div>

        <!-- Recent campaigns -->
        <?php if ($campaigns !== []): ?>
        <div class="overflow-hidden rounded-2xl border border-line2 bg-white">
            <div class="border-b border-line2 p-4"><h3 class="text-[14px] font-bold text-[#333]">کمپین‌های اخیر</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-[12.5px]">
                    <thead>
                        <tr class="border-b border-line bg-surface text-[#888]">
                            <th class="p-3 text-right font-semibold">عنوان</th>
                            <th class="p-3 font-semibold">گیرندگان</th>
                            <th class="p-3 font-semibold">موفق</th>
                            <th class="p-3 font-semibold">ناموفق</th>
                            <th class="p-3 font-semibold">وضعیت</th>
                            <th class="p-3 font-semibold">زمان</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campaigns as $c): ?>
                            <tr class="border-b border-line2 last:border-0 hover:bg-surface/50">
                                <td class="p-3 font-semibold text-[#444]"><?= e($c['title']) ?><div class="mt-0.5 max-w-[260px] truncate text-[10.5px] font-normal text-[#aaa]"><?= e($c['body']) ?></div></td>
                                <td class="p-3 text-center text-[11.5px]"><?= e($audiences[(string) $c['audience']] ?? $c['audience']) ?> <span class="nums text-[#999]">(<?= fa((int) $c['total']) ?>)</span></td>
                                <td class="p-3 text-center nums text-success font-bold"><?= fa((int) $c['sent']) ?></td>
                                <td class="p-3 text-center nums <?= (int) $c['failed'] > 0 ? 'text-danger font-bold' : 'text-[#bbb]' ?>"><?= fa((int) $c['failed']) ?></td>
                                <td class="p-3 text-center">
                                    <?php $st = (string) $c['status']; ?>
                                    <?php if ($st === 'done'): ?><span class="rounded-lg bg-[#E7F7F0] px-2 py-0.5 text-[10.5px] font-bold text-success">کامل</span>
                                    <?php elseif ($st === 'partial'): ?><span class="rounded-lg bg-[#FFF4E5] px-2 py-0.5 text-[10.5px] font-bold text-[#B45309]">ناقص</span>
                                    <?php elseif ($st === 'failed'): ?><span class="rounded-lg bg-[#FDECEC] px-2 py-0.5 text-[10.5px] font-bold text-danger">ناموفق</span>
                                    <?php else: ?><span class="rounded-lg bg-surface px-2 py-0.5 text-[10.5px] font-bold text-[#888]">در حال ارسال</span><?php endif; ?>
                                </td>
                                <td class="p-3 text-center text-[11px] text-[#999] nums"><?= e(jdate((string) $c['created_at'], 'H:i Y/m/d')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- History -->
        <div>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var fa = function (n) { return String(n).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[+d]; }); };

    // ── Character / SMS-page counter (unicode: 70 first page, 63 per part) ──
    document.querySelectorAll('.js-sms-body').forEach(function (ta) {
        var counter = ta.parentElement.querySelector('.js-sms-counter');
        if (!counter) { return; }
        var update = function () {
            var len = ta.value.length;
            if (len === 0) { counter.textContent = ''; return; }
            var ascii = /^[\x00-\x7F]*$/.test(ta.value);
            var pages = ascii
                ? (len <= 160 ? 1 : Math.ceil(len / 153))
                : (len <= 70 ? 1 : Math.ceil(len / 63));
            counter.textContent = fa(len) + ' کاراکتر · ' + fa(pages) + ' پیامک';
        };
        ta.addEventListener('input', update);
        update();
    });

    // ── Audience live count + custom numbers box ──
    var audSel = document.getElementById('js-audience');
    var audCount = document.getElementById('js-aud-count');
    var customBox = document.getElementById('js-custom-numbers');
    var refreshAudience = function () {
        if (!audSel) return;
        if (audSel.value === 'custom') {
            customBox.classList.remove('hidden');
            var raw = customBox.querySelector('textarea').value;
            var nums = raw.split(/[\s,،;]+/).filter(function (t) { return /^09\d{9}$/.test(t.replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); })); });
            audCount.textContent = fa([...new Set(nums)].length) + ' گیرنده';
            return;
        }
        customBox.classList.add('hidden');
        audCount.textContent = '…';
        fetch('<?= e(url('/admin/sms/audience-count')) ?>?audience=' + encodeURIComponent(audSel.value), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (d) { audCount.textContent = fa(d.count || 0) + ' گیرنده'; })
            .catch(function () { audCount.textContent = '؟'; });
    };
    if (audSel) {
        audSel.addEventListener('change', refreshAudience);
        customBox.querySelector('textarea').addEventListener('input', refreshAudience);
        refreshAudience();
    }

    // ── Confirm before group send ──
    var campaignForm = document.getElementById('js-campaign-form');
    if (campaignForm) {
        campaignForm.addEventListener('submit', function (e) {
            var msg = campaignForm.querySelector('.js-sms-body').value.trim();
            if (msg === '') { e.preventDefault(); alert('متن پیام را وارد کنید.'); return; }
            if (!confirm('پیامک برای «' + audSel.options[audSel.selectedIndex].text + '» (' + audCount.textContent + ') ارسال شود؟')) {
                e.preventDefault();
                return;
            }
            document.getElementById('js-campaign-submit').disabled = true;
            document.getElementById('js-campaign-submit').textContent = 'در حال ارسال…';
        });
    }

    // ── Panel credit ──
    var creditEl = document.getElementById('js-sms-credit');
    var creditBtn = document.getElementById('js-sms-credit-btn');
    var loadCredit = function () {
        creditEl.textContent = '…';
        fetch('<?= e(url('/admin/sms/credit')) ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                creditEl.textContent = (d.credit === null || d.credit === undefined)
                    ? 'نامشخص'
                    : fa(Math.floor(d.credit).toLocaleString('en-US').replace(/,/g, '،')) + ' پیامک';
            })
            .catch(function () { creditEl.textContent = 'خطا'; });
    };
    if (creditBtn) { creditBtn.addEventListener('click', loadCredit); loadCredit(); }
});
</script>
