<?php
/** @var list<array<string,mixed>> $items @var string $status @var int $total @var int $page @var int $pages */
$tabs = ['pending' => 'در انتظار', 'approved' => 'تأییدشده', 'rejected' => 'ردشده', '' => 'همه'];
?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <a href="<?= e(url('/admin/blog')) ?>" class="text-[12px] text-mauve">‹ بازگشت به مجله</a>
    <div class="flex items-center gap-1.5">
        <?php foreach ($tabs as $k => $label): ?>
            <a href="<?= e(url('/admin/blog/comments?status=' . urlencode($k))) ?>" class="rounded-xl2 px-3.5 py-1.5 text-[12.5px] font-semibold <?= $status === $k ? 'bg-secondary text-white' : 'bg-surface text-[#666]' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="space-y-3">
    <?php foreach ($items as $c): ?>
        <div class="rounded-2xl border border-line2 bg-white p-4">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <div class="text-[12.5px]">
                    <span class="font-bold text-[#333]"><?= e($c['author_name']) ?></span>
                    <span class="text-[#aaa]">·</span>
                    <a href="<?= e(url('/blog/' . $c['post_slug'])) ?>" target="_blank" rel="noopener" class="text-mauve hover:underline"><?= e($c['post_title']) ?></a>
                    <span class="text-[10.5px] text-[#aaa] nums"><?= jdate((string) $c['created_at'], 'H:i Y/m/d') ?></span>
                </div>
                <?php
                $badge = ['pending' => 'bg-[#FFF7E6] text-warning', 'approved' => 'bg-[#E7F7F0] text-success', 'rejected' => 'bg-[#FDECEC] text-danger'][(string) $c['status']] ?? 'bg-surface';
                $blab  = ['pending' => 'در انتظار', 'approved' => 'تأییدشده', 'rejected' => 'ردشده'][(string) $c['status']] ?? $c['status'];
                ?>
                <span class="rounded-lg px-2 py-0.5 text-[10.5px] font-bold <?= $badge ?>"><?= e($blab) ?></span>
            </div>
            <p class="mb-3 text-[12.5px] leading-7 text-[#555]"><?= nl2br(e($c['body'])) ?></p>
            <form method="post" action="<?= e(url('/admin/blog/comments/' . $c['id'] . '/moderate')) ?>" class="flex items-center gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="return_status" value="<?= e($status) ?>">
                <?php if ((string) $c['status'] !== 'approved'): ?><button name="action" value="approved" class="rounded-lg bg-[#E7F7F0] px-3 py-1.5 text-[11.5px] font-semibold text-success">تأیید</button><?php endif; ?>
                <?php if ((string) $c['status'] !== 'rejected'): ?><button name="action" value="rejected" class="rounded-lg bg-[#FFF7E6] px-3 py-1.5 text-[11.5px] font-semibold text-warning">رد</button><?php endif; ?>
                <button name="action" value="delete" class="rounded-lg bg-[#FDECEC] px-3 py-1.5 text-[11.5px] font-semibold text-danger" onclick="return confirm('حذف این دیدگاه؟')">حذف</button>
            </form>
        </div>
    <?php endforeach; ?>
    <?php if ($items === []): ?><div class="rounded-2xl border border-line2 bg-surface py-14 text-center text-[13px] text-[#999]">دیدگاهی در این وضعیت نیست.</div><?php endif; ?>
</div>
<?php $this->partial('admin/pagination', ['page' => $page, 'pages' => $pages, 'baseUrl' => url('/admin/blog/comments') . '?status=' . urlencode($status) . '&']); ?>
