<?php
// Promotional popup (admin-managed). Rendered hidden; app.js reveals it per
// the frequency / delay rules encoded in the data-* attributes.
$path  = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
$popup = (new \App\Repositories\PopupRepository())->activeForPath($path);
if ($popup === null) {
    return;
}
$corner = (string) $popup['position'] === 'corner';
?>
<div
    id="js-popup"
    class="pointer-events-none fixed inset-0 z-[95] hidden <?= $corner ? 'items-end justify-start p-4 md:p-6' : 'items-center justify-center p-4' ?> flex"
    data-id="<?= (int) $popup['id'] ?>"
    data-delay="<?= (int) $popup['delay_seconds'] ?>"
    data-frequency="<?= e((string) $popup['frequency']) ?>"
>
    <?php if (!$corner): ?>
        <div class="js-popup-backdrop absolute inset-0 bg-black/50"></div>
    <?php endif; ?>
    <div class="js-popup-card pointer-events-auto relative <?= $corner ? 'w-[300px]' : 'w-full max-w-md' ?> overflow-hidden rounded-2xl bg-white shadow-balloon">
        <button type="button" class="js-popup-close absolute left-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-black/30 text-white" aria-label="بستن">&times;</button>
        <?php if (!empty($popup['image'])): ?>
            <img src="<?= e(asset((string) $popup['image'])) ?>" alt="<?= e($popup['title']) ?>" class="max-h-56 w-full object-cover">
        <?php endif; ?>
        <div class="p-5 text-center">
            <h3 class="text-[17px] font-extrabold text-secondary"><?= e($popup['title']) ?></h3>
            <?php if (!empty($popup['body'])): ?>
                <div class="mt-2 text-[12.5px] leading-7 text-[#666]"><?= html_clean((string) $popup['body']) ?></div>
            <?php endif; ?>
            <?php if (!empty($popup['cta_label'])): ?>
                <a href="<?= e($popup['cta_url'] ?: '#') ?>" class="btn-primary mt-4 inline-block px-7 py-2.5 text-[13px]"><?= e($popup['cta_label']) ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>
