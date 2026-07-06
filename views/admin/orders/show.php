<?php
/** @var array<string,mixed> $order @var list<array<string,mixed>> $items @var array<string,mixed>|null $customer */
$sel = 'w-full rounded-xl2 border border-line bg-white px-3 py-2.5 text-[13px] outline-none focus:border-secondary';
$statuses = ['pending' => 'در انتظار', 'processing' => 'در حال پردازش', 'shipped' => 'ارسال شده', 'delivered' => 'تحویل شده', 'canceled' => 'لغو شده'];
$payments = ['unpaid' => 'پرداخت‌نشده', 'paid' => 'پرداخت شده', 'failed' => 'ناموفق'];
?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= e(url('/admin/orders')) ?>" class="text-[12px] text-mauve">‹ بازگشت به سفارش‌ها</a>
    <div class="flex items-center gap-2">
        <span class="text-[15px] font-bold text-secondary nums"><?= e($order['order_number']) ?></span>
        <?php $this->partial('admin/order-badge', ['status' => $order['status'], 'payment' => $order['payment_status']]); ?>
    </div>
</div>

<div class="grid gap-5 lg:grid-cols-3">
    <!-- Items + address -->
    <div class="space-y-5 lg:col-span-2">
        <div class="overflow-hidden rounded-2xl border border-line2 bg-white">
            <div class="border-b border-line px-5 py-3.5 text-[14px] font-bold text-[#333]">اقلام سفارش</div>
            <table class="w-full text-[12.5px]">
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr class="border-b border-line2 last:border-0">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <img src="<?= e(asset((string) ($it['image'] ?: 'assets/images/placeholder-product.svg'))) ?>" alt="" class="h-11 w-11 flex-none rounded-lg object-cover">
                                    <div><div class="font-semibold text-[#333]"><?= e($it['name']) ?></div><?php if (!empty($it['variant_label'])): ?><div class="text-[10.5px] text-[#999]"><?= e($it['variant_label']) ?></div><?php endif; ?></div>
                                </div>
                            </td>
                            <td class="p-3 text-center text-[#666] nums"><?= fa((int) $it['qty']) ?> ×</td>
                            <td class="p-3 text-center text-[#666] nums"><?= money((int) $it['unit_price']) ?></td>
                            <td class="p-3 text-left font-bold text-secondary nums"><?= money((int) $it['line_total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="space-y-1.5 border-t border-line bg-surface px-5 py-4 text-[12.5px]">
                <div class="flex justify-between text-[#666]"><span>جمع کالاها</span><span class="nums"><?= money((int) $order['subtotal']) ?> ت</span></div>
                <?php if ((int) $order['discount'] > 0): ?><div class="flex justify-between text-success"><span>تخفیف</span><span class="nums">− <?= money((int) $order['discount']) ?> ت</span></div><?php endif; ?>
                <div class="flex justify-between text-[#666]"><span>ارسال (<?= e($order['shipping_method']) ?>)</span><span class="nums"><?= (int) $order['shipping_cost'] === 0 ? 'رایگان' : money((int) $order['shipping_cost']) . ' ت' ?></span></div>
                <div class="flex justify-between border-t border-line pt-2 text-[14px] font-extrabold text-secondary"><span>مبلغ کل</span><span class="nums"><?= money((int) $order['total']) ?> تومان</span></div>
            </div>
        </div>

        <div class="rounded-2xl border border-line2 bg-white p-5 text-[12.5px] leading-7">
            <div class="mb-2 text-[14px] font-bold text-[#333]">آدرس تحویل</div>
            <div class="text-[#555]"><?= e($order['receiver_name']) ?> — <span class="nums" dir="ltr"><?= e($order['mobile']) ?></span></div>
            <div class="text-[#555]"><?= e($order['province']) ?>، <?= e($order['city']) ?> — <?= e($order['address']) ?></div>
            <?php if (!empty($order['postal_code'])): ?><div class="text-[#999]">کد پستی: <span class="nums"><?= e($order['postal_code']) ?></span></div><?php endif; ?>
            <div class="mt-2 text-[#999]">روش پرداخت: <?= e($order['payment_method']) ?></div>
        </div>

        <?php if (!empty($order['note'])): ?>
        <div class="rounded-2xl border border-[#F2E3C4] bg-[#FFF9EC] p-5 text-[12.5px] leading-7">
            <div class="mb-1.5 flex items-center gap-2 text-[14px] font-bold text-[#8a6a2a]">💬 توضیحات مشتری</div>
            <p class="whitespace-pre-line text-[#6a5a3a]"><?= e($order['note']) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Manage -->
    <div class="space-y-5">
        <form method="post" action="<?= e(url('/admin/orders/' . $order['id'] . '/update')) ?>" class="rounded-2xl border border-line2 bg-white p-5">
            <?= csrf_field() ?>
            <h3 class="mb-3 text-[14px] font-bold text-[#333]">مدیریت سفارش</h3>
            <div class="mb-3"><label class="mb-1.5 block text-[12px] font-semibold text-[#666]">وضعیت سفارش</label>
                <select name="status" class="<?= $sel ?>"><?php foreach ($statuses as $val => $lbl): ?><option value="<?= $val ?>" <?= $order['status'] === $val ? 'selected' : '' ?>><?= e($lbl) ?></option><?php endforeach; ?></select>
            </div>
            <div class="mb-3"><label class="mb-1.5 block text-[12px] font-semibold text-[#666]">وضعیت پرداخت</label>
                <select name="payment_status" class="<?= $sel ?>"><?php foreach ($payments as $val => $lbl): ?><option value="<?= $val ?>" <?= $order['payment_status'] === $val ? 'selected' : '' ?>><?= e($lbl) ?></option><?php endforeach; ?></select>
                <?php if ($order['payment_method'] === 'card' && $order['payment_status'] !== 'paid'): ?><p class="mt-1.5 text-[11px] text-warning">پرداخت کارت‌به‌کارت — پس از بررسی رسید، «پرداخت شده» را انتخاب کنید.</p><?php endif; ?>
            </div>
            <div class="mb-4"><label class="mb-1.5 block text-[12px] font-semibold text-[#666]">کد رهگیری پستی</label>
                <input name="tracking_code" value="<?= e($order['tracking_code'] ?? '') ?>" dir="ltr" class="<?= $sel ?> text-left" placeholder="خودکار هنگام تایید پرداخت">
            </div>
            <button class="btn-primary w-full py-3 text-[13px]">ذخیره و اطلاع‌رسانی</button>
        </form>

        <?php if ($customer !== null): ?>
        <div class="rounded-2xl border border-line2 bg-white p-5 text-[12.5px]">
            <h3 class="mb-2 text-[14px] font-bold text-[#333]">مشتری</h3>
            <a href="<?= e(url('/admin/customers/' . $customer['id'])) ?>" class="font-semibold text-secondary"><?= e(trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''))) ?: 'کاربر' ?></a>
            <div class="text-[#999] nums" dir="ltr"><?= e($customer['mobile']) ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>
