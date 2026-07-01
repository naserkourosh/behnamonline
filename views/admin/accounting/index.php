<?php
/** @var array{products:int,orders_paid:int,orders_total:int} $counts */
?>
<div class="mb-5 rounded-2xl border border-line2 bg-white p-5">
    <h2 class="text-[15px] font-bold text-secondary">اتصال به حسابداری (هلو / محک)</h2>
    <p class="mt-2 text-[12.5px] leading-7 text-[#777]">
        خروجی کالاها و فاکتورهای فروش را با فرمت CSV سازگار با نرم‌افزارهای هلو و محک دریافت کنید. فایل با کدگذاری
        UTF-8 تولید می‌شود و در اکسل و ابزار ورود اطلاعات این نرم‌افزارها به‌درستی نمایش داده می‌شود.
    </p>
</div>

<div class="grid gap-5 sm:grid-cols-3">
    <div class="rounded-2xl border border-line2 bg-white p-5 text-center">
        <div class="text-[26px] font-extrabold text-secondary nums"><?= fa($counts['products']) ?></div>
        <div class="mt-1 text-[12px] text-[#888]">کالا</div>
    </div>
    <div class="rounded-2xl border border-line2 bg-white p-5 text-center">
        <div class="text-[26px] font-extrabold text-success nums"><?= fa($counts['orders_paid']) ?></div>
        <div class="mt-1 text-[12px] text-[#888]">فاکتور پرداخت‌شده</div>
    </div>
    <div class="rounded-2xl border border-line2 bg-white p-5 text-center">
        <div class="text-[26px] font-extrabold text-[#444] nums"><?= fa($counts['orders_total']) ?></div>
        <div class="mt-1 text-[12px] text-[#888]">کل سفارش‌ها</div>
    </div>
</div>

<div class="mt-5 grid gap-5 sm:grid-cols-2">
    <div class="rounded-2xl border border-line2 bg-white p-5">
        <h3 class="mb-1.5 text-[14px] font-bold text-[#333]">خروجی کالاها</h3>
        <p class="mb-4 text-[12px] text-[#888]">کد، بارکد، نام، گروه، برند، قیمت فروش و موجودی.</p>
        <a href="<?= e(url('/admin/accounting/products.csv')) ?>" class="btn-primary inline-block px-6 py-2.5 text-[13px]">دانلود CSV کالاها</a>
    </div>
    <div class="rounded-2xl border border-line2 bg-white p-5">
        <h3 class="mb-1.5 text-[14px] font-bold text-[#333]">خروجی فاکتورها</h3>
        <p class="mb-4 text-[12px] text-[#888]">فاکتورهای فروش با مبالغ و وضعیت پرداخت.</p>
        <div class="flex flex-wrap gap-2">
            <a href="<?= e(url('/admin/accounting/orders.csv')) ?>" class="btn-primary inline-block px-5 py-2.5 text-[13px]">فقط پرداخت‌شده</a>
            <a href="<?= e(url('/admin/accounting/orders.csv?all=1')) ?>" class="inline-block rounded-xl2 bg-surface px-5 py-2.5 text-[13px] font-semibold text-secondary">همهٔ سفارش‌ها</a>
        </div>
    </div>
</div>
