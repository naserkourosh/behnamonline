<?php
/** @var list<array<string,mixed>> $items @var array<string,string> $roles */
$roleCls = ['super' => 'bg-secondary text-white', 'manager' => 'bg-pink text-secondary', 'editor' => 'bg-[#EEF2FF] text-secondary'];
$meId = \App\Services\AdminAuthService::id();
?>
<div class="mb-4 flex items-center justify-between">
    <span class="text-[13px] font-semibold text-[#555] nums"><?= fa(count($items)) ?> کاربر مدیریت</span>
    <a href="<?= e(url('/admin/staff/create')) ?>" class="btn-primary px-5 py-2.5 text-[13px]">+ کاربر جدید</a>
</div>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">کاربر</th>
                    <th class="p-3 font-semibold">نقش</th>
                    <th class="p-3 font-semibold">دسترسی</th>
                    <th class="p-3 font-semibold">آخرین ورود</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $u):
                    $custom = trim((string) ($u['capabilities'] ?? '')) !== '';
                    $capCount = $custom ? count(array_filter(explode(',', (string) $u['capabilities']))) : 0; ?>
                    <tr class="border-b border-line2 last:border-0 hover:bg-surface/50">
                        <td class="p-3">
                            <a href="<?= e(url('/admin/staff/' . $u['id'] . '/edit')) ?>" class="font-bold text-secondary"><?= e($u['name']) ?></a>
                            <div class="text-[10.5px] text-[#999]" dir="ltr">@<?= e($u['username']) ?><?= (int) $u['id'] === $meId ? ' • شما' : '' ?></div>
                        </td>
                        <td class="p-3 text-center"><span class="rounded-lg px-2 py-1 text-[10.5px] font-bold <?= $roleCls[(string) $u['role']] ?? 'bg-surface text-[#666]' ?>"><?= e($roles[(string) $u['role']] ?? $u['role']) ?></span></td>
                        <td class="p-3 text-center text-[11px] text-[#777]">
                            <?php if ((string) $u['role'] === 'super'): ?>دسترسی کامل
                            <?php elseif ($custom): ?><span class="text-secondary nums">سفارشی (<?= fa($capCount) ?>)</span>
                            <?php else: ?>پیش‌فرض نقش<?php endif; ?>
                        </td>
                        <td class="p-3 text-center text-[10.5px] text-[#999] nums"><?= !empty($u['last_login_at']) ? jdate((string) $u['last_login_at']) : '—' ?></td>
                        <td class="p-3 text-center">
                            <?php if ((int) $u['is_active']): ?><span class="rounded-lg bg-[#E7F7F0] px-2 py-1 text-[10px] font-bold text-success">فعال</span>
                            <?php else: ?><span class="rounded-lg bg-[#FDECEC] px-2 py-1 text-[10px] font-bold text-danger">غیرفعال</span><?php endif; ?>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2.5">
                                <a href="<?= e(url('/admin/staff/' . $u['id'] . '/edit')) ?>" class="text-secondary hover:underline">ویرایش</a>
                                <?php if ((int) $u['id'] !== $meId): ?>
                                    <form method="post" action="<?= e(url('/admin/staff/' . $u['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این کاربر؟"><?= csrf_field() ?><button class="text-danger hover:underline">حذف</button></form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
