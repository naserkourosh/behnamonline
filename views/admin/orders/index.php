<?php
/** @var list<array<string,mixed>> $items @var array<string,mixed> $filters @var int $total @var int $page @var int $pages */
$statuses = ['' => 'همه وضعیت‌ها', 'pending' => 'در انتظار', 'processing' => 'در حال پردازش', 'shipped' => 'ارسال شده', 'delivered' => 'تحویل شده', 'canceled' => 'لغو شده'];
$payments = ['' => 'همه پرداخت‌ها', 'paid' => 'پرداخت شده', 'unpaid' => 'پرداخت‌نشده', 'failed' => 'ناموفق'];
$sel = 'rounded-xl2 border border-line bg-white px-3 py-2 text-[12.5px] outline-none focus:border-secondary';
?>
<form method="get" action="<?= e(url('/admin/orders')) ?>" class="mb-4 flex flex-wrap items-center gap-2">
    <input name="q" value="<?= e($filters['search']) ?>" placeholder="شماره سفارش، موبایل یا نام…" class="w-56 rounded-xl2 border border-line bg-white px-4 py-2 text-[13px] outline-none focus:border-secondary">
    <select name="status" class="<?= $sel ?>"><?php foreach ($statuses as $val => $lbl): ?><option value="<?= $val ?>" <?= $filters['status'] === $val ? 'selected' : '' ?>><?= e($lbl) ?></option><?php endforeach; ?></select>
    <select name="payment_status" class="<?= $sel ?>"><?php foreach ($payments as $val => $lbl): ?><option value="<?= $val ?>" <?= $filters['payment_status'] === $val ? 'selected' : '' ?>><?= e($lbl) ?></option><?php endforeach; ?></select>
    <button class="rounded-xl2 bg-surface px-4 py-2 text-[13px] font-semibold text-secondary">فیلتر</button>
    <span class="text-[12px] text-[#999] nums"><?= fa($total) ?> سفارش</span>
</form>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">سفارش</th>
                    <th class="p-3 font-semibold">مشتری</th>
                    <th class="p-3 font-semibold">اقلام</th>
                    <th class="p-3 font-semibold">مبلغ</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">تاریخ</th>
                    <th class="p-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $o): ?>
                    <tr class="border-b border-line2 last:border-0 hover:bg-surface/50">
                        <td class="p-3"><a href="<?= e(url('/admin/orders/' . $o['id'])) ?>" class="font-bold text-secondary nums"><?= e($o['order_number']) ?></a><?php if (!empty($o['note'])): ?> <span title="این سفارش توضیحات مشتری دارد" class="cursor-help text-[12px]">💬</span><?php endif; ?></td>
                        <td class="p-3 text-center text-[#444]"><?= e($o['receiver_name'] ?: '—') ?><div class="text-[10px] text-[#aaa] nums" dir="ltr"><?= e($o['mobile']) ?></div></td>
                        <td class="p-3 text-center nums"><?= fa((int) $o['item_count']) ?></td>
                        <td class="p-3 text-center font-bold text-[#333] nums"><?= money((int) $o['total']) ?></td>
                        <td class="p-3 text-center"><?php $this->partial('admin/order-badge', ['status' => $o['status'], 'payment' => $o['payment_status']]); ?></td>
                        <td class="p-3 text-center text-[#999] nums"><?= jdate((string) $o['created_at']) ?></td>
                        <td class="p-3 text-center"><a href="<?= e(url('/admin/orders/' . $o['id'])) ?>" class="text-secondary hover:underline">مدیریت</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="7" class="p-8 text-center text-[#999]">سفارشی یافت نشد.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->partial('admin/pagination', ['page' => $page, 'pages' => $pages, 'baseUrl' => url('/admin/orders') . '?q=' . urlencode($filters['search']) . '&status=' . urlencode($filters['status']) . '&payment_status=' . urlencode($filters['payment_status']) . '&']); ?>
