<?php
/** @var array<string,mixed> $order */
/** @var list<array<string,mixed>> $items */
/** @var array<string,mixed> $user */
$brand = (string) setting('brand_name', 'بهنام');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاکتور <?= e($order['order_number']) ?> | <?= e($brand) ?></title>
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <style>
        @media print { .no-print { display: none !important; } body { background: #fff; } }
        body { background: #f4efe9; }
    </style>
</head>
<body class="font-sans text-ink">
<div class="mx-auto my-6 max-w-2xl rounded-2xl bg-white p-8 shadow-soft">
    <div class="flex items-start justify-between border-b border-line pb-5">
        <div>
            <div class="text-[22px] font-extrabold text-secondary"><?= e($brand) ?><span class="text-gold">.</span></div>
            <div class="pr-[0.3em] text-[9px] tracking-[0.4em] text-gold"><?= e((string) config('app.wordmark', 'BEHNAM')) ?></div>
        </div>
        <div class="text-left">
            <div class="text-[16px] font-bold text-[#333]">فاکتور فروش</div>
            <div class="mt-1 text-[12px] text-[#888]">شماره: <span class="font-bold nums"><?= e($order['order_number']) ?></span></div>
            <div class="text-[12px] text-[#888]">تاریخ: <span class="nums"><?= jdate((string) $order['created_at']) ?></span></div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 py-5 text-[12px] leading-7 text-[#555]">
        <div>
            <div class="mb-1 font-bold text-secondary">مشخصات خریدار</div>
            <div><?= e($order['receiver_name']) ?></div>
            <div class="nums" dir="ltr"><?= e($order['mobile']) ?></div>
        </div>
        <div>
            <div class="mb-1 font-bold text-secondary">آدرس تحویل</div>
            <div><?= e($order['province']) ?>، <?= e($order['city']) ?></div>
            <div><?= e($order['address']) ?></div>
            <?php if (!empty($order['postal_code'])): ?><div class="text-[#999]">کد پستی: <span class="nums"><?= e($order['postal_code']) ?></span></div><?php endif; ?>
        </div>
    </div>

    <table class="w-full border-collapse text-[12px]">
        <thead>
            <tr class="bg-surface text-[#777]">
                <th class="border border-line p-2.5 text-right font-semibold">کالا</th>
                <th class="border border-line p-2.5 font-semibold">تعداد</th>
                <th class="border border-line p-2.5 font-semibold">قیمت واحد</th>
                <th class="border border-line p-2.5 font-semibold">جمع</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td class="border border-line p-2.5 text-right">
                        <?= e($it['name']) ?><?= !empty($it['variant_label']) ? ' <span class="text-[#999]">(' . e($it['variant_label']) . ')</span>' : '' ?>
                    </td>
                    <td class="border border-line p-2.5 text-center nums"><?= fa((int) $it['qty']) ?></td>
                    <td class="border border-line p-2.5 text-center nums"><?= money((int) $it['unit_price']) ?></td>
                    <td class="border border-line p-2.5 text-center nums"><?= money((int) $it['line_total']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-5 ms-auto w-full max-w-xs text-[12.5px]">
        <div class="flex justify-between py-1 text-[#666]"><span>جمع کالاها</span><span class="nums"><?= money((int) $order['subtotal']) ?> ت</span></div>
        <?php if ((int) $order['discount'] > 0): ?>
            <div class="flex justify-between py-1 text-success"><span>تخفیف</span><span class="nums">− <?= money((int) $order['discount']) ?> ت</span></div>
        <?php endif; ?>
        <div class="flex justify-between py-1 text-[#666]"><span>ارسال (<?= e($order['shipping_method']) ?>)</span><span class="nums"><?= (int) $order['shipping_cost'] === 0 ? 'رایگان' : money((int) $order['shipping_cost']) . ' ت' ?></span></div>
        <div class="mt-2 flex justify-between border-t border-line pt-2 text-[15px] font-extrabold text-secondary"><span>مبلغ کل</span><span class="nums"><?= money((int) $order['total']) ?> تومان</span></div>
    </div>

    <div class="mt-6 border-t border-line pt-4 text-center text-[11px] text-[#aaa]">
        وضعیت پرداخت: <?= $order['payment_status'] === 'paid' ? 'پرداخت شده' : 'در انتظار پرداخت' ?>
        <?php if (!empty($order['tracking_code'])): ?> · کد رهگیری: <span class="nums" dir="ltr"><?= e($order['tracking_code']) ?></span><?php endif; ?>
        <div class="mt-1">از خرید شما سپاسگزاریم 🌸</div>
    </div>

    <div class="no-print mt-6 flex justify-center gap-3">
        <button type="button" onclick="window.print()" class="btn-primary px-8 py-3 text-[13px]">چاپ فاکتور</button>
        <a href="<?= e(url('/account/orders/' . $order['id'])) ?>" class="btn-outline px-6 py-3 text-[13px]">بازگشت</a>
    </div>
</div>
</body>
</html>
