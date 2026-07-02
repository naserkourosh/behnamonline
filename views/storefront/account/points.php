<?php
/** @var array<string,mixed> $user @var list<array<string,mixed>> $transactions @var float $earn_percent @var bool $enabled */
$this->meta(['title' => 'باشگاه مشتریان | بهنام', 'robots' => 'noindex']);
$typeLabel = ['earn' => ['کسب امتیاز', 'text-success'], 'redeem' => ['استفاده', 'text-danger'], 'adjust' => ['اصلاح', 'text-[#666]']];
?>
<div class="container-page py-6 md:py-8">
    <div class="mb-4 flex items-center gap-2">
        <a href="<?= e(url('/account')) ?>" class="text-secondary"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <h1 class="text-[18px] font-bold text-secondary md:text-[22px]">باشگاه مشتریان</h1>
    </div>

    <div class="md:flex md:items-start md:gap-8">
        <div class="md:flex-1">
            <div class="mb-5 overflow-hidden rounded-2xl bg-gradient-to-l from-secondary to-secondary-light p-6 text-white">
                <div class="text-[12px] opacity-85">امتیاز قابل استفاده شما</div>
                <div class="mt-1 text-[32px] font-extrabold nums"><?= fa((int) $user['reward_points']) ?></div>
                <div class="mt-1 text-[11px] opacity-80">هر امتیاز معادل ۱ تومان اعتبار برای خریدهای بعدی است.</div>
            </div>

            <?php if ($enabled && $earn_percent > 0): ?>
                <div class="mb-5 rounded-2xl border border-line2 bg-pink px-4 py-3 text-[12.5px] text-secondary">
                    🎁 با هر خرید، <span class="font-bold nums"><?= fa(rtrim(rtrim(number_format($earn_percent, 2), '0'), '.')) ?>٪</span> از مبلغ سفارش به‌عنوان امتیاز به شما تعلق می‌گیرد.
                </div>
            <?php endif; ?>

            <h2 class="mb-3 text-[14px] font-bold text-[#333]">تاریخچهٔ امتیازها</h2>
            <?php if ($transactions === []): ?>
                <div class="rounded-2xl border border-line2 bg-surface py-14 text-center text-[13px] text-[#666]">هنوز امتیازی ثبت نشده است. اولین خرید خود را انجام دهید!</div>
            <?php else: ?>
                <div class="overflow-hidden rounded-2xl border border-line2 bg-white">
                    <?php foreach ($transactions as $t): [$label, $cls] = $typeLabel[$t['type']] ?? $typeLabel['adjust']; ?>
                        <div class="flex items-center justify-between border-b border-line2 px-4 py-3 last:border-0">
                            <div>
                                <div class="text-[12.5px] font-semibold text-[#444]"><?= e($t['note'] ?: $label) ?></div>
                                <div class="text-[10.5px] text-[#999] nums"><?= jdate((string) $t['created_at'], 'H:i Y/m/d') ?></div>
                            </div>
                            <div class="text-[14px] font-bold nums <?= (int) $t['points'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= (int) $t['points'] >= 0 ? '+' : '' ?><?= fa((int) $t['points']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <aside class="mt-6 hidden md:mt-0 md:block md:w-72 md:flex-none"><?php $this->partial('account-nav'); ?></aside>
    </div>
</div>
