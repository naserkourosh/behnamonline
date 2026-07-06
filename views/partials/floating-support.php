<?php
$brand = (string) setting('brand_name', 'بهنام');
$chatOn = (bool) setting('chat_enabled', true);
$hasConversation = false;
$isLogged = \App\Services\AuthService::check();
if ($chatOn) {
    $hasConversation = (new \App\Services\ChatService())->conversation() !== null;
}
?>
<!-- Support balloon — live chat when enabled, links panel otherwise -->
<div class="js-chat-panel fixed bottom-[142px] left-3.5 z-[60] hidden w-[320px] animate-balloonPop overflow-hidden rounded-2xl bg-white shadow-balloon md:bottom-24"
     <?= $chatOn ? 'data-chat="1"' : '' ?> data-active="<?= $hasConversation ? '1' : '0' ?>">
    <div class="flex items-center gap-2.5 bg-gradient-to-l from-secondary to-secondary-light p-3.5">
        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/20 text-white">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 12a8 8 0 0 1-11.5 7.2L4 20l.8-5.3A8 8 0 1 1 21 12z"/></svg>
        </div>
        <div class="flex-1 text-white">
            <div class="text-[13px] font-bold">پشتیبانی <?= e($brand) ?></div>
            <div class="text-[9.5px] opacity-85"><span class="text-[#7CF0BE]">●</span> گفتگوی آنلاین · پاسخ سریع</div>
        </div>
        <button type="button" class="js-chat-close text-xl leading-none text-white" aria-label="بستن">&times;</button>
    </div>

    <?php if ($chatOn): ?>
        <div class="js-chat-msgs max-h-[260px] min-h-[140px] space-y-3 overflow-y-auto bg-surface p-4" aria-live="polite">
            <div class="flex gap-2">
                <div class="h-6 w-6 flex-none rounded-full bg-primary"></div>
                <div class="max-w-[220px] rounded-2xl rounded-bl-sm border border-line bg-white px-3 py-2.5 text-[11.5px] leading-7 text-[#444]">سلام! 🌸 به <?= e($brand) ?> خوش آمدید. پیام‌تان را بنویسید؛ کارشناسان ما همین‌جا پاسخ می‌دهند.</div>
            </div>
        </div>
        <div class="js-chat-status hidden border-t border-line bg-[#FFF6E6] px-4 py-2 text-[10.5px] text-[#8a6a4a]">این گفتگو بسته شده است؛ با ارسال پیام جدید دوباره باز می‌شود.</div>
        <form class="js-chat-form border-t border-line bg-white p-3">
            <?php if (!$isLogged && !$hasConversation): ?>
                <input name="name" maxlength="100" placeholder="نام شما (اختیاری)" class="js-chat-name mb-2 w-full rounded-xl border border-line bg-surface px-3 py-2 text-[11.5px] outline-none focus:border-secondary">
            <?php endif; ?>
            <div class="flex items-center gap-2">
                <input name="message" maxlength="2000" autocomplete="off" placeholder="پیام خود را بنویسید…" aria-label="متن پیام" class="js-chat-input flex-1 rounded-xl border border-line bg-surface px-3 py-2.5 text-[12px] outline-none focus:border-secondary">
                <button type="submit" class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-secondary text-white" aria-label="ارسال پیام">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="transform:scaleX(-1)"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
            <div class="mt-2 text-center text-[9.5px] text-[#bbb]"><a href="<?= e(url('/account/tickets')) ?>" class="text-mauve">ارسال تیکت</a> · <a href="<?= e(url('/faq')) ?>" class="text-mauve">سوالات متداول</a></div>
        </form>
    <?php else: ?>
        <div class="max-h-[200px] space-y-3 overflow-y-auto bg-surface p-4">
            <div class="flex gap-2">
                <div class="h-6 w-6 flex-none rounded-full bg-primary"></div>
                <div class="max-w-[200px] rounded-2xl rounded-bl-sm border border-line bg-white px-3 py-2.5 text-[11.5px] leading-7 text-[#444]">سلام! 🌸 به <?= e($brand) ?> خوش آمدید. چطور می‌توانم کمکتان کنم؟</div>
            </div>
        </div>
        <div class="flex flex-col gap-2 border-t border-line bg-white p-3">
            <a href="<?= e(url('/account/tickets')) ?>" class="flex items-center justify-center gap-2 rounded-xl bg-secondary px-3 py-2.5 text-[12px] font-semibold text-white">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                ارسال تیکت پشتیبانی
            </a>
            <a href="<?= e(url('/faq')) ?>" class="flex items-center justify-center gap-2 rounded-xl border border-line bg-surface px-3 py-2.5 text-[12px] font-semibold text-secondary">سوالات متداول</a>
        </div>
    <?php endif; ?>
</div>
<button type="button" class="js-chat-toggle fixed bottom-[90px] left-3.5 z-[55] flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-secondary to-secondary-light shadow-balloon md:bottom-6" aria-label="پشتیبانی">
    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.7"><path d="M21 12a8 8 0 0 1-11.5 7.2L4 20l.8-5.3A8 8 0 1 1 21 12z"/></svg>
    <span class="absolute -right-0.5 -top-0.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-success"></span>
</button>
