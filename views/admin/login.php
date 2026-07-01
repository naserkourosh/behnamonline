<?php $brand = (string) setting('brand_name', 'بهنام'); ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود به پنل مدیریت | <?= e($brand) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="flex min-h-screen items-center justify-center bg-cream p-5 font-sans text-ink">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center">
            <div class="text-[28px] font-extrabold text-secondary"><?= e($brand) ?><span class="text-gold">.</span></div>
            <div class="mt-1 text-[12px] text-mauve">پنل مدیریت</div>
        </div>
        <div class="rounded-3xl border border-line2 bg-white p-6 shadow-soft">
            <?php $err = \App\Core\Session::flash('error'); if ($err): ?>
                <div class="mb-4 rounded-xl2 bg-[#FDECEC] px-4 py-2.5 text-[12.5px] font-semibold text-danger"><?= e($err) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= e(url('/admin/login')) ?>" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="mb-1.5 block text-[12px] text-[#888]">نام کاربری</label>
                    <input name="username" value="<?= e(old('username')) ?>" autofocus class="w-full rounded-xl2 border border-line bg-surface px-4 py-3 text-[13px] outline-none focus:border-secondary" placeholder="admin">
                </div>
                <div>
                    <label class="mb-1.5 block text-[12px] text-[#888]">رمز عبور</label>
                    <input name="password" type="password" class="w-full rounded-xl2 border border-line bg-surface px-4 py-3 text-[13px] outline-none focus:border-secondary" placeholder="••••••••">
                </div>
                <button type="submit" class="btn-primary w-full py-3.5 text-[14px]">ورود</button>
            </form>
        </div>
        <div class="mt-4 text-center text-[11px] text-[#aaa]">بازگشت به <a href="<?= e(url('/')) ?>" class="text-secondary">فروشگاه</a></div>
    </div>
</body>
</html>
