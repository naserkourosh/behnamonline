<?php
/** @var list<array<string,mixed>> $products */
$this->meta(['title' => 'علاقه‌مندی‌ها | بهنام', 'robots' => 'noindex']);
?>
<div class="container-page py-6 md:py-8">
    <div class="mb-4 flex items-center gap-2">
        <a href="<?= e(url('/account')) ?>" class="text-secondary"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <h1 class="text-[18px] font-bold text-secondary md:text-[22px]">لیست علاقه‌مندی‌ها</h1>
    </div>
    <div class="md:flex md:items-start md:gap-8">
        <div class="md:flex-1">
            <?php if ($products === []): ?>
                <div class="rounded-2xl border border-line2 bg-surface py-16 text-center">
                    <p class="text-[14px] text-[#666]">لیست علاقه‌مندی‌های شما خالی است.</p>
                    <a href="<?= e(url('/category')) ?>" class="btn-primary mt-5 px-7 py-3 text-[13px]">مشاهده محصولات</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-3.5 md:grid-cols-3">
                    <?php foreach ($products as $product): ?>
                        <?php $this->partial('product-card', ['product' => $product]); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <aside class="mt-6 hidden md:mt-0 md:block md:w-72 md:flex-none"><?php $this->partial('account-nav'); ?></aside>
    </div>
</div>
