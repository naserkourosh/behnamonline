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

  /* ── add-to-cart animation (fly image → cart, badge bump) ──── */
  function cartTargetEl() {
    var els = $("a[href$='/cart'], .js-cart-count").filter(":visible");
    return els.length ? els.get(0) : null;
  }
  function bumpCart() {
    if (!window.Element || !Element.prototype.animate) { return; }
    var frames = [{ transform: "scale(1)" }, { transform: "scale(1.6)" }, { transform: "scale(1)" }];
    $(".js-cart-count").each(function () { try { this.animate(frames, { duration: 450, easing: "ease-out" }); } catch (e) {} });
    var t = cartTargetEl();
    if (t) { try { t.animate([{ transform: "scale(1)" }, { transform: "scale(1.25)" }, { transform: "scale(1)" }], { duration: 450 }); } catch (e) {} }
  }
  function flyToCart($src) {
    var target = cartTargetEl();
    if (!$src || !$src.length || !target || !window.Element || !Element.prototype.animate) { bumpCart(); return; }
    try {
      var el = $src.get(0);
      var s = el.getBoundingClientRect();
      var t = target.getBoundingClientRect();
      if (!s.width || !s.height) { bumpCart(); return; }
      var clone = el.cloneNode(true);
      clone.style.cssText = "position:fixed;z-index:100;margin:0;border-radius:14px;object-fit:cover;pointer-events:none;" +
        "left:" + s.left + "px;top:" + s.top + "px;width:" + s.width + "px;height:" + s.height + "px;" +
        "box-shadow:0 12px 34px rgba(92,45,70,.28)";
      document.body.appendChild(clone);
      var dx = (t.left + t.width / 2) - (s.left + s.width / 2);
      var dy = (t.top + t.height / 2) - (s.top + s.height / 2);
      var anim = clone.animate([
        { transform: "translate(0,0) scale(1)", opacity: 1 },
        { transform: "translate(" + (dx * 0.5) + "px," + (dy * 0.5 - 50) + "px) scale(0.6)", opacity: 0.95, offset: 0.6 },
        { transform: "translate(" + dx + "px," + dy + "px) scale(0.12)", opacity: 0.2 }
      ], { duration: 780, easing: "cubic-bezier(.5,-0.2,.7,1)" });
      anim.onfinish = function () { clone.remove(); bumpCart(); };
      anim.oncancel = function () { clone.remove(); };
    } catch (e) { bumpCart(); }
  }

  /* ── add to cart ─────────────────────────────────────────── */
  function addToCart(productId, variantId, qty, $btn, $source) {
    if ($btn) { $btn.prop("disabled", true); }
    return api("POST", "/api/cart", {
      product_id: productId,
      variant_id: variantId || "",
      qty: qty || 1,
    })
      .done(function (res) {
        if (res.ok) {
          updateCount(res.summary.count);
          flyToCart($source);
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
    addToCart($b.data("id"), 0, 1, $b, $b.closest(".card-rise").find("a img").first());
  });

  /* ── wishlist (persisted for logged-in customers) ────────── */
  $(document).on("click", ".js-wishlist", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var id = $btn.data("id");
    if (!id) { return; }
    api("POST", "/api/wishlist", { product_id: id }).done(function (res) {
      if (res.auth === false) {
        toast(res.message, "error");
        setTimeout(function () { window.location.href = "/login?redirect=" + encodeURIComponent(window.location.pathname); }, 1200);
        return;
      }
      if (res.ok) {
        $btn.find("svg").attr("fill", res.active ? "#E8C5C8" : "none");
        toast(res.message);
      }
    });
  });

  /* ── product compare ─────────────────────────────────────── */
  function syncCompareBar(count) {
    $(".js-compare-count").text(toFa(count));
    $(".js-compare-bar").toggleClass("hidden", count <= 0);
  }
  $(document).on("click", ".js-compare", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var id = $btn.data("id");
    if (!id) { return; }
    api("POST", "/api/compare/toggle", { id: id }).done(function (res) {
      if (!res.ok) { toast(res.error || "خطا در مقایسه", "error"); return; }
      $btn.toggleClass("bg-secondary text-white", res.in).toggleClass("text-secondary", !res.in);
      var $label = $btn.find(".js-compare-label");
      if ($label.length) { $label.text(res.in ? "در لیست مقایسه ✓" : "افزودن به مقایسه"); }
      syncCompareBar(res.count);
      toast(res.in ? "به لیست مقایسه اضافه شد" : "از لیست مقایسه حذف شد");
    });
  });
  $(document).on("click", ".js-compare-remove", function () {
    api("POST", "/api/compare/remove", { id: $(this).data("id") }).done(function () { window.location.reload(); });
  });
  $(document).on("click", ".js-compare-clear", function () {
    api("POST", "/api/compare/clear", {}).done(function () {
      syncCompareBar(0);
      if (/\/compare\/?$/.test(window.location.pathname)) { window.location.reload(); }
    });
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

  /* ── per-card flash countdowns (data-countdown = unix end ts) ─ */
  var $flashCds = $(".js-flash-cd");
  if ($flashCds.length) {
    var tickFlash = function () {
      var now = Math.floor(Date.now() / 1000);
      $flashCds.each(function () {
        var $el = $(this);
        var left = (parseInt($el.data("countdown"), 10) || 0) - now;
        if (left <= 0) { $el.remove(); return; }
        var pad = function (n) { return String(n).padStart(2, "0"); };
        var t = pad(Math.floor(left / 3600)) + ":" + pad(Math.floor((left % 3600) / 60)) + ":" + pad(left % 60);
        $el.find(".js-cd-text").text(toFa(t));
      });
    };
    tickFlash();
    setInterval(tickFlash, 1000);
  }

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
      addToCart(state.id, state.variant, state.qty, $(this), $("#js-gallery-main"));
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
    $(".js-sum-shipping").html('محاسبه پس از ثبت آدرس');
    $(".js-sum-total, .js-sum-total-mobile").text(money(summary.total));
    $(".js-ship-bar").css("width", summary.free_progress + "%");
    $(".js-ship-msg").text(summary.qualifies_free ? "🎉 سفارش شما شامل ارسال رایگان است" : money(summary.free_remaining) + " تومان تا ارسال رایگان باقی مانده");

    // coupon discount row
    if (summary.coupon_discount > 0) {
      $(".js-sum-coupon-row").removeClass("hidden");
      $(".js-sum-coupon").text("− " + money(summary.coupon_discount) + " تومان");
      $(".js-sum-coupon-code").text(summary.coupon_code || "");
    } else {
      $(".js-sum-coupon-row").addClass("hidden");
    }
    renderCouponBox(summary);
  }

  function renderCouponBox(summary) {
    var $box = $("#js-coupon-box");
    if (!$box.length) { return; }
    if (summary.coupon_code) {
      $box.html('<div class="flex items-center justify-between"><div class="text-[12px] text-[#444]">✅ کد «<span class="font-bold text-success">' +
        summary.coupon_code + '</span>» اعمال شد</div><button type="button" class="js-coupon-remove text-[11.5px] font-semibold text-danger">حذف</button></div>');
    } else {
      var err = summary.coupon_error ? '<p class="mt-2 text-[11px] text-danger">' + summary.coupon_error + "</p>" : "";
      $box.html('<form class="js-coupon flex gap-2.5"><input name="code" class="flex-1 rounded-xl border border-line bg-surface px-3.5 py-2.5 text-[12px] outline-none focus:border-secondary" placeholder="کد تخفیف یا کارت هدیه"><button type="submit" class="rounded-xl bg-secondary px-5 text-[12.5px] font-bold text-white">ثبت</button></form>' + err);
    }
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
    var code = ($(this).find('input[name="code"]').val() || "").trim();
    if (!code) { toast("کد تخفیف را وارد کنید.", "error"); return; }
    api("POST", "/api/cart/coupon", { code: code }).done(function (res) {
      if (res.summary) { renderCart(res.summary); }
      toast(res.ok ? (res.message || "کد تخفیف اعمال شد.") : (res.error || "کد تخفیف نامعتبر است."), res.ok ? "success" : "error");
    });
  });
  $(document).on("click", ".js-coupon-remove", function () {
    api("POST", "/api/cart/coupon/remove", {}).done(function (res) {
      if (res.summary) { renderCart(res.summary); }
      toast("کد تخفیف حذف شد.");
    });
  });

  /* ── promotional popup ───────────────────────────────────── */
  (function () {
    var $p = $("#js-popup");
    if (!$p.length) { return; }
    var id = $p.data("id");
    var freq = $p.data("frequency");
    var key = "behnam_popup_" + id;

    function seenRecently() {
      try {
        if (freq === "always") { return false; }
        if (freq === "once_session") { return sessionStorage.getItem(key) === "1"; }
        // once_day
        var ts = parseInt(localStorage.getItem(key) || "0", 10);
        return ts && (Date.now() - ts) < 86400000;
      } catch (e) { return false; }
    }
    function mark() {
      try {
        if (freq === "once_session") { sessionStorage.setItem(key, "1"); }
        else if (freq === "once_day") { localStorage.setItem(key, String(Date.now())); }
      } catch (e) {}
    }
    function close() { $p.addClass("hidden"); mark(); }

    if (seenRecently()) { return; }
    setTimeout(function () { $p.removeClass("hidden"); }, (parseInt($p.data("delay"), 10) || 0) * 1000);
    $p.on("click", ".js-popup-close, .js-popup-backdrop", close);
    $p.on("click", ".btn-primary", mark);
  })();

  /* ── shared OTP + geo helpers (Phase 2) ──────────────────── */
  function toEn(s) {
    return String(s).replace(/[۰-۹]/g, function (d) { return "۰۱۲۳۴۵۶۷۸۹".indexOf(d); });
  }
  function bindOtpBoxes($scope) {
    var $boxes = $scope.find(".js-otp-box");
    $boxes.on("input", function () {
      this.value = toEn(this.value).replace(/[^0-9]/g, "").slice(0, 1);
      if (this.value && this.nextElementSibling) { $(this.nextElementSibling).trigger("focus"); }
    });
    $boxes.on("keydown", function (e) {
      if (e.key === "Backspace" && !this.value && this.previousElementSibling) { $(this.previousElementSibling).trigger("focus"); }
    });
    return {
      value: function () { var c = ""; $boxes.each(function () { c += this.value || ""; }); return c; },
      fill: function (code) { $boxes.each(function (i) { this.value = code[i] || ""; }); },
      clear: function () { $boxes.val(""); $boxes.first().trigger("focus"); },
    };
  }
  function startResend($el, seconds) {
    var t = seconds;
    function tick() {
      if (t > 0) { $el.text("ارسال مجدد کد تا " + toFa(t) + " ثانیه دیگر"); t--; }
      else { clearInterval(iv); $el.html('<button type="button" class="js-resend font-bold text-secondary">ارسال مجدد کد</button>'); }
    }
    tick();
    var iv = setInterval(tick, 1000);
    return function () { clearInterval(iv); };
  }
  function populateCities($province, $city, geo, selected) {
    var cities = geo[$province.val()] || [];
    $city.empty();
    if (!cities.length) { $city.append('<option value="">ابتدا استان</option>'); return; }
    cities.forEach(function (c) {
      $city.append('<option value="' + c + '"' + (c === selected ? " selected" : "") + ">" + c + "</option>");
    });
  }

  /* ── checkout ────────────────────────────────────────────── */
  var $ck = $("#checkout-page");
  if ($ck.length) {
    var ckCfg = JSON.parse($("#checkout-config").text());
    var $prov = $("#ck-province"), $city = $("#ck-city");
    var shipCost = 0, shipReady = false, shipCollect = false;

    function renderShipping() {
      var province = $prov.val(), city = $city.val();
      shipReady = false; shipCost = 0; shipCollect = false;
      if (!city) {
        $("#ck-shipping").html('<p class="text-[12px] text-[#999]">برای نمایش روش‌های ارسال، استان و شهر را انتخاب کنید.</p>');
        updateTotal(); return;
      }
      $("#ck-shipping").html('<p class="text-[12px] text-[#999]">در حال محاسبه هزینه ارسال…</p>');
      api("GET", "/api/shipping/quote", { province: province, city: city }).done(function (res) {
        var opts = (res && res.options) || [];
        if (!opts.length) {
          $("#ck-shipping").html('<p class="text-[12px] text-[#999]">روش ارسالی برای این مقصد یافت نشد.</p>');
          shipReady = false; updateTotal(); return;
        }
        var html = "";
        opts.forEach(function (o, i) {
          var costLabel = o.collect ? "پس‌کرایه" : (o.cost === 0 ? "رایگان" : money(o.cost) + " ت");
          var costCls = (o.collect || o.cost === 0) ? "text-success" : "text-secondary";
          var meta = [];
          if (o.desc) meta.push(o.desc);
          if (o.eta) meta.push("تحویل: " + o.eta);
          html += '<label class="flex cursor-pointer items-center gap-3 rounded-xl2 border p-3 ' + (i === 0 ? "border-secondary bg-pink" : "border-line") + '">' +
            '<input type="radio" name="ship_opt" value="' + o.key + '" data-cost="' + o.cost + '" data-collect="' + (o.collect ? 1 : 0) + '" class="accent-secondary" ' + (i === 0 ? "checked" : "") + ">" +
            '<div class="flex-1"><div class="text-[13px] font-bold text-secondary">' + o.label + '</div><div class="text-[10.5px] text-[#999]">' + meta.join(" · ") + "</div></div>" +
            '<span class="text-[12px] font-bold ' + costCls + '">' + costLabel + "</span></label>";
        });
        $("#ck-shipping").html(html);
        shipCost = parseInt(opts[0].cost, 10) || 0; shipCollect = !!opts[0].collect; shipReady = true; updateTotal();
      }).fail(function () {
        $("#ck-shipping").html('<p class="text-[12px] text-danger">خطا در محاسبه هزینه ارسال. دوباره تلاش کنید.</p>');
        shipReady = false; updateTotal();
      });
    }
    function updateTotal() {
      $(".js-ck-total").text(money(ckCfg.net + (shipReady ? shipCost : 0)));
      var html;
      if (!shipReady) html = '<span class="text-mauve">محاسبه پس از ثبت آدرس</span>';
      else if (shipCollect) html = '<span class="text-success">پس‌کرایه</span>';
      else if (shipCost === 0) html = '<span class="text-success">رایگان</span>';
      else html = money(shipCost) + " تومان";
      $(".js-ck-ship-cost").html(html);
    }
    $prov.on("change", function () { populateCities($prov, $city, ckCfg.geo, ""); renderShipping(); });
    $city.on("change", renderShipping);
    $ck.on("change", 'input[name="ship_opt"]', function () {
      shipCost = parseInt($(this).data("cost"), 10) || 0;
      shipCollect = String($(this).data("collect")) === "1";
      shipReady = true; updateTotal();
    });
    if ($prov.val()) { populateCities($prov, $city, ckCfg.geo, ckCfg.prefillCity || ""); }
    renderShipping();

    // No OTP: submit the shipping form → create the order → go to payment.
    function ckPayload() {
      var data = {};
      $("#ck-form").serializeArray().forEach(function (f) { data[f.name] = f.value; });
      data.shipping_method = $('input[name="ship_opt"]:checked').val() || "";
      return data;
    }
    $(document).on("click", ".js-ck-send", function () {
      if (!$('input[name="ship_opt"]:checked').length) { toast("لطفاً روش ارسال را انتخاب کنید.", "error"); return; }
      var $btn = $(this).prop("disabled", true);
      api("POST", "/checkout/place", ckPayload()).done(function (res) {
        if (!res.ok) { toast(res.error || "خطا در ثبت سفارش", "error"); return; }
        updateCount(0);
        window.location.href = res.payment_url;
      }).fail(function (xhr) { toast((xhr.responseJSON && xhr.responseJSON.error) || "خطا در ثبت سفارش", "error"); })
        .always(function () { $btn.prop("disabled", false); });
    });
  }

  /* ── login ───────────────────────────────────────────────── */
  var $lg = $("#login-page");
  if ($lg.length) {
    var lgOtp = bindOtpBoxes($lg), lgStop;
    var lgRedirect = $lg.data("redirect") || "/account";
    function lgSend($btn) {
      var mobile = toEn($("#lg-mobile").val()).replace(/[^0-9]/g, "");
      if (!/^09\d{9}$/.test(mobile)) { toast("شماره موبایل معتبر نیست.", "error"); return; }
      $btn && $btn.prop("disabled", true);
      api("POST", "/login/send-otp", { mobile: mobile }).done(function (res) {
        if (!res.ok) { toast(res.error || "خطا", "error"); return; }
        $(".js-lg-mobile").text(mobile);
        $("#lg-step-mobile").addClass("hidden");
        $("#lg-step-otp").removeClass("hidden");
        if (res.dev_code) { lgOtp.fill(res.dev_code); toast("کد تست: " + res.dev_code); }
        if (lgStop) lgStop();
        lgStop = startResend($(".js-lg-resend"), res.resend_wait || 90);
      }).fail(function (xhr) { toast((xhr.responseJSON && xhr.responseJSON.error) || "خطا", "error"); })
        .always(function () { $btn && $btn.prop("disabled", false); });
    }
    $(document).on("click", ".js-lg-send", function () { lgSend($(this)); });
    $(document).on("click", "#login-page .js-resend", function () { lgSend(); });
    $(document).on("click", ".js-lg-change", function () { $("#lg-step-otp").addClass("hidden"); $("#lg-step-mobile").removeClass("hidden"); });
    $(document).on("click", ".js-lg-verify", function () {
      var code = lgOtp.value();
      if (code.length < 5) { toast("کد ۵ رقمی را کامل وارد کنید.", "error"); return; }
      var $btn = $(this).prop("disabled", true);
      api("POST", "/login/verify", { code: code, redirect: lgRedirect }).done(function (res) {
        if (res.ok) { window.location.href = res.redirect; }
        else { toast(res.error || "کد نادرست", "error"); lgOtp.clear(); }
      }).fail(function (xhr) { toast((xhr.responseJSON && xhr.responseJSON.error) || "خطا", "error"); })
        .always(function () { $btn.prop("disabled", false); });
    });
  }

  /* ── addresses (province→city + inline edit) ─────────────── */
  var $addr = $("#addresses-page");
  if ($addr.length) {
    var addrGeo = JSON.parse($("#addr-geo").text());
    var $ap = $("#addr-province"), $ac = $("#addr-city");
    $ap.on("change", function () { populateCities($ap, $ac, addrGeo, ""); });
    $(document).on("click", ".js-addr-edit", function () {
      var a = $(this).closest("[data-addr]").data("addr");
      $("#addr-id").val(a.id);
      $("#addr-receiver").val(a.receiver_name);
      $("#addr-mobile").val(a.mobile);
      $("#addr-address").val(a.address);
      $("#addr-postal").val(a.postal_code || "");
      $("#addr-default").prop("checked", String(a.is_default) === "1");
      $ap.val(a.province); populateCities($ap, $ac, addrGeo, a.city);
      $("#addr-form-title").text("ویرایش آدرس");
      $(".js-addr-reset").removeClass("hidden");
      $("html,body").animate({ scrollTop: $("#addr-form").offset().top - 80 }, 300);
    });
    $(document).on("click", ".js-addr-reset", function () {
      $("#addr-form")[0].reset(); $("#addr-id").val(""); $ac.html('<option value="">ابتدا استان</option>');
      $("#addr-form-title").text("افزودن آدرس جدید"); $(this).addClass("hidden");
    });
  }
})(jQuery);
