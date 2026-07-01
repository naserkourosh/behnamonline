<?php
/** @var string $status */
/** @var string $payment */
$statusMap = [
    'pending'    => ['در انتظار', 'bg-[#FFF6E6] text-warning'],
    'processing' => ['در حال پردازش', 'bg-pink text-secondary'],
    'shipped'    => ['ارسال شده', 'bg-[#EEF2FF] text-[#4f46e5]'],
    'delivered'  => ['تحویل شده', 'bg-[#E7F7F0] text-success'],
    'canceled'   => ['لغو شده', 'bg-[#FDECEC] text-danger'],
];
$payMap = [
    'paid'   => ['پرداخت شده', 'bg-[#E7F7F0] text-success'],
    'unpaid' => ['پرداخت‌نشده', 'bg-[#FFF6E6] text-warning'],
    'failed' => ['ناموفق', 'bg-[#FDECEC] text-danger'],
];
[$sLabel, $sCls] = $statusMap[$status] ?? [$status, 'bg-surface text-[#666]'];
[$pLabel, $pCls] = $payMap[$payment] ?? [$payment, 'bg-surface text-[#666]'];
?>
<span class="inline-flex items-center rounded-lg px-2 py-1 text-[10.5px] font-bold leading-none <?= $sCls ?>"><?= e($sLabel) ?></span>
<span class="inline-flex items-center rounded-lg px-2 py-1 text-[10.5px] font-bold leading-none <?= $pCls ?>"><?= e($pLabel) ?></span>
