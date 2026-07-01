<?php
$success = \App\Core\Session::flash('success');
$error   = \App\Core\Session::flash('error');
if ($success === null && $error === null) {
    return;
}
?>
<div class="container-page pt-4">
    <?php if ($success !== null): ?>
        <div class="rounded-xl2 border border-[#BEE9D6] bg-[#E7F7F0] px-4 py-3 text-[13px] font-semibold text-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error !== null): ?>
        <div class="rounded-xl2 border border-[#F5C9C9] bg-[#FDECEC] px-4 py-3 text-[13px] font-semibold text-danger"><?= e($error) ?></div>
    <?php endif; ?>
</div>
