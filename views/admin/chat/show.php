<?php
/** @var array<string,mixed> $conv @var list<array<string,mixed>> $messages */
$name = trim(((string) ($conv['first_name'] ?? '')) . ' ' . ((string) ($conv['last_name'] ?? '')));
$name = $name !== '' ? $name : ((string) ($conv['guest_name'] ?? '') ?: 'میهمان');
$lastId = 0;
foreach ($messages as $m) { $lastId = max($lastId, (int) $m['id']); }
?>
<div class="mx-auto max-w-3xl">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="<?= e(url('/admin/chat')) ?>" class="text-[12px] text-mauve">← همه گفتگوها</a>
            <div class="mt-1 text-[14px] font-bold text-[#333]">
                <?= e($name) ?>
                <?php if (!empty($conv['mobile'])): ?><span class="mr-2 text-[11px] font-normal text-[#999] nums" dir="ltr"><?= e($conv['mobile']) ?></span><?php endif; ?>
                <?php if (empty($conv['user_id'])): ?><span class="mr-2 rounded-lg bg-surface px-2 py-0.5 text-[10px] font-semibold text-[#999]">میهمان</span><?php endif; ?>
            </div>
        </div>
        <?php if ((string) $conv['status'] === 'open'): ?>
            <form method="post" action="<?= e(url('/admin/chat/' . $conv['id'] . '/close')) ?>" onsubmit="return confirm('این گفتگو بسته شود؟');">
                <?= csrf_field() ?>
                <button type="submit" class="rounded-xl2 border border-line px-4 py-2 text-[12px] font-semibold text-danger">بستن گفتگو</button>
            </form>
        <?php else: ?>
            <span class="rounded-lg bg-surface px-3 py-1.5 text-[11px] font-bold text-[#999]">بسته‌شده — پیام جدید مشتری آن را باز می‌کند</span>
        <?php endif; ?>
    </div>

    <div id="js-chat-thread" class="max-h-[480px] min-h-[240px] space-y-3 overflow-y-auto rounded-2xl border border-line2 bg-surface p-4"
         data-poll="<?= e(url('/admin/chat/' . $conv['id'] . '/poll')) ?>" data-last="<?= $lastId ?>">
        <?php foreach ($messages as $m): $mine = $m['sender'] === 'admin'; ?>
            <div class="flex <?= $mine ? 'justify-end' : '' ?> gap-2">
                <div class="max-w-[420px] rounded-2xl px-3.5 py-2.5 text-[12.5px] leading-7 <?= $mine ? 'rounded-br-sm bg-secondary text-white' : 'rounded-bl-sm border border-line bg-white text-[#444]' ?>">
                    <?= nl2br(e($m['body'])) ?>
                    <div class="mt-1 text-[9.5px] <?= $mine ? 'text-white/70' : 'text-[#aaa]' ?> nums"><?= jdate((string) $m['created_at'], 'H:i') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if ($messages === []): ?><div class="js-chat-empty py-10 text-center text-[12px] text-[#999]">پیامی ثبت نشده است.</div><?php endif; ?>
    </div>

    <form id="js-chat-reply" action="<?= e(url('/admin/chat/' . $conv['id'] . '/send')) ?>" class="mt-3 flex items-center gap-2">
        <input name="message" maxlength="2000" autocomplete="off" placeholder="پاسخ خود را بنویسید…" aria-label="متن پاسخ" class="flex-1 rounded-xl2 border border-line bg-white px-4 py-3 text-[13px] outline-none focus:border-secondary">
        <button type="submit" class="rounded-xl2 bg-secondary px-6 py-3 text-[13px] font-bold text-white">ارسال</button>
    </form>
</div>

<script>
// jQuery is loaded at the end of <body>, so wait for DOMContentLoaded.
document.addEventListener("DOMContentLoaded", function () {
    var $ = window.jQuery;
    var $thread = $("#js-chat-thread");
    if (!$thread.length) { return; }
    var pollUrl = $thread.data("poll");
    var lastId = parseInt($thread.data("last"), 10) || 0;
    var csrf = document.querySelector('#js-logout-form input[name="_token"]');
    csrf = csrf ? csrf.value : "";

    function faNum(s) {
        var d = "۰۱۲۳۴۵۶۷۸۹";
        return String(s).replace(/[0-9]/g, function (c) { return d[+c]; });
    }
    function bubble(m) {
        var mine = m.sender === "admin";
        var $b = $('<div class="max-w-[420px] rounded-2xl px-3.5 py-2.5 text-[12.5px] leading-7"></div>')
            .addClass(mine ? "rounded-br-sm bg-secondary text-white" : "rounded-bl-sm border border-line bg-white text-[#444]")
            .text(m.body)
            .append($('<div class="mt-1 text-[9.5px] nums"></div>').addClass(mine ? "text-white/70" : "text-[#aaa]").text(faNum(m.time || "")));
        return $('<div class="flex gap-2"></div>').addClass(mine ? "justify-end" : "").append($b);
    }
    function append(msgs) {
        if (!msgs || !msgs.length) { return; }
        $thread.find(".js-chat-empty").remove();
        msgs.forEach(function (m) {
            if (m.id <= lastId) { return; }
            lastId = m.id;
            $thread.append(bubble(m));
        });
        $thread.scrollTop($thread[0].scrollHeight);
    }
    function poll() {
        $.getJSON(pollUrl + "?after=" + lastId, function (res) {
            if (res && res.ok) { append(res.messages); }
        });
    }
    $thread.scrollTop($thread[0].scrollHeight);
    setInterval(poll, 3500);

    $("#js-chat-reply").on("submit", function (e) {
        e.preventDefault();
        var $form = $(this);
        var $input = $form.find('input[name="message"]');
        var text = $.trim($input.val());
        if (!text) { return; }
        $input.prop("disabled", true);
        $.ajax({
            method: "POST",
            url: $form.attr("action"),
            data: { message: text },
            dataType: "json",
            headers: { "X-CSRF-Token": csrf, "X-Requested-With": "XMLHttpRequest" },
        }).done(function (res) {
            if (res.ok) {
                append([{ id: res.message_id, sender: "admin", body: text, time: "" }]);
                $input.val("");
            }
        }).always(function () { $input.prop("disabled", false).trigger("focus"); });
    });
});
</script>
