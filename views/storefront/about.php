<?php
$brand = (string) setting('brand_name', 'بهنام');
$about = trim((string) setting('about_text', ''));
$this->meta([
    'title'       => 'درباره ما | ' . $brand,
    'description' => 'آشنایی با فروشگاه ' . $brand . ' — فروشگاه محصولات آرایشی، بهداشتی و شویندهٔ اصل.',
]);
$values = [
    ['✔️', 'ضمانت اصالت کالا', 'همهٔ محصولات اورجینال و دارای کد رهگیری هستند.'],
    ['🚚', 'ارسال سریع', 'ارسال به سراسر کشور؛ تحویل سریع در گرگان.'],
    ['💬', 'پشتیبانی واقعی', 'پاسخگویی از طریق گفتگوی آنلاین و تلفن.'],
    ['💰', 'قیمت منصفانه', 'قیمت‌گذاری شفاف، تخفیف‌های واقعی.'],
];
?>
<div class="container-page py-8 md:py-12">
    <nav class="mb-5 text-[11px] text-mauve"><a href="<?= e(url('/')) ?>" class="hover:text-secondary">خانه</a> <span class="mx-1">/</span> <span class="text-[#777]">درباره ما</span></nav>

    <div class="rounded-4xl bg-gradient-to-br from-secondary to-secondary-light p-7 text-center text-white md:p-12">
        <h1 class="text-[20px] font-extrabold md:text-[28px]">دربارهٔ <?= e($brand) ?></h1>
        <p class="mx-auto mt-3 max-w-xl text-[12.5px] leading-8 opacity-90 md:text-[14px]">فروشگاه محصولات آرایشی، بهداشتی و شویندهٔ اصل — با ضمانت اصالت و ارسال به سراسر کشور</p>
    </div>

    <?php if ($about !== ''): ?>
        <div class="mx-auto mt-8 max-w-3xl rounded-2xl border border-line2 bg-white p-6 text-[13.5px] leading-9 text-[#555] md:p-8">
            <?= nl2br(e($about)) ?>
        </div>
    <?php endif; ?>

    <div class="mt-8 grid grid-cols-2 gap-3.5 md:grid-cols-4">
        <?php foreach ($values as [$icon, $t, $d]): ?>
            <div class="rounded-2xl border border-line2 bg-white p-5 text-center">
                <div class="text-[26px]"><?= $icon ?></div>
                <div class="mt-2 text-[13.5px] font-bold text-secondary"><?= e($t) ?></div>
                <p class="mt-1.5 text-[11.5px] leading-6 text-[#888]"><?= e($d) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-8 text-center">
        <a href="<?= e(url('/contact')) ?>" class="btn-primary inline-flex px-8 py-3 text-[13.5px]">تماس با ما</a>
    </div>
</div>
