<?php
/** @var int $page @var int $pages @var string $baseUrl (already contains ? or & ready for page=) */
if (($pages ?? 1) <= 1) {
    return;
}
$mk = static fn (int $p): string => $baseUrl . 'page=' . $p;
?>
<div class="mt-5 flex items-center justify-center gap-1.5">
    <?php if ($page > 1): ?>
        <a href="<?= e($mk($page - 1)) ?>" class="rounded-lg border border-line px-3 py-1.5 text-[12px] text-secondary">قبلی</a>
    <?php endif; ?>
    <?php for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++): ?>
        <a href="<?= e($mk($p)) ?>" class="rounded-lg border px-3 py-1.5 text-[12px] nums <?= $p === $page ? 'border-secondary bg-secondary text-white' : 'border-line text-secondary' ?>"><?= fa($p) ?></a>
    <?php endfor; ?>
    <?php if ($page < $pages): ?>
        <a href="<?= e($mk($page + 1)) ?>" class="rounded-lg border border-line px-3 py-1.5 text-[12px] text-secondary">بعدی</a>
    <?php endif; ?>
</div>
