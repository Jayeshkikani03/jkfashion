(function () {
  const toggle = document.querySelector(".vl-nav-toggle");
  const nav = document.querySelector(".vl-nav--mobile");
  const overlay = document.querySelector(".vl-nav-overlay");
  const closeBtn = document.querySelector(".vl-nav-close");
  const i18n = (typeof dftData !== "undefined" && dftData.i18n) || {};

  function setNav(open) {
    if (!nav || !toggle) return;
    nav.classList.toggle("is-open", open);
    document.body.classList.toggle("vl-nav-open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    if (overlay) {
      overlay.hidden = !open;
    }
  }

  setNav(false);

  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      setNav(!nav.classList.contains("is-open"));
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      setNav(false);
    });
  }

  if (overlay) {
    overlay.addEventListener("click", function () {
      setNav(false);
    });
  }

  if (nav) {
    nav.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        setNav(false);
      });
    });
  }

  document.addEventListener("keyup", function (e) {
    if (e.key === "Escape") {
      setNav(false);
      closeDesktopDropdowns();
    }
  });

  // Desktop "More" / submenu: click to open (hover alone was clipped / unreliable).
  const desktopNav = document.querySelector(".vl-nav--desktop");

  function closeDesktopDropdowns(except) {
    if (!desktopNav) return;
    desktopNav.querySelectorAll(".menu-item-has-children.is-open, .vl-menu-more.is-open").forEach(function (item) {
      if (except && item === except) return;
      item.classList.remove("is-open");
      const trigger = item.querySelector("a");
      if (trigger) trigger.setAttribute("aria-expanded", "false");
    });
  }

  if (desktopNav) {
    desktopNav.querySelectorAll(".menu-item-has-children, .vl-menu-more").forEach(function (item) {
      const kids = Array.prototype.slice.call(item.children);
      const trigger = kids.find(function (el) { return el.tagName === "A"; });
      const submenu = kids.find(function (el) { return el.classList && el.classList.contains("sub-menu"); });
      if (!trigger || !submenu || !submenu.children.length) return;

      trigger.setAttribute("aria-haspopup", "true");
      trigger.setAttribute("aria-expanded", "false");

      trigger.addEventListener("click", function (e) {
        e.preventDefault();
        const open = !item.classList.contains("is-open");
        closeDesktopDropdowns(item);
        item.classList.toggle("is-open", open);
        trigger.setAttribute("aria-expanded", open ? "true" : "false");
      });
    });

    document.addEventListener("click", function (e) {
      if (!desktopNav.contains(e.target)) {
        closeDesktopDropdowns();
      }
    });
  }

  const mainImage = document.querySelector("[data-vl-gallery-main]");
  const thumbs = document.querySelectorAll("[data-vl-gallery-thumb]");

  if (mainImage && thumbs.length) {
    thumbs.forEach(function (thumb) {
      thumb.addEventListener("click", function () {
        const src = thumb.getAttribute("data-full");
        if (src) mainImage.setAttribute("src", src);
      });
    });
  }

  function updateCartCount(count) {
    const badges = document.querySelectorAll("[data-vl-cart-count]");
    badges.forEach(function (badge) {
      const n = parseInt(count, 10) || 0;
      badge.textContent = String(n);
      if (n > 0) {
        badge.removeAttribute("hidden");
      } else {
        badge.setAttribute("hidden", "hidden");
      }
    });
  }

  function postCart(action, payload) {
    if (typeof dftData === "undefined") {
      return Promise.reject(new Error("no dftData"));
    }
    const data = new FormData();
    data.set("action", action);
    data.set("nonce", dftData.cartNonce || "");
    Object.keys(payload || {}).forEach(function (key) {
      data.set(key, payload[key]);
    });
    return fetch(dftData.ajaxUrl, {
      method: "POST",
      body: data,
      credentials: "same-origin",
    }).then(function (r) {
      return r.json();
    });
  }

  function showMsg(el, text, ok) {
    if (!el) return;
    el.hidden = false;
    el.textContent = text;
    el.classList.remove("is-ok", "is-err");
    el.classList.add(ok ? "is-ok" : "is-err");
  }

  function addToCart(productId, qty, opts) {
    opts = opts || {};
    return postCart("vl_cart_add", {
      product_id: productId,
      qty: qty || 1,
    }).then(function (json) {
      const ok = !!(json && json.success);
      const payload = (json && json.data) || {};
      if (ok) {
        updateCartCount(payload.count);
        if (opts.redirectCheckout && dftData.checkoutUrl) {
          window.location.href = dftData.checkoutUrl;
          return payload;
        }
        if (opts.redirectCart && dftData.cartUrl) {
          window.location.href = dftData.cartUrl;
          return payload;
        }
      }
      if (!ok) {
        throw new Error(payload.message || i18n.error || "Error");
      }
      return payload;
    });
  }

  document.addEventListener("click", function (e) {
    const addBtn = e.target.closest("[data-vl-add-cart]");
    const buyBtn = e.target.closest("[data-vl-buy-now]");
    const removeBtn = e.target.closest("[data-vl-cart-remove]");

    if (addBtn) {
      e.preventDefault();
      const id = addBtn.getAttribute("data-product-id");
      const wrap = addBtn.closest("[data-vl-atc]");
      const qtyInput = wrap
        ? wrap.querySelector("[data-vl-atc-qty]")
        : null;
      const qty = qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1;
      const msg = wrap ? wrap.querySelector(".vl-atc__msg") : null;
      const gotoCart = addBtn.getAttribute("data-vl-goto-cart") === "1";
      const original = addBtn.textContent;
      addBtn.disabled = true;
      addBtn.textContent = i18n.adding || "Adding…";
      addToCart(id, qty, { redirectCart: gotoCart })
        .then(function (payload) {
          if (gotoCart) return;
          addBtn.textContent = i18n.added || "Added to cart";
          showMsg(
            msg,
            (payload && payload.message) || i18n.added || "Added to cart",
            true
          );
          setTimeout(function () {
            addBtn.textContent = original || i18n.addToCart || "Add to cart";
            addBtn.disabled = false;
          }, 1200);
        })
        .catch(function (err) {
          showMsg(msg, err.message || i18n.error || "Error", false);
          addBtn.textContent = original || i18n.addToCart || "Add to cart";
          addBtn.disabled = false;
        });
      return;
    }

    if (buyBtn) {
      e.preventDefault();
      return;
    }

    if (removeBtn) {
      e.preventDefault();
      const id = removeBtn.getAttribute("data-product-id");
      const row = removeBtn.closest(".vl-cart__row");
      removeBtn.disabled = true;
      postCart("vl_cart_update", { product_id: id, qty: 0 })
        .then(function (json) {
          const ok = !!(json && json.success);
          const payload = (json && json.data) || {};
          if (!ok) throw new Error(payload.message || i18n.error || "Error");
          updateCartCount(payload.count);
          if (row) row.remove();
          const totalEl = document.querySelector("[data-vl-cart-total]");
          if (totalEl && payload.total_html) {
            totalEl.textContent = payload.total_html;
          }
          if (payload.empty) {
            window.location.reload();
          }
        })
        .catch(function () {
          removeBtn.disabled = false;
        });
    }
  });

  document.addEventListener("change", function (e) {
    const qtyInput = e.target.closest("[data-vl-cart-qty]");
    if (!qtyInput) return;
    const id = qtyInput.getAttribute("data-product-id");
    const qty = Math.max(1, parseInt(qtyInput.value, 10) || 1);
    qtyInput.value = String(qty);
    qtyInput.disabled = true;
    postCart("vl_cart_update", { product_id: id, qty: qty })
      .then(function (json) {
        const ok = !!(json && json.success);
        const payload = (json && json.data) || {};
        if (!ok) throw new Error(payload.message || i18n.error || "Error");
        updateCartCount(payload.count);
        const totalEl = document.querySelector("[data-vl-cart-total]");
        if (totalEl && payload.total_html) {
          totalEl.textContent = payload.total_html;
        }
        // Refresh line totals simply by reload for accuracy.
        window.location.reload();
      })
      .catch(function () {
        qtyInput.disabled = false;
      });
  });

  // COD checkout form (AJAX).
  const codForm = document.querySelector("[data-vl-checkout-form], .vl-cod__form");
  if (codForm && typeof dftData !== "undefined") {
    codForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const msg = codForm.querySelector(".vl-cod__msg");
      const btn = codForm.querySelector(".vl-cod__submit");
      const data = new FormData(codForm);
      data.set("action", "vl_cod_order");

      if (btn) {
        btn.disabled = true;
        btn.textContent = i18n.placing || "Placing order…";
      }
      if (msg) {
        msg.hidden = true;
        msg.classList.remove("is-ok", "is-err");
      }

      fetch(dftData.ajaxUrl, {
        method: "POST",
        body: data,
        credentials: "same-origin",
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (json) {
          const ok = !!(json && json.success);
          const payload = (json && json.data) || {};
          const text =
            payload.message ||
            (ok ? "Order placed." : i18n.error || "Could not place order.");
          if (msg) {
            msg.hidden = false;
            msg.textContent = text;
            msg.classList.add(ok ? "is-ok" : "is-err");
          }
          if (ok) {
            updateCartCount(0);
            if (payload.redirect_url) {
              window.location.href = payload.redirect_url;
              return;
            }
            codForm.reset();
          }
        })
        .catch(function () {
          if (msg) {
            msg.hidden = false;
            msg.textContent = i18n.error || "Network error. Please try again.";
            msg.classList.add("is-err");
          }
        })
        .finally(function () {
          if (btn) {
            btn.disabled = false;
            btn.textContent = i18n.placeOrder || "Place COD order";
          }
        });
    });
  }
})();
