<?php
/** @var array<string,mixed> $user @var list<array<string,mixed>> $orders @var list<array<string,mixed>> $addresses */
$name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
?>
<a href="<?= e(url('/admin/customers')) ?>" class="mb-4 inline-block text-[12px] text-mauve">‹ بازگشت به مشتریان</a>
<div class="grid gap-5 lg:grid-cols-3">
    <div class="space-y-5">
        <div class="rounded-2xl border border-line2 bg-white p-5">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-secondary to-secondary-light text-[18px] font-bold text-white"><?= e(mb_substr($name ?: 'ک', 0, 1)) ?></div>
                <div>
                    <div class="text-[15px] font-bold text-[#333]"><?= e($name ?: 'کاربر') ?></div>
                    <div class="text-[12px] text-[#999] nums" dir="ltr"><?= e($user['mobile']) ?></div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-center">
                <div class="rounded-xl2 bg-surface p-3"><div class="text-[16px] font-extrabold text-secondary nums"><?= fa(count($orders)) ?></div><div class="text-[10.5px] text-[#999]">سفارش</div></div>
                <div class="rounded-xl2 bg-surface p-3"><div class="text-[14px] font-extrabold text-secondary nums"><?= money((int) $user['wallet_balance']) ?></div><div class="text-[10.5px] text-[#999]">کیف پول</div></div>
            </div>
        </div>
        <div class="rounded-2xl border border-line2 bg-white p-5">
            <h3 class="mb-3 text-[14px] font-bold text-[#333]">آدرس‌ها</h3>
            <?php if ($addresses === []): ?><p class="text-[12px] text-[#999]">آدرسی ثبت نشده.</p><?php else: ?>
                <div class="space-y-2.5">
                    <?php foreach ($addresses as $a): ?>
                        <div class="rounded-xl2 border border-line p-3 text-[12px] leading-6 text-[#555]">
                            <div class="font-semibold text-[#333]"><?= e($a['receiver_name']) ?> · <span class="nums" dir="ltr"><?= e($a['mobile']) ?></span></div>
                            <?= e($a['province']) ?>، <?= e($a['city']) ?> — <?= e($a['address']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl border border-line2 bg-white">
            <div class="border-b border-line px-5 py-3.5 text-[14px] font-bold text-[#333]">سفارش‌های مشتری</div>
            <table class="w-full text-[12.5px]">
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr class="border-b border-line2 last:border-0">
                            <td class="p-3"><a href="<?= e(url('/admin/orders/' . $o['id'])) ?>" class="font-bold text-secondary nums"><?= e($o['order_number']) ?></a></td>
                            <td class="p-3 text-center font-bold text-[#333] nums"><?= money((int) $o['total']) ?></td>
                            <td class="p-3 text-center"><?php $this->partial('admin/order-badge', ['status' => $o['status'], 'payment' => $o['payment_status']]); ?></td>
                            <td class="p-3 text-center text-[#999] nums"><?= jdate((string) $o['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($orders === []): ?><tr><td class="p-8 text-center text-[#999]">سفارشی ندارد.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
