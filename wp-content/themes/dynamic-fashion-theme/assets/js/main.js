(function () {
  const toggle = document.querySelector(".vl-nav-toggle");
  const nav = document.querySelector(".vl-nav--mobile");
  const overlay = document.querySelector(".vl-nav-overlay");
  const closeBtn = document.querySelector(".vl-nav-close");

  function setNav(open) {
    if (!nav || !toggle) return;
    nav.classList.toggle("is-open", open);
    document.body.classList.toggle("vl-nav-open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    if (overlay) {
      if (open) {
        overlay.hidden = false;
      } else {
        overlay.hidden = true;
      }
    }
  }

  // Always start closed on load.
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
    if (e.key === "Escape") setNav(false);
  });

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

  // COD order form (AJAX).
  const codForm = document.querySelector(".vl-cod__form");
  if (codForm && typeof dftData !== "undefined") {
    codForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const msg = codForm.querySelector(".vl-cod__msg");
      const btn = codForm.querySelector(".vl-cod__submit");
      const data = new FormData(codForm);
      data.set("action", "vl_cod_order");

      if (btn) {
        btn.disabled = true;
        btn.textContent = "Placing order…";
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
          const text =
            (json && json.data && json.data.message) ||
            (ok ? "Order placed." : "Could not place order.");
          if (msg) {
            msg.hidden = false;
            msg.textContent = text;
            msg.classList.add(ok ? "is-ok" : "is-err");
          }
          if (ok) {
            codForm.reset();
            const qty = codForm.querySelector('[name="qty"]');
            if (qty) qty.value = "1";
          }
        })
        .catch(function () {
          if (msg) {
            msg.hidden = false;
            msg.textContent = "Network error. Please try again.";
            msg.classList.add("is-err");
          }
        })
        .finally(function () {
          if (btn) {
            btn.disabled = false;
            btn.textContent = "Add to Cart — Place COD Order";
          }
        });
    });
  }
})();
