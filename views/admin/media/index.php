<?php
/** @var list<array<string,mixed>> $items @var list<string> $folders @var string $folder @var array{images:int,videos:int,bytes:int} $stats */
$fmt = static function (int $b): string {
    if ($b >= 1048576) { return number_format($b / 1048576, 1) . ' MB'; }
    if ($b >= 1024) { return number_format($b / 1024, 0) . ' KB'; }
    return $b . ' B';
};
?>
<div class="mb-4 grid gap-3 sm:grid-cols-3">
    <div class="rounded-2xl border border-line2 bg-white p-4 text-center"><div class="text-[22px] font-extrabold text-secondary nums"><?= fa($stats['images']) ?></div><div class="text-[11.5px] text-[#888]">تصویر</div></div>
    <div class="rounded-2xl border border-line2 bg-white p-4 text-center"><div class="text-[22px] font-extrabold text-mauve nums"><?= fa($stats['videos']) ?></div><div class="text-[11.5px] text-[#888]">ویدیو</div></div>
    <div class="rounded-2xl border border-line2 bg-white p-4 text-center"><div class="text-[22px] font-extrabold text-[#444] nums"><?= fa($fmt($stats['bytes'])) ?></div><div class="text-[11.5px] text-[#888]">حجم کل</div></div>
</div>

<!-- upload -->
<form method="post" action="<?= e(url('/admin/media/upload')) ?>" enctype="multipart/form-data" class="mb-4 flex flex-wrap items-end gap-3 rounded-2xl border border-line2 bg-white p-4">
    <?= csrf_field() ?>
    <div class="flex-1">
        <label class="mb-1.5 block text-[12px] font-semibold text-[#666]">بارگذاری فایل (تصویر یا ویدیو)</label>
        <input type="file" name="files[]" accept="image/*,video/mp4,video/webm" multiple class="w-full text-[12px] text-[#666] file:mr-2 file:rounded-lg file:border-0 file:bg-pink file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-secondary">
    </div>
    <div>
        <label class="mb-1.5 block text-[12px] font-semibold text-[#666]">پوشه</label>
        <input name="folder" value="<?= e($folder ?: 'library') ?>" dir="ltr" class="w-40 rounded-xl2 border border-line bg-white px-3 py-2 text-[13px] text-left outline-none focus:border-secondary">
    </div>
    <button class="btn-primary px-6 py-2.5 text-[13px]">بارگذاری</button>
    <p class="w-full text-[11px] text-[#aaa]">تصویر تا ۳ مگابایت (JPG/PNG/WEBP/GIF) · ویدیو تا ۳۰ مگابایت (MP4/WEBM).</p>
</form>

<!-- folder filter -->
<div class="mb-4 flex flex-wrap items-center gap-1.5">
    <a href="<?= e(url('/admin/media')) ?>" class="rounded-xl2 px-3.5 py-1.5 text-[12.5px] font-semibold <?= $folder === '' ? 'bg-secondary text-white' : 'bg-surface text-[#666]' ?>">همه</a>
    <?php foreach ($folders as $f): ?>
        <a href="<?= e(url('/admin/media?folder=' . urlencode($f))) ?>" dir="ltr" class="rounded-xl2 px-3.5 py-1.5 text-[12.5px] font-semibold <?= $folder === $f ? 'bg-secondary text-white' : 'bg-surface text-[#666]' ?>"><?= e($f) ?></a>
    <?php endforeach; ?>
</div>

<?php if ($items === []): ?>
    <div class="rounded-2xl border border-line2 bg-surface py-16 text-center text-[13px] text-[#999]">فایلی یافت نشد.</div>
<?php else: ?>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
        <?php foreach ($items as $m): ?>
            <div class="group overflow-hidden rounded-2xl border border-line2 bg-white">
                <div class="relative aspect-square bg-surface">
                    <?php if ($m['is_video']): ?>
                        <video src="<?= e(asset((string) $m['path'])) ?>" class="h-full w-full object-cover" muted preload="metadata"></video>
                        <span class="absolute right-2 top-2 rounded-lg bg-black/60 px-1.5 py-0.5 text-[9px] font-bold text-white">ویدیو</span>
                    <?php else: ?>
                        <img src="<?= e(asset((string) $m['path'])) ?>" alt="<?= e($m['name']) ?>" loading="lazy" class="h-full w-full object-cover">
                    <?php endif; ?>
                </div>
                <div class="p-2">
                    <div class="truncate text-[10.5px] text-[#888] nums" dir="ltr" title="<?= e($m['path']) ?>"><?= e($m['name']) ?></div>
                    <div class="mt-0.5 flex items-center justify-between text-[9.5px] text-[#aaa]">
                        <span class="uppercase"><?= e($m['ext']) ?></span>
                        <span class="nums"><?= fa($fmt((int) $m['size'])) ?></span>
                    </div>
                    <div class="mt-2 flex items-center justify-between gap-1">
                        <button type="button" class="js-copy-path flex-1 rounded-lg bg-surface py-1 text-[10.5px] font-semibold text-secondary" data-path="<?= e($m['path']) ?>">کپی مسیر</button>
                        <form method="post" action="<?= e(url('/admin/media/delete')) ?>" class="js-confirm" data-confirm="حذف این فایل؟ اگر جایی استفاده شده باشد، تصویر آن خراب می‌شود.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="path" value="<?= e($m['path']) ?>">
                            <input type="hidden" name="return_folder" value="<?= e($folder) ?>">
                            <button class="rounded-lg bg-[#FDECEC] px-2 py-1 text-[10.5px] font-semibold text-danger">حذف</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
