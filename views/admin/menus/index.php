<?php /** @var list<array<string,mixed>> $menus */
$inp = 'rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
?>
<div class="grid gap-5 lg:grid-cols-3">
    <div class="lg:col-span-1">
        <form method="post" action="<?= e(url('/admin/menus')) ?>" class="rounded-2xl border border-line2 bg-white p-5">
            <?= csrf_field() ?>
            <h3 class="mb-3 text-[14px] font-bold text-[#333]">منوی جدید</h3>
            <input name="name" placeholder="نام منو" class="<?= $inp ?> mb-3 w-full" required>
            <button class="btn-primary w-full py-2.5 text-[13px]">ایجاد منو</button>
        </form>
    </div>
    <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl border border-line2 bg-white">
            <table class="w-full text-[12.5px]">
                <thead><tr class="border-b border-line bg-surface text-[#888]"><th class="p-3 text-right font-semibold">نام منو</th><th class="p-3 font-semibold">نامک</th><th class="p-3 font-semibold">آیتم‌ها</th><th class="p-3 font-semibold"></th></tr></thead>
                <tbody>
                    <?php foreach ($menus as $m): ?>
                        <tr class="border-b border-line2 last:border-0">
                            <td class="p-3 font-semibold text-[#333]"><?= e($m['name']) ?> <?php if ($m['slug'] === 'primary'): ?><span class="rounded bg-pink px-1.5 py-0.5 text-[9px] text-secondary">منوی هدر</span><?php endif; ?></td>
                            <td class="p-3 text-center text-[#999]" dir="ltr"><?= e($m['slug']) ?></td>
                            <td class="p-3 text-center nums"><?= fa((int) $m['item_count']) ?></td>
                            <td class="p-3 text-center"><a href="<?= e(url('/admin/menus/' . $m['id'])) ?>" class="text-secondary hover:underline">مدیریت آیتم‌ها</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-[11.5px] leading-6 text-[#999]">منوی با نامک <code dir="ltr">primary</code> در هدر فروشگاه نمایش داده می‌شود.</p>
    </div>
</div>
