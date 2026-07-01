<?php
/** @var list<array<string,mixed>> $items @var string $status @var int $total @var int $page @var int $pages */
$tabs = ['' => 'همه', 'open' => 'باز', 'answered' => 'پاسخ‌داده', 'closed' => 'بسته'];
$badge = ['open' => 'bg-[#EEF2FF] text-[#4F46E5]', 'answered' => 'bg-[#E7F7F0] text-success', 'closed' => 'bg-surface text-[#999]'];
$blab  = ['open' => 'باز', 'answered' => 'پاسخ‌داده', 'closed' => 'بسته'];
$prio  = ['high' => 'bg-[#FDECEC] text-danger', 'normal' => 'bg-surface text-[#888]', 'low' => 'bg-surface text-[#aaa]'];
$plab  = ['high' => 'فوری', 'normal' => 'عادی', 'low' => 'کم'];
?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <span class="text-[13px] font-semibold text-[#555] nums"><?= fa($total) ?> تیکت</span>
    <div class="flex items-center gap-1.5">
        <?php foreach ($tabs as $k => $label): ?>
            <a href="<?= e(url('/admin/tickets?status=' . urlencode($k))) ?>" class="rounded-xl2 px-3.5 py-1.5 text-[12.5px] font-semibold <?= $status === $k ? 'bg-secondary text-white' : 'bg-surface text-[#666]' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[680px] text-[12.5px]">
            <thead><tr class="border-b border-line bg-surface text-[#888]"><th class="p-3 text-right font-semibold">موضوع</th><th class="p-3 font-semibold">مشتری</th><th class="p-3 font-semibold">اولویت</th><th class="p-3 font-semibold">پیام‌ها</th><th class="p-3 font-semibold">وضعیت</th><th class="p-3 font-semibold">آخرین</th></tr></thead>
            <tbody>
                <?php foreach ($items as $t): $name = trim(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? '')); ?>
                    <tr class="cursor-pointer border-b border-line2 last:border-0 hover:bg-surface/50" onclick="location.href='<?= e(url('/admin/tickets/' . $t['id'])) ?>'">
                        <td class="p-3 font-semibold text-[#333]"><?= e($t['subject']) ?></td>
                        <td class="p-3 text-center text-[#666]"><?= e($name ?: 'کاربر') ?><div class="text-[10px] text-[#aaa] nums" dir="ltr"><?= e($t['mobile']) ?></div></td>
                        <td class="p-3 text-center"><span class="rounded-lg px-2 py-0.5 text-[10.5px] font-bold <?= $prio[$t['priority']] ?? '' ?>"><?= e($plab[$t['priority']] ?? '') ?></span></td>
                        <td class="p-3 text-center nums text-[#666]"><?= fa((int) $t['message_count']) ?></td>
                        <td class="p-3 text-center"><span class="rounded-lg px-2 py-0.5 text-[10.5px] font-bold <?= $badge[$t['status']] ?? '' ?>"><?= e($blab[$t['status']] ?? '') ?></span></td>
                        <td class="p-3 text-center nums text-[10.5px] text-[#999]"><?= jdate((string) ($t['last_reply_at'] ?: $t['created_at']), 'H:i Y/m/d') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="6" class="p-8 text-center text-[#999]">تیکتی یافت نشد.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->partial('admin/pagination', ['page' => $page, 'pages' => $pages, 'baseUrl' => url('/admin/tickets') . '?status=' . urlencode($status) . '&']); ?>
