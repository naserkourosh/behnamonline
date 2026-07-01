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

  /* Menu builder: quick-fill from category */
  $(document).on("change", ".js-menu-cat", function () {
    var $opt = $(this).find("option:selected");
    if ($opt.val()) {
      $(".js-menu-url").val($opt.val());
      if (!$(".js-menu-label").val()) { $(".js-menu-label").val($opt.data("label")); }
    }
  });
})(jQuery);
