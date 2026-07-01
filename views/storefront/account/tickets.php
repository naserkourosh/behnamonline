<?php
/** @var list<array<string,mixed>> $tickets */
$this->meta(['title' => 'تیکت‌های پشتیبانی | بهنام', 'robots' => 'noindex']);
$st = ['open' => ['باز', 'bg-[#EEF2FF]', 'text-[#4F46E5]'], 'answered' => ['پاسخ داده‌شده', 'bg-[#E7F7F0]', 'text-success'], 'closed' => ['بسته‌شده', 'bg-surface', 'text-[#999]']];
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
?>
<div class="container-page py-6 md:py-8">
    <div class="mb-4 flex items-center gap-2">
        <a href="<?= e(url('/account')) ?>" class="text-secondary"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <h1 class="text-[18px] font-bold text-secondary md:text-[22px]">تیکت‌های پشتیبانی</h1>
    </div>

    <div class="md:flex md:items-start md:gap-8">
        <div class="md:flex-1">
            <!-- New ticket -->
            <details class="mb-5 rounded-2xl border border-line2 bg-white p-4" <?= $tickets === [] ? 'open' : '' ?>>
                <summary class="cursor-pointer text-[13.5px] font-bold text-secondary">+ ارسال تیکت جدید</summary>
                <form method="post" action="<?= e(url('/account/tickets')) ?>" class="mt-4 space-y-3">
                    <?= csrf_field() ?>
                    <div><label class="mb-1.5 block text-[12px] font-semibold text-[#666]">موضوع</label><input name="subject" class="<?= $inp ?>" required></div>
                    <div>
                        <label class="mb-1.5 block text-[12px] font-semibold text-[#666]">اولویت</label>
                        <select name="priority" class="<?= $inp ?>">
                            <option value="normal">عادی</option>
                            <option value="high">فوری</option>
                            <option value="low">کم</option>
                        </select>
                    </div>
                    <div><label class="mb-1.5 block text-[12px] font-semibold text-[#666]">پیام</label><textarea name="body" rows="4" class="<?= $inp ?>" required></textarea></div>
                    <button class="btn-primary px-6 py-2.5 text-[13px]">ارسال تیکت</button>
                </form>
            </details>

            <?php if ($tickets === []): ?>
                <div class="rounded-2xl border border-line2 bg-surface py-14 text-center text-[13px] text-[#666]">هنوز تیکتی ثبت نکرده‌اید.</div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($tickets as $t): [$label, $bg, $tx] = $st[$t['status']] ?? $st['open']; ?>
                        <a href="<?= e(url('/account/tickets/' . $t['id'])) ?>" class="block rounded-2xl border border-line2 bg-white p-4 hover:border-secondary">
                            <div class="flex items-center justify-between">
                                <span class="text-[13.5px] font-bold text-[#333]"><?= e($t['subject']) ?></span>
                                <span class="badge <?= $bg ?> <?= $tx ?>"><?= e($label) ?></span>
                            </div>
                            <div class="mt-2 text-[10.5px] text-[#999] nums">آخرین به‌روزرسانی: <?= jdate((string) ($t['last_reply_at'] ?: $t['created_at'])) ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <aside class="mt-6 hidden md:mt-0 md:block md:w-72 md:flex-none"><?php $this->partial('account-nav'); ?></aside>
    </div>
</div>
