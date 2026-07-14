<?php
/** @var list<array<string,mixed>> $items @var string $status */
/** @var array{pending:int,approved:int,rejected:int} $counts */
/** @var int $total @var int $page @var int $pages */
$tabs = ['pending' => 'در انتظار تایید', 'approved' => 'تاییدشده', 'rejected' => 'ردشده'];
?>
<div class="mb-4 flex flex-wrap items-center gap-2">
    <?php foreach ($tabs as $key => $label): ?>
        <a href="<?= e(url('/admin/reviews?status=' . $key)) ?>"
           class="rounded-xl2 px-4 py-2 text-[12.5px] font-semibold transition <?= $status === $key ? 'bg-secondary text-white' : 'bg-white text-[#666] border border-line hover:bg-surface' ?>">
            <?= e($label) ?> <span class="nums">(<?= fa($counts[$key]) ?>)</span>
        </a>
    <?php endforeach; ?>
</div>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">محصول</th>
                    <th class="p-3 text-right font-semibold">نویسنده</th>
                    <th class="p-3 font-semibold">امتیاز</th>
                    <th class="p-3 text-right font-semibold">متن دیدگاه</th>
                    <th class="p-3 font-semibold">تاریخ</th>
                    <th class="p-3 font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $r): ?>
                    <tr class="border-b border-line2 align-top last:border-0 hover:bg-surface/50">
                        <td class="p-3">
                            <a href="<?= e(url('/product/' . $r['product_slug'])) ?>" target="_blank" rel="noopener" class="font-semibold text-secondary hover:underline"><?= e($r['product_name']) ?> ↗</a>
                        </td>
                        <td class="p-3">
                            <div class="font-semibold text-[#444]"><?= e($r['author_name']) ?></div>
                            <?php if (!empty($r['is_verified'])): ?>
                                <span class="mt-1 inline-block rounded-lg bg-[#E7F7F0] px-2 py-0.5 text-[10px] font-bold text-success">خریدار محصول</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3 text-center text-star"><?= str_repeat('★', (int) $r['rating']) ?><span class="text-line"><?= str_repeat('★', 5 - (int) $r['rating']) ?></span></td>
                        <td class="max-w-[300px] whitespace-pre-line p-3 text-[#555]"><?= e($r['body']) ?></td>
                        <td class="p-3 text-center text-[11px] text-[#999] nums"><?= e(jdate((string) $r['created_at'], 'Y/m/d')) ?></td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2">
                                <?php if ($status !== 'approved'): ?>
                                    <form method="post" action="<?= e(url('/admin/reviews/' . $r['id'] . '/status')) ?>"><?= csrf_field() ?>
                                        <input type="hidden" name="status" value="approved">
                                        <button class="rounded-lg bg-[#E7F7F0] px-2.5 py-1 text-[11px] font-bold text-success">تایید</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($status !== 'rejected'): ?>
                                    <form method="post" action="<?= e(url('/admin/reviews/' . $r['id'] . '/status')) ?>"><?= csrf_field() ?>
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="rounded-lg bg-[#FFF4E5] px-2.5 py-1 text-[11px] font-bold text-[#B45309]">رد</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="<?= e(url('/admin/reviews/' . $r['id'] . '/delete')) ?>" class="js-confirm" data-confirm="حذف این دیدگاه؟"><?= csrf_field() ?>
                                    <button class="rounded-lg bg-[#FDECEC] px-2.5 py-1 text-[11px] font-bold text-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?>
                    <tr><td colspan="6" class="p-8 text-center text-[#999]">دیدگاهی در این وضعیت نیست.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->partial('admin/pagination', ['page' => $page, 'pages' => $pages, 'baseUrl' => url('/admin/reviews') . '?status=' . urlencode($status) . '&']); ?>
