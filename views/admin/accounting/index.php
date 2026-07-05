<?php
/** @var array{products:int,orders_paid:int,orders_total:int} $counts */
/** @var string $apiKey @var bool $torobEnabled @var string $torobJson @var string $torobXml @var string $apiBase */
/** @var string $syncDriver @var bool $syncReady */
$driverLabels = ['none' => 'تنظیم‌نشده', 'holoo' => 'هلو', 'mahak' => 'محک'];
?>
<div class="mb-5 rounded-2xl border border-line2 bg-white p-5">
    <h2 class="text-[15px] font-bold text-secondary">حسابداری و یکپارچه‌سازی</h2>
    <p class="mt-2 text-[12.5px] leading-7 text-[#777]">
        خروجی CSV سازگار با هلو/محک، فید محصولات برای ترب، و API برای اتصال نرم‌افزارهای حسابداری و انبارداری.
    </p>
</div>

<!-- ── Torob feed ── -->
<div class="mb-5 rounded-2xl border border-line2 bg-white p-5">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-[14px] font-bold text-[#333]">اتصال به ترب (Torob)</h3>
        <form method="post" action="<?= e(url('/admin/accounting/torob')) ?>">
            <?= csrf_field() ?>
            <label class="flex cursor-pointer items-center gap-2 text-[12px] text-[#555]">
                <input type="checkbox" name="torob_enabled" value="1" class="h-5 w-5 accent-secondary" <?= $torobEnabled ? 'checked' : '' ?> onchange="this.form.submit()">
                فعال
            </label>
        </form>
    </div>
    <p class="mb-3 text-[12px] leading-7 text-[#888]">این نشانی فید را در پنل فروشندگان ترب ثبت کنید تا محصولات به‌صورت خودکار در ترب نمایه شوند (هر محصول در صفحه‌اش هم داده‌های ترب را دارد).</p>
    <div class="space-y-2">
        <div class="flex items-center gap-2 rounded-xl2 border border-line bg-surface px-3 py-2">
            <span class="text-[11px] text-[#999]">JSON</span>
            <code class="flex-1 truncate text-[12px] text-secondary" dir="ltr"><?= e($torobJson) ?></code>
            <button type="button" class="js-copy-path flex-none rounded-lg bg-pink px-2.5 py-1 text-[11px] font-semibold text-secondary" data-path="<?= e($torobJson) ?>">کپی</button>
        </div>
        <div class="flex items-center gap-2 rounded-xl2 border border-line bg-surface px-3 py-2">
            <span class="text-[11px] text-[#999]">XML&nbsp;</span>
            <code class="flex-1 truncate text-[12px] text-secondary" dir="ltr"><?= e($torobXml) ?></code>
            <button type="button" class="js-copy-path flex-none rounded-lg bg-pink px-2.5 py-1 text-[11px] font-semibold text-secondary" data-path="<?= e($torobXml) ?>">کپی</button>
        </div>
    </div>
</div>

<!-- ── Integration API for accounting/inventory software ── -->
<div class="mb-5 rounded-2xl border border-line2 bg-white p-5">
    <h3 class="mb-2 text-[14px] font-bold text-[#333]">API یکپارچه‌سازی (هلو / محک / انبارداری)</h3>
    <p class="mb-3 text-[12px] leading-7 text-[#888]">با این کلید، نرم‌افزار حسابداری می‌تواند کالاها و سفارش‌ها را بخواند و موجودی/قیمت را به‌روزرسانی کند. کلید را محرمانه نگه دارید.</p>
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <div class="flex flex-1 items-center gap-2 rounded-xl2 border border-line bg-surface px-3 py-2">
            <span class="text-[11px] text-[#999]">API Key</span>
            <code class="flex-1 truncate text-[12px] text-[#333]" dir="ltr"><?= $apiKey !== '' ? e($apiKey) : '— هنوز ساخته نشده —' ?></code>
            <?php if ($apiKey !== ''): ?><button type="button" class="js-copy-path flex-none rounded-lg bg-pink px-2.5 py-1 text-[11px] font-semibold text-secondary" data-raw="1" data-path="<?= e($apiKey) ?>">کپی</button><?php endif; ?>
        </div>
        <form method="post" action="<?= e(url('/admin/accounting/api-key')) ?>" class="js-confirm" data-confirm="کلید فعلی باطل و کلید جدید ساخته شود؟">
            <?= csrf_field() ?>
            <button class="btn-primary px-4 py-2.5 text-[12.5px]"><?= $apiKey !== '' ? 'ساخت کلید جدید' : 'ساخت کلید' ?></button>
        </form>
    </div>
    <div class="rounded-xl2 bg-surface p-3 text-[11.5px] leading-7 text-[#666]" dir="ltr">
        <div class="mb-1 text-[#999]">Endpoints (header: <span class="text-secondary">X-Api-Key</span>)</div>
        <div>GET&nbsp; <?= e($apiBase) ?>/products?page=1&amp;limit=100</div>
        <div>GET&nbsp; <?= e($apiBase) ?>/orders?paid=1&amp;with_items=1</div>
        <div>POST <?= e($apiBase) ?>/stock &nbsp;<span class="text-[#999]">{"items":[{"code":"SKU","stock":12,"price":150000}]}</span></div>
    </div>
</div>

<!-- ── Holoo/Mahak live web-service sync ── -->
<div class="mb-5 rounded-2xl border border-line2 bg-white p-5">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-[14px] font-bold text-[#333]">همگام‌سازی مستقیم با وب‌سرویس حسابداری</h3>
            <p class="mt-1 text-[12px] text-[#888]">راه‌انداز فعلی: <span class="font-bold text-secondary"><?= e($driverLabels[$syncDriver] ?? $syncDriver) ?></span>
                <?php if ($syncReady): ?><span class="mr-1 rounded bg-[#E7F7F0] px-2 py-0.5 text-[10.5px] font-bold text-success">آماده</span>
                <?php else: ?><span class="mr-1 rounded bg-[#FDECEC] px-2 py-0.5 text-[10.5px] font-bold text-danger">تنظیم در .env لازم است</span><?php endif; ?>
            </p>
        </div>
        <form method="post" action="<?= e(url('/admin/accounting/sync')) ?>">
            <?= csrf_field() ?>
            <button class="btn-outline px-5 py-2.5 text-[12.5px]" <?= $syncReady ? '' : 'disabled' ?>>دریافت موجودی و قیمت</button>
        </form>
    </div>
    <p class="mt-2 text-[11px] leading-6 text-[#999]">برای فعال‌سازی، در فایل .env مقادیر <code dir="ltr">ACCOUNTING_DRIVER=holoo|mahak</code>، <code dir="ltr">ACCOUNTING_URL</code> و کلید/نام‌کاربری را تنظیم کنید. تطبیق کالاها بر اساس بارکد یا کد (SKU) انجام می‌شود.</p>
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
