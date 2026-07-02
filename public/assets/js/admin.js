/* بهنام (Behnam) — admin panel interactions */
(function ($) {
  "use strict";

  function csrf() {
    return $('#js-logout-form input[name="_token"]').val() || "";
  }

  /* Sidebar (mobile) */
  function sidebar(open) {
    var $s = $(".js-admin-sidebar");
    $s.css("transform", open ? "translateX(0)" : "");
    $(".js-admin-overlay").toggleClass("hidden", !open);
  }
  $(document).on("click", ".js-admin-menu", function () { sidebar(true); });
  $(document).on("click", ".js-admin-overlay", function () { sidebar(false); });

  /* Logout via POST form */
  $(document).on("click", ".js-logout", function (e) {
    e.preventDefault();
    $("#js-logout-form").trigger("submit");
  });

  /* Confirm before destructive form submits */
  $(document).on("submit", ".js-confirm", function (e) {
    if (!window.confirm($(this).data("confirm") || "آیا مطمئن هستید؟")) {
      e.preventDefault();
    }
  });

  /* Dynamic rows (product specs / variants) */
  function addRow(tplId, containerSel) {
    var tpl = document.getElementById(tplId);
    if (tpl && $(containerSel).length) {
      $(containerSel).append(tpl.innerHTML);
    }
  }
  $(document).on("click", ".js-add-spec", function () { addRow("tpl-spec", ".js-specs"); });
  $(document).on("click", ".js-add-variant", function () { addRow("tpl-variant", ".js-variants"); });
  $(document).on("click", ".js-del-row", function () { $(this).closest(".js-row").remove(); });

  /* Delete a product image via AJAX */
  $(document).on("click", ".js-del-image", function () {
    if (!window.confirm("حذف این تصویر؟")) { return; }
    var $btn = $(this);
    $.ajax({
      method: "POST",
      url: $btn.data("url"),
      headers: { "X-CSRF-Token": csrf(), "X-Requested-With": "XMLHttpRequest" },
      dataType: "json",
    }).always(function () {
      $btn.closest(".js-image-tile").slideUp(180, function () { $(this).remove(); });
    });
  });

  /* Inline product sort (display order) — save on change */
  $(document).on("change", ".js-sort-input", function () {
    var $inp = $(this);
    $inp.prop("disabled", true);
    $.ajax({
      method: "POST",
      url: $inp.data("url"),
      headers: { "X-CSRF-Token": csrf(), "X-Requested-With": "XMLHttpRequest" },
      data: { sort: $inp.val() },
      dataType: "json",
    }).done(function () {
      $inp.css("background-color", "#E7F7F0");
      setTimeout(function () { $inp.css("background-color", ""); }, 700);
    }).fail(function () {
      $inp.css("background-color", "#FDECEC");
    }).always(function () {
      $inp.prop("disabled", false);
    });
  });

  /* ── Full WYSIWYG editor (WordPress-classic style, self-hosted) ── */
  var wzInsert = null; // { area, sync } target for media inserts
  var wzRange = null;  // saved selection range inside that area

  function wzExec(cmd, val) {
    try { document.execCommand("styleWithCSS", false, true); } catch (e) {}
    document.execCommand(cmd, false, typeof val === "undefined" ? null : val);
  }
  function wzSaveRange() {
    var s = window.getSelection();
    wzRange = (s && s.rangeCount) ? s.getRangeAt(0) : null;
  }
  function wzInsertHtml(html) {
    if (!wzInsert) { return; }
    wzInsert.area.focus();
    if (wzRange) { var s = window.getSelection(); s.removeAllRanges(); s.addRange(wzRange); }
    document.execCommand("insertHTML", false, html);
    wzInsert.sync();
  }

  /* Image manager modal — built once, backed by the Media Library */
  var $mm = null;
  function mmBuild() {
    if ($mm) { return $mm; }
    $mm = $(
      '<div class="fixed inset-0 z-[120] hidden items-center justify-center bg-black/50 p-4">' +
        '<div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white">' +
          '<div class="flex items-center justify-between border-b border-line px-4 py-3">' +
            '<h3 class="text-[14px] font-bold text-[#333]">مدیریت تصاویر</h3>' +
            '<button type="button" class="mm-close text-2xl leading-none text-mauve">&times;</button></div>' +
          '<div class="flex gap-3 border-b border-line px-4 py-2 text-[12.5px]">' +
            '<button type="button" class="mm-tab font-bold text-secondary" data-tab="lib">کتابخانه</button>' +
            '<button type="button" class="mm-tab text-[#999]" data-tab="up">بارگذاری</button>' +
            '<button type="button" class="mm-tab text-[#999]" data-tab="url">نشانی</button></div>' +
          '<div class="flex-1 overflow-y-auto p-4">' +
            '<div class="mm-pane" data-pane="lib"><div class="mm-grid grid grid-cols-3 gap-2 sm:grid-cols-4">…</div></div>' +
            '<div class="mm-pane hidden" data-pane="up"><input type="file" class="mm-file" accept="image/*"><button type="button" class="btn-primary mm-upload mt-3 px-5 py-2 text-[13px]">بارگذاری و درج</button></div>' +
            '<div class="mm-pane hidden" data-pane="url"><input type="text" dir="ltr" class="mm-url w-full rounded-xl2 border border-line px-3 py-2.5 text-[13px]" placeholder="https://…"><button type="button" class="btn-primary mm-url-add mt-3 px-5 py-2 text-[13px]">درج</button></div>' +
          "</div></div></div>"
    );
    $("body").append($mm);
    $mm.on("click", ".mm-close", mmClose);
    $mm.on("click", function (e) { if (e.target === $mm[0]) { mmClose(); } });
    $mm.on("click", ".mm-tab", function () {
      var tab = $(this).data("tab");
      $mm.find(".mm-tab").removeClass("font-bold text-secondary").addClass("text-[#999]");
      $(this).addClass("font-bold text-secondary").removeClass("text-[#999]");
      $mm.find(".mm-pane").addClass("hidden").filter('[data-pane="' + tab + '"]').removeClass("hidden");
    });
    $mm.on("click", ".mm-grid img", function () { mmInsert($(this).data("url")); });
    $mm.on("click", ".mm-url-add", function () {
      var u = ($mm.find(".mm-url").val() || "").trim();
      if (u) { mmInsert(u); }
    });
    $mm.on("click", ".mm-upload", function () {
      var input = $mm.find(".mm-file")[0];
      if (!input.files || !input.files[0]) { return; }
      var fd = new FormData();
      fd.append("_token", csrf());
      fd.append("folder", "library");
      fd.append("files[]", input.files[0]);
      var $btn = $(this).prop("disabled", true).text("در حال بارگذاری…");
      $.ajax({
        method: "POST", url: "/admin/media/upload", data: fd, processData: false, contentType: false,
        headers: { "X-CSRF-Token": csrf(), "X-Requested-With": "XMLHttpRequest" }, dataType: "json"
      }).done(function (res) {
        if (res.ok) { mmInsert(res.url || res.path); } else { window.alert(res.error || "بارگذاری ناموفق بود."); }
      }).fail(function () { window.alert("بارگذاری ناموفق بود."); })
        .always(function () { $btn.prop("disabled", false).text("بارگذاری و درج"); });
    });
    return $mm;
  }
  function mmLoad() {
    var $g = $mm.find(".mm-grid").html('<div class="col-span-full py-6 text-center text-[12px] text-[#999]">در حال بارگذاری…</div>');
    $.ajax({ method: "GET", url: "/admin/media/list", dataType: "json", headers: { "X-Requested-With": "XMLHttpRequest" } })
      .done(function (res) {
        if (!res.ok || !res.items.length) { $g.html('<div class="col-span-full py-6 text-center text-[12px] text-[#999]">فایلی موجود نیست.</div>'); return; }
        $g.empty();
        res.items.forEach(function (m) {
          if (m.is_video) { return; }
          $g.append('<button type="button" class="overflow-hidden rounded-lg border border-line2 hover:border-secondary"><img src="' + m.url + '" data-url="' + m.url + '" class="aspect-square w-full object-cover" loading="lazy" alt=""></button>');
        });
      })
      .fail(function () { $g.html('<div class="col-span-full py-6 text-center text-[12px] text-danger">خطا در دریافت فهرست.</div>'); });
  }
  function mmOpen() { mmBuild().css("display", "flex"); mmLoad(); }
  function mmClose() { if ($mm) { $mm.hide(); } }
  function mmInsert(url) { wzInsertHtml('<img src="' + url + '" alt="" style="max-width:100%">'); mmClose(); }

  function initWysiwyg() {
    $("textarea.js-wysiwyg").each(function () {
      var ta = this;
      if (ta.dataset.wysiwygInit) { return; }
      ta.dataset.wysiwygInit = "1";
      var $ta = $(ta).hide();

      var $wrap = $('<div class="js-wysiwyg-wrap"></div>');
      var $bar = $('<div class="wysiwyg-toolbar"></div>');
      var $area = $('<div class="wysiwyg-area rich" contenteditable="true" dir="rtl"></div>')
        .attr("data-placeholder", $ta.attr("placeholder") || "").html(ta.value || "");
      var $code = $('<textarea class="wysiwyg-code hidden" dir="ltr" spellcheck="false"></textarea>');
      var codeMode = false;
      var activeImg = null;

      function sync() { ta.value = codeMode ? $code.val() : $area.html(); }
      function wzFontSize(px) {
        var sel = window.getSelection();
        if (!sel.rangeCount) { return; }
        var r = sel.getRangeAt(0);
        if (r.collapsed) { return; }
        var span = document.createElement("span");
        span.style.fontSize = px;
        span.appendChild(r.extractContents());
        r.insertNode(span);
        sel.removeAllRanges();
        var nr = document.createRange();
        nr.selectNodeContents(span);
        sel.addRange(nr);
      }
      function imgAlign(kind) {
        if (!activeImg) { window.alert("ابتدا روی یک تصویر داخل ویرایشگر کلیک کنید."); return; }
        activeImg.removeAttribute("align");
        activeImg.style.float = ""; activeImg.style.display = ""; activeImg.style.margin = "";
        if (kind === "right") { activeImg.style.float = "right"; activeImg.style.margin = "0 0 .5rem 1rem"; }
        else if (kind === "left") { activeImg.style.float = "left"; activeImg.style.margin = "0 1rem .5rem 0"; }
        else if (kind === "center") { activeImg.style.display = "block"; activeImg.style.margin = ".5rem auto"; }
        sync();
      }
      function btn(label, title, handler, style) {
        return $('<button type="button" class="wysiwyg-btn"></button>')
          .attr("title", title).attr("style", style || "").html(label)
          .on("click", function (e) { e.preventDefault(); if (codeMode) { return; } $area.focus(); handler(); sync(); });
      }
      function sep() { return $('<span class="mx-0.5 w-px self-stretch bg-line"></span>'); }
      function sel(opts, title, onpick) {
        var $s = $('<select class="wz-select h-8 rounded-lg border border-line bg-white px-1 text-[12px] text-secondary"></select>').attr("title", title);
        opts.forEach(function (o) { $s.append('<option value="' + o[0] + '">' + o[1] + "</option>"); });
        return $s.on("change", function () { if (codeMode || (this.value === "" && this.selectedIndex === 0)) { return; } $area.focus(); onpick(this.value); sync(); this.selectedIndex = 0; });
      }

      var $head = sel([["", "قالب"], ["p", "متن"], ["h1", "تیتر ۱"], ["h2", "تیتر ۲"], ["h3", "تیتر ۳"], ["h4", "تیتر ۴"]], "قالب پاراگراف", function (v) { if (v) { wzExec("formatBlock", v.toUpperCase()); } });
      var $size = sel([["", "اندازه"], ["13px", "کوچک"], ["15px", "معمولی"], ["20px", "بزرگ"], ["26px", "خیلی بزرگ"]], "اندازه فونت", function (v) { if (v) { wzFontSize(v); } });
      var $color = $('<input type="color" value="#5C2D46" title="رنگ متن" class="h-8 w-9 cursor-pointer rounded-lg border border-line bg-white p-0.5">')
        .on("input", function () { if (codeMode) { return; } $area.focus(); wzExec("foreColor", this.value); sync(); });

      var $codeBtn = $('<button type="button" class="wysiwyg-btn" title="نمایش/ویرایش HTML">&lt;/&gt; کد</button>')
        .on("click", function (e) {
          e.preventDefault();
          codeMode = !codeMode;
          if (codeMode) { $code.val($area.html()); $area.addClass("hidden"); $code.removeClass("hidden"); $wrap.addClass("is-code"); }
          else { $area.html($code.val()); $code.addClass("hidden"); $area.removeClass("hidden"); $wrap.removeClass("is-code"); }
          sync();
        });

      $bar.append(
        $head, $size,
        btn("B", "درشت", function () { wzExec("bold"); }, "font-weight:800"),
        btn("I", "کج", function () { wzExec("italic"); }, "font-style:italic"),
        btn("U", "زیرخط", function () { wzExec("underline"); }, "text-decoration:underline"),
        btn("S", "خط‌خورده", function () { wzExec("strikeThrough"); }, "text-decoration:line-through"),
        $color, sep(),
        btn("• لیست", "لیست نقطه‌ای", function () { wzExec("insertUnorderedList"); }),
        btn("۱. لیست", "لیست شماره‌دار", function () { wzExec("insertOrderedList"); }),
        btn("❝", "نقل‌قول", function () { wzExec("formatBlock", "BLOCKQUOTE"); }), sep(),
        btn("راست", "تراز راست", function () { wzExec("justifyRight"); }),
        btn("وسط", "تراز وسط", function () { wzExec("justifyCenter"); }),
        btn("چپ", "تراز چپ", function () { wzExec("justifyLeft"); }),
        btn("دوطرفه", "تراز دوطرفه", function () { wzExec("justifyFull"); }), sep(),
        btn("🔗", "افزودن لینک", function () { var u = window.prompt("آدرس لینک:", "https://"); if (u) { wzExec("createLink", u); } }),
        btn("⛓", "حذف لینک", function () { wzExec("unlink"); }),
        btn("🖼 تصویر", "درج/مدیریت تصویر", function () { wzInsert = { area: $area[0], sync: sync }; wzSaveRange(); mmOpen(); }),
        btn("↦تصویر", "تصویر راست", function () { imgAlign("right"); }),
        btn("⇥وسط", "تصویر وسط", function () { imgAlign("center"); }),
        btn("تصویر↤", "تصویر چپ", function () { imgAlign("left"); }), sep(),
        btn("پاک‌سازی", "حذف قالب‌بندی", function () { wzExec("removeFormat"); }),
        $codeBtn
      );

      $area.on("click", "img", function () { activeImg = this; });
      $area.on("input blur", sync);
      $code.on("input", sync);
      $(ta.form).on("submit", sync);

      $wrap.append($bar, $area, $code);
      $(ta).after($wrap);
    });
  }
  initWysiwyg();

  /* Media library: copy a file's web path */
  $(document).on("click", ".js-copy-path", function () {
    var $b = $(this);
    var path = "/" + String($b.data("path") || "").replace(/^\/+/, "");
    var done = function () {
      var t = $b.text();
      $b.text("کپی شد ✓");
      setTimeout(function () { $b.text(t); }, 1200);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(path).then(done, done);
    } else {
      var $tmp = $("<input>").val(path).appendTo("body").select();
      try { document.execCommand("copy"); } catch (e) {}
      $tmp.remove();
      done();
    }
  });

  /* Menu builder: quick-fill from category */
  $(document).on("change", ".js-menu-cat", function () {
    var $opt = $(this).find("option:selected");
    if ($opt.val()) {
      $(".js-menu-url").val($opt.val());
      if (!$(".js-menu-label").val()) { $(".js-menu-label").val($opt.data("label")); }
    }
  });
})(jQuery);
