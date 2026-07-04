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
                <div>
                    <label class="mb-1.5 block text-[12px] text-[#888]">کد امنیتی</label>
                    <div class="flex items-center gap-2">
                        <input name="captcha" autocomplete="off" inputmode="latin" dir="ltr" class="w-full rounded-xl2 border border-line bg-surface px-4 py-3 text-center text-[15px] font-bold tracking-[0.3em] outline-none focus:border-secondary" placeholder="------" required>
                        <img id="captcha-img" src="<?= e(url('/admin/captcha')) ?>" width="120" height="42" alt="کد امنیتی" class="h-[42px] flex-none rounded-lg border border-line">
                        <button type="button" id="captcha-reload" title="کد جدید" class="flex-none rounded-lg border border-line px-2.5 py-2 text-secondary hover:bg-pink">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12a9 9 0 1 1-2.64-6.36M21 3v6h-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full py-3.5 text-[14px]">ورود</button>
            </form>
        </div>
        <div class="mt-4 text-center text-[11px] text-[#aaa]">بازگشت به <a href="<?= e(url('/')) ?>" class="text-secondary">فروشگاه</a></div>
    </div>
    <script>
        (function () {
            var b = document.getElementById('captcha-reload'), img = document.getElementById('captcha-img');
            if (b && img) { b.addEventListener('click', function () { img.src = <?= json_encode(url('/admin/captcha'), JSON_UNESCAPED_SLASHES) ?> + '?r=' + Date.now(); }); }
        })();
    </script>
</body>
</html>
