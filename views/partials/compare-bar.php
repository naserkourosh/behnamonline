<?php
/** Floating comparison bar — visible once at least one product is queued. */
$__cmpCount = (new \App\Services\CompareService())->count();
?>
<div class="js-compare-bar fixed inset-x-0 bottom-[86px] z-[60] flex justify-center px-4 md:bottom-6 <?= $__cmpCount > 0 ? '' : 'hidden' ?>">
    <div class="flex items-center gap-3 rounded-full bg-secondary px-5 py-3 shadow-card">
        <span class="text-[12.5px] font-semibold text-white">⚖️ مقایسه (<span class="js-compare-count nums"><?= fa($__cmpCount) ?></span>)</span>
        <a href="<?= e(url('/compare')) ?>" class="rounded-full bg-white px-4 py-1.5 text-[12px] font-bold text-secondary">مشاهده</a>
        <button type="button" class="js-compare-clear text-[18px] leading-none text-white/85" aria-label="حذف همه مقایسه‌ها">&times;</button>
    </div>
</div>
