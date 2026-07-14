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

  // Live filter for the (possibly large) grouped tag picker on the product
  // form. Groups are <details> accordions: searching opens matching groups.
  $(document).on("input", ".js-tag-filter", function () {
    var q = ($(this).val() || "").toString().trim().toLowerCase();
    $(".js-tag-item").each(function () {
      var name = ($(this).data("name") || "").toString().toLowerCase();
      $(this).toggle(q === "" || name.indexOf(q) !== -1);
    });
    $(".js-tag-group").each(function () {
      var any = $(this).find(".js-tag-item:visible").length > 0;
      $(this).toggle(any);
      if (q !== "" && any) { this.open = true; }
    });
  });

  /* Create a new tag inline from the product form (appears pre-checked). */
  $(document).on("click", ".js-tag-create", function () {
    var $btn = $(this), name = $.trim($("#js-new-tag-name").val() || "");
    if (name === "") { return; }
    $btn.prop("disabled", true);
    $.ajax({
      method: "POST", url: $btn.data("url"),
      headers: { "X-CSRF-Token": csrf(), "X-Requested-With": "XMLHttpRequest" },
      data: { name: name }, dataType: "json",
    }).done(function (res) {
      if (!res.ok) { window.alert(res.error || "خطا در ساخت برچسب"); return; }
      $("#js-new-tags").append(
        '<label class="js-tag-item cursor-pointer" data-name="' + res.name + '">' +
          '<input type="checkbox" name="tags[]" value="' + res.id + '" class="peer sr-only" checked>' +
          '<span class="inline-block rounded-lg border border-line px-3 py-1.5 text-[11.5px] text-[#777] transition peer-checked:border-secondary peer-checked:bg-pink peer-checked:text-secondary">' + res.name + '</span>' +
        '</label>'
      );
      $("#js-new-tag-name").val("");
    }).fail(function () {
      window.alert("خطا در ساخت برچسب");
    }).always(function () {
      $btn.prop("disabled", false);
    });
  });

  /* ── Product form: instant image upload + media-library picker ── */

  /* Build an image tile matching the PHP-rendered ones so alt/title/primary
     of freshly added images are saved on the next form submit. */
  function imageTile(img) {
    return (
      '<div class="js-image-tile flex gap-3 rounded-xl2 border border-line p-2.5" data-id="' + img.id + '">' +
        '<img src="/' + img.path + '" alt="" class="h-16 w-16 flex-none rounded-lg bg-white object-contain">' +
        '<div class="flex-1 space-y-1.5">' +
          '<input name="img_alt[' + img.id + ']" placeholder="متن جایگزین (alt)" class="w-full rounded-lg border border-line bg-surface px-2 py-1.5 text-[11.5px] outline-none">' +
          '<input name="img_title[' + img.id + ']" placeholder="عنوان (title)" class="w-full rounded-lg border border-line bg-surface px-2 py-1.5 text-[11.5px] outline-none">' +
          '<div class="flex items-center justify-between">' +
            '<label class="flex items-center gap-1.5 text-[11px] text-[#666]"><input type="radio" name="primary_image" value="' + img.id + '" class="accent-secondary"> تصویر اصلی</label>' +
            '<button type="button" class="js-del-image text-[11px] text-danger" data-url="/admin/products/images/' + img.id + '/delete">حذف</button>' +
          '</div>' +
        '</div>' +
      '</div>'
    );
  }
  function appendTiles(added) {
    var $list = $("#js-image-list").removeClass("hidden");
    (added || []).forEach(function (img) { $list.append(imageTile(img)); });
  }

  /* Warn before leaving a form with unsaved edits (product create/edit).
     Saving (submit) clears the flag; browser shows its native prompt. */
  var guardDirty = false;
  $(document).on("input change", "form.js-guard-unsaved :input", function () { guardDirty = true; });
  $(document).on("submit", "form.js-guard-unsaved", function () { guardDirty = false; });
  window.addEventListener("beforeunload", function (e) {
    if (guardDirty) { e.preventDefault(); e.returnValue = ""; }
  });

  /* WordPress-style media modal: tab 1 = library grid, tab 2 = upload.
     The library list is re-fetched on EVERY open; uploads land in the media
     library first and come back pre-selected. */
  function libSelected() { return $("#js-lib-grid .js-lib-item.border-secondary"); }
  function libRefresh() {
    var n = libSelected().length;
    $("#js-lib-count").text(n ? n + " تصویر انتخاب شده" : "");
    $(".js-lib-attach").prop("disabled", n === 0);
  }
  function libTab(name) {
    $(".js-lib-tab").each(function () {
      var on = $(this).data("tab") === name;
      $(this).toggleClass("border-secondary text-secondary", on)
             .toggleClass("border-transparent text-[#888]", !on);
    });
    $("#js-lib-pane-library").toggleClass("hidden", name !== "library");
    $("#js-lib-pane-upload").toggleClass("hidden", name !== "upload");
  }
  function libLoad(preselect) {
    var $grid = $("#js-lib-grid").html('<p class="col-span-full py-8 text-center text-[12px] text-[#999]">در حال بارگذاری…</p>');
    $.getJSON($("#js-lib-modal").data("list-url"), function (res) {
      $grid.empty();
      if (!res.items || !res.items.length) {
        $grid.append('<p class="col-span-full py-8 text-center text-[12px] text-[#999]">تصویری در کتابخانه نیست — از تب «آپلود تصویر» اضافه کنید.</p>');
        return;
      }
      res.items.forEach(function (it) {
        var sel = (preselect || []).indexOf(it.path) !== -1;
        $grid.append(
          '<button type="button" class="js-lib-item overflow-hidden rounded-xl2 border-2 ' + (sel ? "border-secondary" : "border-line") + '" data-path="' + it.path + '" title="' + it.name + '">' +
            '<img src="/' + it.path + '" alt="" class="h-24 w-full bg-white object-contain" loading="lazy">' +
          '</button>'
        );
      });
      libRefresh();
    });
  }
  $(document).on("click", ".js-lib-open", function () {
    var $btn = $(this);
    $("#js-lib-modal").removeClass("hidden").addClass("flex")
      .data("attach-url", $btn.data("attach-url") || "")
      .data("list-url", $btn.data("list-url"))
      .data("upload-url", $btn.data("upload-url"));
    $("#js-lib-upload-status").text("");
    libTab("library");
    libRefresh();
    libLoad([]);
  });
  $(document).on("click", ".js-lib-tab", function () { libTab($(this).data("tab")); });

  /* Upload tab: send to the media library, then show it selected in the grid. */
  $(document).on("change", "#js-lib-upload-input", function () {
    var input = this;
    if (!input.files || !input.files.length) { return; }
    var fd = new FormData();
    $.each(input.files, function (_, f) { fd.append("files[]", f); });
    fd.append("folder", "library");
    var $status = $("#js-lib-upload-status").text("در حال آپلود…");
    $.ajax({
      method: "POST", url: $("#js-lib-modal").data("upload-url"), data: fd,
      processData: false, contentType: false,
      headers: { "X-CSRF-Token": csrf(), "X-Requested-With": "XMLHttpRequest" },
      dataType: "json",
    }).done(function (res) {
      var paths = res.paths || (res.path ? [res.path] : []);
      if (!res.ok || !paths.length) { $status.text(res.error || "فایل نامعتبر بود."); return; }
      $status.text("✓ " + paths.length + " تصویر به کتابخانه اضافه شد");
      libTab("library");
      libLoad(paths);
    }).fail(function () {
      $status.text("خطا در آپلود؛ دوباره تلاش کنید.");
    }).always(function () {
      $(input).val("");
    });
  });

  $(document).on("click", ".js-lib-item", function () {
    $(this).toggleClass("border-secondary border-line");
    libRefresh();
  });
  $(document).on("click", ".js-lib-close", function () {
    $("#js-lib-modal").addClass("hidden").removeClass("flex");
  });
  $(document).on("click", "#js-lib-modal", function (e) {
    if (e.target === this) { $("#js-lib-modal").addClass("hidden").removeClass("flex"); }
  });
  $(document).on("click", ".js-lib-attach", function () {
    var paths = libSelected().map(function () { return $(this).data("path"); }).get();
    if (!paths.length) { return; }
    var attachUrl = $("#js-lib-modal").data("attach-url");

    // CREATE mode (no product yet): queue full tiles (hidden path + alt +
    // remove) — store() imports queued_path[]/queued_alt[] after insert.
    if (!attachUrl) {
      var $queued = $("#js-queued-list").removeClass("hidden");
      paths.forEach(function (p) {
        if ($queued.find('input[name="queued_path[]"][value="' + p + '"]').length) { return; }
        $queued.append(
          '<div class="js-queued-tile flex gap-3 rounded-xl2 border border-line p-2.5">' +
            '<img src="/' + p + '" alt="" class="h-16 w-16 flex-none rounded-lg bg-white object-contain">' +
            '<div class="flex-1 space-y-1.5">' +
              '<input type="hidden" name="queued_path[]" value="' + p + '">' +
              '<input name="queued_alt[]" placeholder="متن جایگزین (alt)" class="w-full rounded-lg border border-line bg-surface px-2 py-1.5 text-[11.5px] outline-none">' +
              '<div class="flex items-center justify-between">' +
                '<span class="text-[10.5px] text-[#aaa]">با «ایجاد محصول» ذخیره می‌شود</span>' +
                '<button type="button" class="js-queued-remove text-[11px] text-danger">حذف</button>' +
              '</div>' +
            '</div>' +
          '</div>'
        );
      });
      libSelected().removeClass("border-secondary").addClass("border-line");
      libRefresh();
      $("#js-lib-modal").addClass("hidden").removeClass("flex");
      return;
    }

    var $btn = $(this).prop("disabled", true).text("در حال افزودن…");
    $.ajax({
      method: "POST", url: attachUrl,
      headers: { "X-CSRF-Token": csrf(), "X-Requested-With": "XMLHttpRequest" },
      data: { paths: paths }, dataType: "json",
    }).done(function (res) {
      appendTiles(res.added);
      libSelected().removeClass("border-secondary").addClass("border-line");
      libRefresh();
      $("#js-lib-modal").addClass("hidden").removeClass("flex");
    }).always(function () {
      $btn.text("افزودن انتخاب‌شده‌ها");
      libRefresh();
    });
  });

  /* Remove a queued (not yet saved) image tile on the create form */
  $(document).on("click", ".js-queued-remove", function () {
    var $list = $(this).closest("#js-queued-list");
    $(this).closest(".js-queued-tile").remove();
    if (!$list.find(".js-queued-tile").length) { $list.addClass("hidden"); }
  });

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
    var raw = String($b.data("path") || "");
    // Absolute URLs and raw values (API keys) are copied as-is; relative media paths get a leading slash.
    var path = ($b.data("raw") || /^https?:\/\//.test(raw)) ? raw : "/" + raw.replace(/^\/+/, "");
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

  /* Banners: quick on/off toggle from the list (AJAX) */
  $(document).on("change", ".js-banner-toggle", function () {
    var $t = $(this);
    $t.prop("disabled", true);
    $.ajax({
      method: "POST",
      url: $t.data("url"),
      headers: { "X-CSRF-Token": csrf(), "X-Requested-With": "XMLHttpRequest" },
      dataType: "json",
    }).done(function (res) {
      if (res && res.ok) {
        $t.prop("checked", res.active);
        $t.closest("td").find(".js-banner-state")
          .text(res.active ? "فعال" : "غیرفعال")
          .toggleClass("text-success", res.active)
          .toggleClass("text-danger", !res.active);
      }
    }).fail(function () { $t.prop("checked", !$t.prop("checked")); })
      .always(function () { $t.prop("disabled", false); });
  });

  /* Banners: live image preview when a new file is picked */
  $(document).on("change", ".js-banner-img-input", function () {
    var file = this.files && this.files[0];
    if (!file || !window.FileReader) { return; }
    var reader = new FileReader();
    reader.onload = function (e) {
      $(".js-banner-img-box").removeClass("hidden")
        .find(".js-banner-img-preview").attr("src", e.target.result);
    };
    reader.readAsDataURL(file);
  });

  /* Menu builder: quick-fill from category */
  $(document).on("change", ".js-menu-cat", function () {
    var $opt = $(this).find("option:selected");
    if ($opt.val()) {
      $(".js-menu-url").val($opt.val());
      if (!$(".js-menu-label").val()) { $(".js-menu-label").val($opt.data("label")); }
    }
  });

  /* ── Jalali date picker (تقویم شمسی) ─────────────────────────
     Attach `.js-jdate` (date) or `.js-jdatetime` (date+time) to a text
     input holding a Gregorian value; it becomes a hidden carrier while a
     visible input shows the Jalali date and opens a popup calendar. The
     form still submits Gregorian (YYYY-MM-DD [HH:MM:00]) — no PHP changes. */

  // Integer division/modulo truncated toward zero (NOT Math.floor — the
  // jalaali algorithm relies on truncation for negative intermediates).
  function jdiv(a, b) { return ~~(a / b); }
  function jmod(a, b) { return a - ~~(a / b) * b; }

  var JD = {
    faD: "۰۱۲۳۴۵۶۷۸۹",
    fa: function (s) { return String(s).replace(/[0-9]/g, function (c) { return JD.faD[+c]; }); },
    pad: function (n) { return String(n).padStart(2, "0"); },
    months: ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"],
    isLeap: function (jy) { return JD.jalCal(jy).leap === 0; },
    jalCal: function (jy) {
      var breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
      var gy = jy + 621, leapJ = -14, jp = breaks[0], jm, jump = 0, n, i;
      for (i = 1; i < breaks.length; i += 1) {
        jm = breaks[i]; jump = jm - jp;
        if (jy < jm) { break; }
        leapJ = leapJ + jdiv(jump, 33) * 8 + jdiv(jmod(jump, 33), 4);
        jp = jm;
      }
      n = jy - jp;
      leapJ = leapJ + jdiv(n, 33) * 8 + jdiv(jmod(n, 33) + 3, 4);
      if (jmod(jump, 33) === 4 && jump - n === 4) { leapJ += 1; }
      var leapG = jdiv(gy, 4) - jdiv((jdiv(gy, 100) + 1) * 3, 4) - 150;
      var march = 20 + leapJ - leapG;
      if (jump - n < 6) { n = n - jump + jdiv(jump + 4, 33) * 33; }
      var leap = jmod(jmod(n + 1, 33) - 1, 4);
      if (leap === -1) { leap = 4; }
      return { leap: leap, gy: gy, march: march };
    },
    j2d: function (jy, jm, jd) {
      var r = JD.jalCal(jy);
      return JD.g2d(r.gy, 3, r.march) + (jm - 1) * 31 - jdiv(jm, 7) * (jm - 7) + jd - 1;
    },
    d2j: function (jdn) {
      var gy = JD.d2g(jdn).gy, jy = gy - 621, r = JD.jalCal(jy);
      var jdn1f = JD.g2d(gy, 3, r.march), k = jdn - jdn1f, jm, jd;
      if (k >= 0) {
        if (k <= 185) { jm = 1 + jdiv(k, 31); jd = jmod(k, 31) + 1; return { jy: jy, jm: jm, jd: jd }; }
        k -= 186;
      } else {
        jy -= 1; k += 179;
        if (r.leap === 1) { k += 1; }
      }
      jm = 7 + jdiv(k, 30); jd = jmod(k, 30) + 1;
      return { jy: jy, jm: jm, jd: jd };
    },
    g2d: function (gy, gm, gd) {
      var d = jdiv((gy + jdiv(gm - 8, 6) + 100100) * 1461, 4)
        + jdiv(153 * jmod(gm + 9, 12) + 2, 5) + gd - 34840408;
      return d - jdiv(jdiv(gy + 100100 + jdiv(gm - 8, 6), 100) * 3, 4) + 752;
    },
    d2g: function (jdn) {
      var j = 4 * jdn + 139361631;
      j = j + jdiv(jdiv(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
      var i = jdiv(jmod(j, 1461), 4) * 5 + 308;
      var gd = jdiv(jmod(i, 153), 5) + 1;
      var gm = jmod(jdiv(i, 153), 12) + 1;
      var gy = jdiv(j, 1461) - 100100 + jdiv(8 - gm, 6);
      return { gy: gy, gm: gm, gd: gd };
    },
    toJalali: function (gy, gm, gd) { return JD.d2j(JD.g2d(gy, gm, gd)); },
    toGregorian: function (jy, jm, jd) { return JD.d2g(JD.j2d(jy, jm, jd)); },
    monthLen: function (jy, jm) { return jm <= 6 ? 31 : (jm <= 11 ? 30 : (JD.isLeap(jy) ? 30 : 29)); },
  };

  function jdpInit($input) {
    var withTime = $input.hasClass("js-jdatetime");
    var todayJ = (function () { var t = new Date(); return JD.toJalali(t.getFullYear(), t.getMonth() + 1, t.getDate()); })();
    var sel = null, hh = "00", mm = "00";

    // Parse the existing Gregorian value (if any).
    var m = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/.exec($.trim($input.val() || ""));
    if (m) {
      sel = JD.toJalali(+m[1], +m[2], +m[3]);
      hh = m[4] || "00"; mm = m[5] || "00";
    }
    var view = { jy: (sel || todayJ).jy, jm: (sel || todayJ).jm };

    var $vis = $('<input type="text" readonly class="jdp-input" placeholder="انتخاب تاریخ…">')
      .attr("dir", "ltr").attr("aria-label", "انتخاب تاریخ");
    if ($input.attr("class")) { $vis.addClass($input.attr("class").replace(/js-jdate(time)?/g, "")); }
    var $panel = $('<div class="jdp-panel hidden"></div>');
    var $wrap = $('<div class="jdp-wrap"></div>');
    $input.after($wrap);
    $wrap.append($vis).append($panel);
    $input.prop("type", "hidden").appendTo($wrap);

    function label() {
      if (!sel) { $vis.val(""); return; }
      var t = JD.fa(sel.jy + "/" + JD.pad(sel.jm) + "/" + JD.pad(sel.jd));
      if (withTime) { t += "  " + JD.fa(hh + ":" + mm); }
      $vis.val(t);
    }
    function commit() {
      if (!sel) { $input.val(""); return; }
      var g = JD.toGregorian(sel.jy, sel.jm, sel.jd);
      var v = g.gy + "-" + JD.pad(g.gm) + "-" + JD.pad(g.gd);
      if (withTime) { v += " " + hh + ":" + mm + ":00"; }
      $input.val(v).trigger("change");
    }
    function render() {
      var first = JD.j2d(view.jy, view.jm, 1);
      var g = JD.d2g(first);
      var wd = (new Date(g.gy, g.gm - 1, g.gd).getDay() + 1) % 7; // Saturday-first
      var len = JD.monthLen(view.jy, view.jm);
      var html = '<div class="jdp-head">'
        + '<button type="button" class="jdp-nav" data-nav="-1">‹</button>'
        + '<span class="jdp-title">' + JD.months[view.jm - 1] + " " + JD.fa(view.jy) + "</span>"
        + '<button type="button" class="jdp-nav" data-nav="1">›</button></div>'
        + '<div class="jdp-grid">';
      ["ش", "ی", "د", "س", "چ", "پ", "ج"].forEach(function (w) { html += '<span class="jdp-wd">' + w + "</span>"; });
      var i;
      for (i = 0; i < wd; i += 1) { html += "<span></span>"; }
      for (i = 1; i <= len; i += 1) {
        var cls = "jdp-day";
        if (sel && sel.jy === view.jy && sel.jm === view.jm && sel.jd === i) { cls += " is-sel"; }
        if (todayJ.jy === view.jy && todayJ.jm === view.jm && todayJ.jd === i) { cls += " is-today"; }
        html += '<button type="button" class="' + cls + '" data-day="' + i + '">' + JD.fa(i) + "</button>";
      }
      html += "</div>";
      if (withTime) {
        html += '<div class="jdp-time"><label>ساعت</label>'
          + '<input type="text" class="jdp-h" inputmode="numeric" maxlength="2" value="' + hh + '">'
          + "<span>:</span>"
          + '<input type="text" class="jdp-m" inputmode="numeric" maxlength="2" value="' + mm + '"></div>';
      }
      html += '<div class="jdp-foot"><button type="button" class="jdp-today">امروز</button><button type="button" class="jdp-clear">حذف</button></div>';
      $panel.html(html);
    }

    $vis.on("click focus", function () { render(); $panel.removeClass("hidden"); });
    $(document).on("mousedown", function (e) {
      if (!$wrap[0].contains(e.target)) { $panel.addClass("hidden"); }
    });
    $panel.on("click", ".jdp-nav", function () {
      view.jm += +$(this).data("nav");
      if (view.jm > 12) { view.jm = 1; view.jy += 1; }
      if (view.jm < 1) { view.jm = 12; view.jy -= 1; }
      render();
    });
    $panel.on("click", ".jdp-day", function () {
      sel = { jy: view.jy, jm: view.jm, jd: +$(this).data("day") };
      label(); commit(); render();
      if (!withTime) { $panel.addClass("hidden"); }
    });
    $panel.on("input", ".jdp-h, .jdp-m", function () {
      var isH = $(this).hasClass("jdp-h");
      var v = String($(this).val()).replace(/[^0-9]/g, "");
      var n = Math.min(isH ? 23 : 59, +v || 0);
      if (isH) { hh = JD.pad(n); } else { mm = JD.pad(n); }
      if (sel) { label(); commit(); }
    });
    $panel.on("click", ".jdp-today", function () {
      sel = { jy: todayJ.jy, jm: todayJ.jm, jd: todayJ.jd };
      view = { jy: todayJ.jy, jm: todayJ.jm };
      label(); commit(); render();
      if (!withTime) { $panel.addClass("hidden"); }
    });
    $panel.on("click", ".jdp-clear", function () {
      sel = null;
      $input.val("").trigger("change");
      label();
      $panel.addClass("hidden");
    });

    label();
  }

  $(".js-jdate, .js-jdatetime").each(function () { jdpInit($(this)); });
})(jQuery);
