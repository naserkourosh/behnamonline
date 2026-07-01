<?php
/** @var array<string,mixed> $ticket @var list<array<string,mixed>> $messages */
$this->meta(['title' => 'تیکت ' . $ticket['subject'] . ' | بهنام', 'robots' => 'noindex']);
$st = ['open' => ['باز', 'bg-[#EEF2FF]', 'text-[#4F46E5]'], 'answered' => ['پاسخ داده‌شده', 'bg-[#E7F7F0]', 'text-success'], 'closed' => ['بسته‌شده', 'bg-surface', 'text-[#999]']];
[$label, $bg, $tx] = $st[$ticket['status']] ?? $st['open'];
?>
<div class="container-page max-w-3xl py-6 md:py-8">
    <div class="mb-4 flex items-center gap-2">
        <a href="<?= e(url('/account/tickets')) ?>" class="text-secondary"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <h1 class="flex-1 text-[17px] font-bold text-secondary md:text-[20px]"><?= e($ticket['subject']) ?></h1>
        <span class="badge <?= $bg ?> <?= $tx ?>"><?= e($label) ?></span>
    </div>

    <div class="space-y-3">
        <?php foreach ($messages as $m): $mine = (string) $m['sender'] === 'customer'; ?>
            <div class="flex <?= $mine ? 'justify-start' : 'justify-end' ?>">
                <div class="max-w-[85%] rounded-2xl border p-3.5 <?= $mine ? 'border-line2 bg-white' : 'border-pink bg-pink' ?>">
                    <div class="mb-1.5 flex items-center gap-2 text-[10.5px] <?= $mine ? 'text-[#999]' : 'text-secondary' ?>">
                        <span class="font-bold"><?= $mine ? 'شما' : 'پشتیبانی بهنام' ?></span>
                        <span class="nums"><?= jdate((string) $m['created_at'], 'H:i Y/m/d') ?></span>
                    </div>
                    <p class="text-[12.5px] leading-7 text-[#444]"><?= nl2br(e($m['body'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ((string) $ticket['status'] === 'closed'): ?>
        <div class="mt-5 rounded-2xl bg-surface py-4 text-center text-[12.5px] text-[#888]">این تیکت بسته شده است.</div>
    <?php else: ?>
        <form method="post" action="<?= e(url('/account/tickets/' . $ticket['id'] . '/reply')) ?>" class="mt-5 rounded-2xl border border-line2 bg-white p-4">
            <?= csrf_field() ?>
            <textarea name="body" rows="3" placeholder="پاسخ خود را بنویسید…" class="w-full rounded-xl2 border border-line bg-surface px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary" required></textarea>
            <div class="mt-3 text-left"><button class="btn-primary px-6 py-2.5 text-[13px]">ارسال پاسخ</button></div>
        </form>
    <?php endif; ?>
</div>
