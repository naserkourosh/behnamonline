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
