/* بهنام (Behnam) — storefront interactions (jQuery + AJAX) */
(function ($) {
  "use strict";

  var CFG = window.Behnam || { csrf: "", baseUrl: "", placeholder: "" };

  /* ── helpers ─────────────────────────────────────────────── */
  function toFa(str) {
    return String(str)
      .replace(/[0-9]/g, function (d) { return "۰۱۲۳۴۵۶۷۸۹"[d]; })
      .replace(/,/g, "٬");
  }
  function money(n) {
    return toFa(Number(n || 0).toLocaleString("en-US"));
  }
  function api(method, url, data) {
    return $.ajax({
      method: method,
      url: CFG.baseUrl + url,
      data: data,
      dataType: "json",
      headers: { "X-CSRF-Token": CFG.csrf, "X-Requested-With": "XMLHttpRequest" },
    });
  }

  /* ── toast ───────────────────────────────────────────────── */
  function toast(message, kind) {
    var color = kind === "error" ? "bg-danger" : "bg-secondary";
    var $t = $(
      '<div class="pointer-events-auto animate-toastIn rounded-2xl px-5 py-3 text-[13px] font-semibold text-white shadow-card ' +
        color +
        '"></div>'
    ).text(message);
    $("#toast-root").append($t);
    setTimeout(function () {
      $t.fadeOut(250, function () { $(this).remove(); });
    }, 2600);
  }

  /* ── cart count badge ────────────────────────────────────── */
  function updateCount(count) {
    $(".js-cart-count").each(function () {
      var $b = $(this);
      $b.text(toFa(count));
      $b.toggleClass("hidden", Number(count) <= 0);
    });
    $(".js-cart-title-count").text("(" + toFa(count) + ")");
  }

  /* ── add to cart ─────────────────────────────────────────── */
  function addToCart(productId, variantId, qty, $btn) {
    if ($btn) { $btn.prop("disabled", true); }
    return api("POST", "/api/cart", {
      product_id: productId,
      variant_id: variantId || "",
      qty: qty || 1,
    })
      .done(function (res) {
        if (res.ok) {
          updateCount(res.summary.count);
          toast(res.message || "به سبد خرید اضافه شد.");
        } else {
          toast(res.message || res.error || "خطا در افزودن به سبد.", "error");
        }
      })
      .fail(function (xhr) {
        var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || "خطا در ارتباط با سرور.";
        toast(msg, "error");
      })
      .always(function () {
        if ($btn) { $btn.prop("disabled", false); }
      });
  }

  // product-card "+" buttons
  $(document).on("click", ".js-add-cart", function () {
    var $b = $(this);
    addToCart($b.data("id"), 0, 1, $b);
  });

  /* ── wishlist (visual toggle; persistence in Phase 2) ─────── */
  $(document).on("click", ".js-wishlist", function (e) {
    e.preventDefault();
    var $svg = $(this).find("svg");
    var on = $svg.attr("fill") !== "none" && $svg.attr("fill");
    $svg.attr("fill", on ? "none" : "#E8C5C8");
    toast(on ? "از علاقه‌مندی‌ها حذف شد." : "به علاقه‌مندی‌ها اضافه شد.");
  });

  /* ── mobile menu ─────────────────────────────────────────── */
  function menu(open) {
    $(".js-menu-overlay").toggleClass("hidden", !open);
    $(".js-menu-panel").toggleClass("hidden", !open);
  }
  $(document).on("click", ".js-menu-open", function () { menu(true); });
  $(document).on("click", ".js-menu-close, .js-menu-overlay", function () { menu(false); });

  /* ── chat balloon ────────────────────────────────────────── */
  $(document).on("click", ".js-chat-toggle", function () { $(".js-chat-panel").toggleClass("hidden"); });
  $(document).on("click", ".js-chat-close", function () { $(".js-chat-panel").addClass("hidden"); });

  /* ── search suggest ──────────────────────────────────────── */
  var searchTimer = null;
  $(document).on("input", ".js-search-input", function () {
    var $input = $(this);
    var $results = $input.closest("form").find(".js-search-results");
    var q = $input.val().trim();
    clearTimeout(searchTimer);
    if (q.length < 2) { $results.addClass("hidden").empty(); return; }
    searchTimer = setTimeout(function () {
      api("GET", "/api/search?q=" + encodeURIComponent(q)).done(function (res) {
        if (!res.results || !res.results.length) {
          $results.html('<div class="p-4 text-center text-[12px] text-[#999]">نتیجه‌ای یافت نشد.</div>').removeClass("hidden");
          return;
        }
        var html = res.results.map(function (p) {
          return (
            '<a href="' + CFG.baseUrl + "/product/" + p.slug + '" class="flex items-center gap-3 border-b border-line2 px-3 py-2.5 hover:bg-surface">' +
            '<img src="' + CFG.baseUrl + "/" + p.image + '" class="h-10 w-10 rounded-lg object-cover" alt="">' +
            '<div class="flex-1"><div class="text-[12px] font-semibold text-[#333]">' + p.name + "</div>" +
            '<div class="text-[10px] text-mauve">' + (p.brand || "") + "</div></div>" +
            '<div class="text-[12px] font-bold text-secondary nums">' + money(p.price) + "</div></a>"
          );
        }).join("");
        $results.html(html).removeClass("hidden");
      });
    }, 250);
  });
  $(document).on("click", function (e) {
    if (!$(e.target).closest(".js-search-results, .js-search-input").length) {
      $(".js-search-results").addClass("hidden");
    }
  });

  /* ── hero slider ─────────────────────────────────────────── */
  $(".js-hero").each(function () {
    var $hero = $(this);
    var $slides = $hero.find(".hero-slide");
    var $dots = $hero.find(".js-hero-dot");
    var idx = 0, timer = null;
    function go(i) {
      idx = (i + $slides.length) % $slides.length;
      $slides.removeClass("opacity-100").addClass("opacity-0").eq(idx).removeClass("opacity-0").addClass("opacity-100");
      $dots.each(function (d) {
        $(this).toggleClass("w-5 bg-secondary", d === idx).toggleClass("w-1.5 bg-[#E0CDD3]", d !== idx);
      });
    }
    function start() { if ($hero.data("autoplay")) { timer = setInterval(function () { go(idx + 1); }, 4500); } }
    $dots.on("click", function () { clearInterval(timer); go($(this).data("index")); start(); });
    start();
  });

  /* ── flash-sale countdown ────────────────────────────────── */
  $(".js-countdown").each(function () {
    var $cd = $(this);
    var remaining = parseInt($cd.data("remaining"), 10) || 0;
    function tick() {
      var h = Math.floor(remaining / 3600);
      var m = Math.floor((remaining % 3600) / 60);
      var s = remaining % 60;
      var pad = function (n) { return toFa(String(n).padStart(2, "0")); };
      $cd.find(".js-cd-h").text(pad(h));
      $cd.find(".js-cd-m").text(pad(m));
      $cd.find(".js-cd-s").text(pad(s));
      if (remaining > 0) { remaining--; }
    }
    tick();
    setInterval(tick, 1000);
  });

  /* ── category: filter sheet, sort, load more ─────────────── */
  function filterSheet(open) {
    $(".js-filter-overlay").toggleClass("hidden", !open);
    $(".js-filter-sheet").toggleClass("hidden", !open);
  }
  $(document).on("click", ".js-filter-open", function () { filterSheet(true); });
  $(document).on("click", ".js-filter-close, .js-filter-overlay", function () { filterSheet(false); });
  $(document).on("change", ".js-sort", function () { $(this).closest("form").submit(); });

  $(document).on("click", ".js-load-more", function () {
    var $b = $(this);
    var nextPage = (parseInt($b.data("page"), 10) || 1) + 1;
    var query = $b.data("query") || "";
    var url = $b.data("base") + "?" + (query ? query + "&" : "") + "page=" + nextPage + "&partial=1";
    $b.prop("disabled", true).text("در حال بارگذاری…");
    $.getJSON(CFG.baseUrl + url, function (res) {
      if (res.ok) {
        $("#js-product-grid").append(res.html);
        $b.data("page", res.page);
        if (!res.hasMore) { $b.parent().remove(); }
      }
    }).always(function () {
      $b.prop("disabled", false).text("نمایش محصولات بیشتر");
    });
  });

  /* ── product detail page ─────────────────────────────────── */
  var $pdp = $("#js-pdp");
  if ($pdp.length) {
    var state = { id: $pdp.data("id"), variant: 0, qty: 1 };
    var $firstVar = $pdp.find(".js-variant.bg-secondary").first();
    if ($firstVar.length) { state.variant = $firstVar.data("id"); }

    function setQty(q) {
      state.qty = Math.max(1, q);
      $(".js-qty").text(toFa(state.qty)).attr("data-qty", state.qty);
    }
    $(document).on("click", ".js-qty-inc", function () { setQty(state.qty + 1); });
    $(document).on("click", ".js-qty-dec", function () { setQty(state.qty - 1); });

    $pdp.on("click", ".js-variant", function () {
      var $v = $(this);
      $pdp.find(".js-variant").removeClass("border-secondary bg-secondary text-white").addClass("border-line bg-white text-secondary");
      $v.removeClass("border-line bg-white text-secondary").addClass("border-secondary bg-secondary text-white");
      state.variant = $v.data("id");
      $(".js-pdp-price").text(money($v.data("price")));
      var stock = parseInt($v.data("stock"), 10);
      var $st = $(".js-pdp-stock");
      if (stock <= 0) { $st.text("ناموجود").attr("class", "js-pdp-stock text-[12px] font-bold text-danger"); }
      else if (stock <= 5) { $st.text("تنها " + toFa(stock) + " عدد در انبار").attr("class", "js-pdp-stock text-[12px] font-bold text-warning"); }
      else { $st.text("موجود در انبار").attr("class", "js-pdp-stock text-[12px] font-bold text-success"); }
      $(".js-pdp-add").prop("disabled", stock <= 0);
    });

    $(document).on("click", ".js-pdp-add", function () {
      addToCart(state.id, state.variant, state.qty, $(this));
    });

    // thumbnails
    $pdp.on("click", ".js-thumb", function () {
      var $t = $(this);
      $("#js-gallery-main").attr("src", $t.data("src")).attr("alt", $t.data("alt"));
      $pdp.find(".js-thumb").removeClass("border-secondary").addClass("border-transparent");
      $t.removeClass("border-transparent").addClass("border-secondary");
    });

    // simple zoom lightbox
    $(document).on("click", ".js-zoom", function () {
      var src = $(this).attr("src");
      var $box = $('<div class="fixed inset-0 z-[90] flex items-center justify-center bg-black/80 p-6 cursor-zoom-out"></div>');
      $box.append('<img src="' + src + '" class="max-h-full max-w-full rounded-2xl">');
      $box.on("click", function () { $(this).remove(); });
      $("body").append($box);
    });

    // tabs
    $(document).on("click", ".js-tab", function () {
      var tab = $(this).data("tab");
      $(".js-tab").removeClass("border-secondary text-secondary").addClass("border-transparent text-[#bbb]");
      $(this).removeClass("border-transparent text-[#bbb]").addClass("border-secondary text-secondary");
      $(".js-tab-panel").addClass("hidden").filter('[data-panel="' + tab + '"]').removeClass("hidden");
    });

    // bundle add
    $(document).on("click", ".js-add-bundle", function () {
      var ids = String($(this).data("ids")).split(",").filter(Boolean);
      var $b = $(this).prop("disabled", true);
      var chain = $.Deferred().resolve();
      ids.forEach(function (id) {
        chain = chain.then(function () { return addToCart(parseInt(id, 10), 0, 1); });
      });
      chain.always(function () { $b.prop("disabled", false); toast("محصولات به سبد اضافه شدند."); });
    });
  }

  /* ── cart page ───────────────────────────────────────────── */
  function renderCart(summary) {
    updateCount(summary.count);
    if (!summary.items.length) { window.location.reload(); return; }

    var byId = {};
    summary.items.forEach(function (it) { byId[it.id] = it; });

    $(".js-cart-row").each(function () {
      var $row = $(this);
      var id = $row.data("id");
      if (!byId[id]) { $row.slideUp(200, function () { $(this).remove(); }); return; }
      $row.find(".js-row-qty").text(toFa(byId[id].qty)).attr("data-qty", byId[id].qty);
      $row.find(".js-line-total").text(money(byId[id].line_total));
    });

    $(".js-sum-gross").text(money(summary.gross) + " تومان");
    $(".js-sum-savings").text("− " + money(summary.savings) + " تومان");
    $(".js-sum-shipping").html(summary.shipping === 0 ? '<span class="text-success">رایگان</span>' : money(summary.shipping) + " تومان");
    $(".js-sum-total, .js-sum-total-mobile").text(money(summary.total));
    $(".js-ship-bar").css("width", summary.free_progress + "%");
    $(".js-ship-msg").text(summary.qualifies_free ? "🎉 سفارش شما شامل ارسال رایگان است" : money(summary.free_remaining) + " تومان تا ارسال رایگان باقی مانده");
  }

  $(document).on("click", ".js-cart-inc, .js-cart-dec", function () {
    var $row = $(this).closest(".js-cart-row");
    var qty = parseInt($row.find(".js-row-qty").attr("data-qty"), 10) || 1;
    qty += $(this).hasClass("js-cart-inc") ? 1 : -1;
    api("POST", "/api/cart/update", { item_id: $row.data("id"), qty: Math.max(0, qty) })
      .done(function (res) { if (res.ok) { renderCart(res.summary); } });
  });
  $(document).on("click", ".js-cart-remove", function () {
    var $row = $(this).closest(".js-cart-row");
    api("POST", "/api/cart/remove", { item_id: $row.data("id") })
      .done(function (res) { if (res.ok) { renderCart(res.summary); } });
  });

  /* ── newsletter / coupon stubs ───────────────────────────── */
  $(document).on("submit", ".js-newsletter", function (e) {
    e.preventDefault();
    this.reset();
    toast("عضویت شما با موفقیت ثبت شد. 🌸");
  });
  $(document).on("submit", ".js-coupon", function (e) {
    e.preventDefault();
    toast("کد تخفیف نامعتبر است.", "error");
  });
})(jQuery);
