<?php
/** @var array<string,mixed> $user */
$this->meta(['title' => 'ویرایش پروفایل | بهنام', 'robots' => 'noindex']);
?>
<div class="container-page py-6 md:py-8">
    <div class="mb-4 flex items-center gap-2">
        <a href="<?= e(url('/account')) ?>" class="text-secondary"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <h1 class="text-[18px] font-bold text-secondary md:text-[22px]">ویرایش پروفایل</h1>
    </div>
    <div class="md:flex md:items-start md:gap-8">
        <div class="md:flex-1">
            <form method="post" action="<?= e(url('/account/profile')) ?>" class="rounded-2xl border border-line2 bg-white p-5">
                <?= csrf_field() ?>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="mb-1.5 block text-[11px] text-[#888]">نام</label><input name="first_name" value="<?= e($user['first_name']) ?>" class="ck-input" placeholder="نام"></div>
                    <div><label class="mb-1.5 block text-[11px] text-[#888]">نام خانوادگی</label><input name="last_name" value="<?= e($user['last_name']) ?>" class="ck-input" placeholder="نام خانوادگی"></div>
                </div>
                <div class="mt-3">
                    <label class="mb-1.5 block text-[11px] text-[#888]">تلفن همراه</label>
                    <input value="<?= e($user['mobile']) ?>" dir="ltr" class="ck-input text-left opacity-60" readonly>
                    <p class="mt-1.5 text-[10.5px] text-[#aaa]">شماره موبایل شناسه ورود شماست و قابل تغییر نیست.</p>
                </div>
                <button type="submit" class="btn-primary mt-4 px-8 py-3 text-[13px]">ذخیره تغییرات</button>
            </form>
        </div>
        <aside class="mt-6 hidden md:mt-0 md:block md:w-72 md:flex-none"><?php $this->partial('account-nav'); ?></aside>
    </div>
</div>
