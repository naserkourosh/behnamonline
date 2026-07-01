<?php
/** @var int $status */
/** @var string $title */
$messages = [
    404 => 'صفحه‌ای که به دنبال آن بودید پیدا نشد.',
    405 => 'این روش درخواست برای صفحه مجاز نیست.',
    419 => 'نشست شما منقضی شده است. لطفاً صفحه را تازه‌سازی کنید.',
    429 => 'تعداد درخواست‌ها زیاد بود. کمی صبر کنید و دوباره تلاش کنید.',
    500 => 'متأسفیم، خطایی در سرور رخ داد. لطفاً بعداً تلاش کنید.',
];
?>
<section class="container-page flex min-h-[55vh] flex-col items-center justify-center py-20 text-center">
    <div class="text-[72px] font-extrabold leading-none text-secondary nums md:text-[110px]"><?= fa($status) ?></div>
    <h1 class="mt-2 text-[18px] font-bold text-ink md:text-[22px]"><?= e($title) ?></h1>
    <p class="mt-3 max-w-sm text-[13px] leading-7 text-[#888]"><?= e($messages[$status] ?? 'خطایی رخ داد.') ?></p>
    <a href="<?= e(url('/')) ?>" class="btn-primary mt-7 px-7 py-3 text-[13px]">بازگشت به فروشگاه</a>
</section>
