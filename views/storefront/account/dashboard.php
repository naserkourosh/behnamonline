<?php
/** @var array<string,mixed> $user */
/** @var array<string,mixed> $stats */
/** @var list<array<string,mixed>> $orders */

$this->meta(['title' => 'حساب کاربری | بهنام', 'robots' => 'noindex']);
$name    = trim(((string) $user['first_name']) . ' ' . ((string) $user['last_name']));
$name    = $name !== '' ? $name : 'کاربر بهنام';
$initial = mb_substr($name, 0, 1);
?>
<div class="container-page py-6 md:py-8">
    <!-- profile header -->
    <div class="rounded-3xl bg-gradient-to-l from-secondary to-secondary-light p-5 text-white md:p-7">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-white/30 bg-white/15 text-[22px] font-bold"><?= e($initial) ?></div>
            <div class="flex-1">
                <div class="text-[17px] font-bold"><?= e($name) ?></div>
                <div class="mt-1 text-[12px] opacity-85 nums" dir="ltr"><?= e($user['mobile']) ?></div>
            </div>
            <a href="<?= e(url('/account/profile')) ?>" class="rounded-full bg-white/15 px-3.5 py-1.5 text-[11px]">ویرایش</a>
        </div>
    </div>

    <!-- stat cards -->
    <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
        <?php
        $cards = [
            ['📦', $stats['orders'], 'سفارش', 'bg-pink'],
            ['♡', $stats['wishlist'], 'علاقه‌مندی', 'bg-[#FFF0F3]'],
            ['💳', money((int) $stats['wallet']), 'کیف پول', 'bg-[#E7F7F0]'],
            ['★', $stats['points'], 'امتیاز', 'bg-[#FFF6E6]'],
        ];
        foreach ($cards as [$icon, $value, $label, $tint]): ?>
            <div class="flex items-center gap-3 rounded-2xl border border-line2 bg-white p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl2 <?= $tint ?>"><?= $icon ?></div>
                <div>
                    <div class="text-[15px] font-extrabold text-[#333] nums"><?= is_numeric($value) ? fa((int) $value) : $value ?></div>
                    <div class="text-[10.5px] text-[#999]"><?= e($label) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-6 md:flex md:items-start md:gap-8">
        <div class="md:flex-1">
            <div class="mb-3.5 flex items-center justify-between">
                <h2 class="text-[15px] font-bold text-secondary">سفارش‌های اخیر</h2>
                <a href="<?= e(url('/account/orders')) ?>" class="text-[11.5px] text-mauve">مشاهده همه ›</a>
            </div>
            <?php if ($orders === []): ?>
                <div class="rounded-2xl border border-line2 bg-surface py-12 text-center text-[13px] text-[#999]">هنوز سفارشی ثبت نکرده‌اید.</div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($orders as $o): $st = order_status((string) $o['status']); ?>
                        <a href="<?= e(url('/account/orders/' . $o['id'])) ?>" class="block rounded-2xl border border-line2 bg-white p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-[12px] text-[#999]">سفارش <span class="font-bold text-[#333] nums"><?= e($o['order_number']) ?></span></div>
                                <span class="badge <?= $st['bg'] ?> <?= $st['text'] ?>"><?= e($st['label']) ?></span>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <div class="text-[10px] text-[#999]"><?= jdate((string) $o['created_at']) ?> · <?= fa((int) $o['item_count']) ?> قلم</div>
                                <div class="text-[14px] font-extrabold text-secondary nums"><?= money((int) $o['total']) ?> <span class="text-[9px] text-[#999]">ت</span></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="mt-6 md:mt-0 md:w-72 md:flex-none">
            <?php $this->partial('account-nav'); ?>
        </aside>
    </div>
</div>
