<?php
/** @var list<array<string,mixed>> $items @var string $status */
$tabs  = ['' => 'همه', 'open' => 'باز', 'closed' => 'بسته'];
$badge = ['open' => 'bg-[#E7F7F0] text-success', 'closed' => 'bg-surface text-[#999]'];
$blab  = ['open' => 'باز', 'closed' => 'بسته'];
?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <span class="text-[13px] font-semibold text-[#555] nums"><?= fa(count($items)) ?> گفتگو</span>
    <div class="flex items-center gap-1.5">
        <?php foreach ($tabs as $k => $label): ?>
            <a href="<?= e(url('/admin/chat?status=' . urlencode($k))) ?>" class="rounded-xl2 px-3.5 py-1.5 text-[12.5px] font-semibold <?= $status === $k ? 'bg-secondary text-white' : 'bg-surface text-[#666]' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-[12.5px]">
            <thead><tr class="border-b border-line bg-surface text-[#888]"><th class="p-3 text-right font-semibold">مشتری</th><th class="p-3 text-right font-semibold">آخرین پیام</th><th class="p-3 font-semibold">خوانده‌نشده</th><th class="p-3 font-semibold">وضعیت</th><th class="p-3 font-semibold">زمان</th></tr></thead>
            <tbody>
                <?php foreach ($items as $c):
                    $name = trim(((string) ($c['first_name'] ?? '')) . ' ' . ((string) ($c['last_name'] ?? '')));
                    $name = $name !== '' ? $name : ((string) ($c['guest_name'] ?? '') ?: 'میهمان');
                    $unread = (int) $c['unread']; ?>
                    <tr class="cursor-pointer border-b border-line2 last:border-0 hover:bg-surface/50 <?= $unread > 0 ? 'bg-pink/40' : '' ?>" onclick="location.href='<?= e(url('/admin/chat/' . $c['id'])) ?>'">
                        <td class="p-3 font-semibold text-[#333]">
                            <?= e($name) ?>
                            <?php if (!empty($c['mobile'])): ?><div class="text-[10px] text-[#aaa] nums" dir="ltr"><?= e($c['mobile']) ?></div><?php endif; ?>
                        </td>
                        <td class="max-w-[280px] truncate p-3 text-[#666]"><?= e(mb_substr((string) ($c['last_body'] ?? ''), 0, 80)) ?></td>
                        <td class="p-3 text-center">
                            <?php if ($unread > 0): ?><span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-danger px-1.5 text-[10.5px] font-bold text-white nums"><?= fa($unread) ?></span><?php else: ?><span class="text-[#ccc]">—</span><?php endif; ?>
                        </td>
                        <td class="p-3 text-center"><span class="rounded-lg px-2 py-0.5 text-[10.5px] font-bold <?= $badge[$c['status']] ?? '' ?>"><?= e($blab[$c['status']] ?? '') ?></span></td>
                        <td class="p-3 text-center nums text-[10.5px] text-[#999]"><?= jdate((string) ($c['last_message_at'] ?: $c['created_at']), 'H:i Y/m/d') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="5" class="p-8 text-center text-[#999]">گفتگویی یافت نشد.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>setTimeout(function () { window.location.reload(); }, 20000);</script>
