<?php
/** @var array<string,mixed> $ticket @var list<array<string,mixed>> $messages */
$name = trim(($ticket['first_name'] ?? '') . ' ' . ($ticket['last_name'] ?? ''));
$badge = ['open' => 'bg-[#EEF2FF] text-[#4F46E5]', 'answered' => 'bg-[#E7F7F0] text-success', 'closed' => 'bg-surface text-[#999]'];
$blab  = ['open' => 'باز', 'answered' => 'پاسخ‌داده', 'closed' => 'بسته'];
?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <a href="<?= e(url('/admin/tickets')) ?>" class="text-[12px] text-mauve">‹ بازگشت به تیکت‌ها</a>
    <form method="post" action="<?= e(url('/admin/tickets/' . $ticket['id'] . '/status')) ?>" class="flex items-center gap-2">
        <?= csrf_field() ?>
        <select name="status" class="rounded-xl2 border border-line bg-white px-3 py-1.5 text-[12px] outline-none">
            <?php foreach ($blab as $k => $label): ?><option value="<?= e($k) ?>" <?= (string) $ticket['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
        </select>
        <button class="rounded-xl2 bg-surface px-3 py-1.5 text-[12px] font-semibold text-secondary">تغییر وضعیت</button>
    </form>
</div>

<div class="grid gap-5 lg:grid-cols-4">
    <div class="lg:col-span-3">
        <div class="mb-4 rounded-2xl border border-line2 bg-white p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-bold text-secondary"><?= e($ticket['subject']) ?></h2>
                <span class="rounded-lg px-2 py-0.5 text-[10.5px] font-bold <?= $badge[$ticket['status']] ?? '' ?>"><?= e($blab[$ticket['status']] ?? '') ?></span>
            </div>
        </div>

        <div class="space-y-3">
            <?php foreach ($messages as $m): $admin = (string) $m['sender'] === 'admin'; ?>
                <div class="flex <?= $admin ? 'justify-end' : 'justify-start' ?>">
                    <div class="max-w-[85%] rounded-2xl border p-3.5 <?= $admin ? 'border-pink bg-pink' : 'border-line2 bg-white' ?>">
                        <div class="mb-1.5 flex items-center gap-2 text-[10.5px] <?= $admin ? 'text-secondary' : 'text-[#999]' ?>">
                            <span class="font-bold"><?= $admin ? 'پشتیبانی' : ($name ?: 'مشتری') ?></span>
                            <span class="nums"><?= jdate((string) $m['created_at'], 'H:i Y/m/d') ?></span>
                        </div>
                        <p class="text-[12.5px] leading-7 text-[#444]"><?= nl2br(e($m['body'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ((string) $ticket['status'] !== 'closed'): ?>
            <form method="post" action="<?= e(url('/admin/tickets/' . $ticket['id'] . '/reply')) ?>" class="mt-5 rounded-2xl border border-line2 bg-white p-4">
                <?= csrf_field() ?>
                <textarea name="body" rows="3" placeholder="پاسخ پشتیبانی…" class="w-full rounded-xl2 border border-line bg-surface px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary" required></textarea>
                <div class="mt-3 flex items-center justify-between">
                    <label class="flex items-center gap-1.5 text-[11.5px] text-[#888]"><input type="checkbox" name="close" value="1" class="h-4 w-4 accent-secondary"> بستن تیکت پس از ارسال</label>
                    <button class="btn-primary px-6 py-2.5 text-[13px]">ارسال پاسخ</button>
                </div>
            </form>
        <?php else: ?>
            <div class="mt-5 rounded-2xl bg-surface py-4 text-center text-[12.5px] text-[#888]">این تیکت بسته شده است.</div>
        <?php endif; ?>
    </div>

    <aside>
        <div class="rounded-2xl border border-line2 bg-white p-4 text-[12px]">
            <h3 class="mb-3 text-[13px] font-bold text-[#333]">اطلاعات مشتری</h3>
            <div class="mb-2"><span class="text-[#999]">نام:</span> <span class="font-semibold text-[#444]"><?= e($name ?: '—') ?></span></div>
            <div class="mb-2"><span class="text-[#999]">موبایل:</span> <span class="font-semibold text-[#444] nums" dir="ltr"><?= e($ticket['mobile']) ?></span></div>
            <div><span class="text-[#999]">ایجاد:</span> <span class="nums text-[#666]"><?= jdate((string) $ticket['created_at'], 'H:i Y/m/d') ?></span></div>
        </div>
    </aside>
</div>
