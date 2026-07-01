<?php
/** @var array<string,list<array<string,mixed>>> $groups */
$brand = (string) setting('brand_name', 'بهنام');
$this->meta([
    'title'       => 'سوالات متداول | ' . $brand,
    'description' => 'پاسخ پرسش‌های پرتکرار دربارهٔ سفارش، ارسال، پرداخت و مرجوعی کالا در فروشگاه ' . $brand . '.',
]);
// FAQPage structured data
$entities = [];
foreach ($groups as $rows) {
    foreach ($rows as $r) {
        $entities[] = [
            '@type'          => 'Question',
            'name'           => $r['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $r['answer']],
        ];
    }
}
if ($entities !== []) {
    $this->push('json_ld', '<script type="application/ld+json">' . json_encode([
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $entities,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>');
}
?>
<div class="container-page max-w-3xl py-6 md:py-10">
    <div class="mb-6 text-center">
        <h1 class="text-[22px] font-extrabold text-secondary md:text-[28px]">سوالات متداول</h1>
        <p class="mt-2 text-[12.5px] text-[#888]">هر آنچه باید دربارهٔ خرید از <?= e($brand) ?> بدانید.</p>
    </div>

    <?php if ($groups === []): ?>
        <div class="rounded-2xl border border-line2 bg-surface py-16 text-center text-[14px] text-[#666]">هنوز پرسشی ثبت نشده است.</div>
    <?php else: ?>
        <?php foreach ($groups as $category => $rows): ?>
            <section class="mb-7">
                <h2 class="mb-3 text-[15px] font-bold text-[#333]"><?= e($category) ?></h2>
                <div class="space-y-2.5">
                    <?php foreach ($rows as $r): ?>
                        <details class="group rounded-2xl border border-line2 bg-white p-4 [&_summary::-webkit-details-marker]:hidden">
                            <summary class="flex cursor-pointer items-center justify-between gap-3 text-[13.5px] font-semibold text-[#333]">
                                <span><?= e($r['question']) ?></span>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="flex-none text-secondary transition group-open:rotate-180"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </summary>
                            <p class="mt-3 border-t border-line2 pt-3 text-[12.5px] leading-7 text-[#666]"><?= nl2br(e($r['answer'])) ?></p>
                        </details>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="mt-8 rounded-2xl bg-pink p-6 text-center">
        <p class="text-[13px] font-semibold text-secondary">پاسخ سوال‌تان را پیدا نکردید؟</p>
        <a href="<?= e(url('/account/tickets')) ?>" class="btn-primary mt-4 inline-block px-7 py-3 text-[13px]">ارسال تیکت پشتیبانی</a>
    </div>
</div>
