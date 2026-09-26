/**
 * KI-BASE panel scripts.
 * Behaviour taken from the template (twin sidebar, theme toggle, password
 * toggle, dismissible alerts) without the template demo code.
 */
(function () {
  "use strict";

  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  // ---------------------------------------------------------------
  // Twin sidebar (icon rail + menu panel). Active group/item are set
  // on the server; here only switching, collapsing and mobile menu.
  // ---------------------------------------------------------------
  function initTwinSidebar() {
    var sidebar = document.querySelector(".twin-sidebar");
    if (!sidebar) return;

    var icons = sidebar.querySelectorAll(".rail-icon[data-menu]");
    var menus = sidebar.querySelectorAll(".twin-menu");
    var collapseBtn = document.querySelector(".twin-collapse");
    var mobileToggle = document.querySelector(".twin-mobile-toggle");
    var backdrop = document.querySelector(".twin-backdrop");

    icons.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var key = btn.getAttribute("data-menu");
        icons.forEach(function (i) { i.classList.remove("active"); });
        menus.forEach(function (m) { m.classList.remove("active"); });
        btn.classList.add("active");
        var menu = sidebar.querySelector('.twin-menu[data-menu="' + key + '"]');
        if (menu) menu.classList.add("active");
        sidebar.classList.remove("is-collapsed");
        document.body.classList.remove("twin-collapsed");
        storeCollapsed(false);
      });
    });

    if (collapseBtn) {
      collapseBtn.addEventListener("click", function () {
        var collapsed = sidebar.classList.toggle("is-collapsed");
        document.body.classList.toggle("twin-collapsed", collapsed);
        storeCollapsed(collapsed);
      });
    }

    // Remember the collapsed state between pages.
    if (readCollapsed()) {
      sidebar.classList.add("is-collapsed");
      document.body.classList.add("twin-collapsed");
    }

    function openMobile() { sidebar.classList.add("is-open"); if (backdrop) backdrop.classList.add("show"); }
    function closeMobile() { sidebar.classList.remove("is-open"); if (backdrop) backdrop.classList.remove("show"); }
    if (mobileToggle) mobileToggle.addEventListener("click", openMobile);
    if (backdrop) backdrop.addEventListener("click", closeMobile);
  }

  function storeCollapsed(value) {
    try { localStorage.setItem("sidebarCollapsed", value ? "1" : "0"); } catch (e) {}
  }

  function readCollapsed() {
    try { return localStorage.getItem("sidebarCollapsed") === "1"; } catch (e) { return false; }
  }

  // ---------------------------------------------------------------
  // Light / dark theme toggle (button with [data-theme-toggle]).
  // The template CSS draws the icon from the button's aria-label.
  // ---------------------------------------------------------------
  function initThemeToggle() {
    var button = document.querySelector("[data-theme-toggle]");
    var current = document.documentElement.getAttribute("data-theme") === "dark" ? "dark" : "light";

    function apply(theme) {
      document.documentElement.setAttribute("data-theme", theme);
      if (button) {
        button.setAttribute("aria-label", theme);
        button.innerText = theme;
      }
    }

    apply(current);
    if (!button) return;

    button.addEventListener("click", function () {
      current = current === "dark" ? "light" : "dark";
      try { localStorage.setItem("theme", current); } catch (e) {}
      apply(current);
    });
  }

  // ---------------------------------------------------------------
  // Show / hide password (template .toggle-password).
  // ---------------------------------------------------------------
  function initPasswordToggle() {
    document.querySelectorAll(".toggle-password").forEach(function (toggle) {
      toggle.addEventListener("click", function () {
        var input = document.querySelector(toggle.getAttribute("data-toggle"));
        if (!input) return;
        var show = input.getAttribute("type") === "password";
        input.setAttribute("type", show ? "text" : "password");
        toggle.classList.toggle("ri-eye-off-line", show);
      });
    });
  }

  // ---------------------------------------------------------------
  // Dismissible alerts (template .remove-button inside .alert).
  // ---------------------------------------------------------------
  function initAlerts() {
    document.addEventListener("click", function (e) {
      var btn = e.target.closest(".alert .remove-button");
      if (btn) btn.closest(".alert").remove();
    });
  }

  // ---------------------------------------------------------------
  // Copy a table cell (tables with [data-copy]; cells with
  // [data-no-copy] are skipped, [data-copy-value] overrides the text).
  //   Desktop: copy icon appears on hover, click copies.
  //   Touch:   long press (0.55 s) on the cell copies.
  // ---------------------------------------------------------------
  function initCopyCells() {
    var CELL = "table[data-copy] tbody td:not([data-no-copy])";
    var toastTimer = null;

    function cellText(td) {
      if (td.hasAttribute("data-copy-value")) return td.getAttribute("data-copy-value");
      return (td.innerText || "").replace(/\s+/g, " ").trim();
    }

    function writeClipboard(text) {
      if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
      }
      return new Promise(function (resolve, reject) {
        var ta = document.createElement("textarea");
        ta.value = text;
        ta.setAttribute("readonly", "");
        ta.style.position = "fixed";
        ta.style.opacity = "0";
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand("copy") ? resolve() : reject(); } catch (e) { reject(e); }
        document.body.removeChild(ta);
      });
    }

    function toast(message) {
      var el = document.querySelector(".copy-toast");
      if (!el) {
        el = document.createElement("div");
        el.className = "copy-toast bg-neutral-900 text-white radius-8 px-16 py-8 text-sm fw-medium";
        el.setAttribute("role", "status");
        document.body.appendChild(el);
      }
      el.textContent = message;
      el.hidden = false;
      clearTimeout(toastTimer);
      toastTimer = setTimeout(function () { el.hidden = true; }, 1400);
    }

    function copyCell(td) {
      var text = cellText(td);
      if (text === "") return;
      writeClipboard(text).then(function () {
        td.classList.add("copy-flash");
        setTimeout(function () { td.classList.remove("copy-flash"); }, 400);
        toast(document.body.getAttribute("data-i18n-copied") || "Copied");
      }).catch(function () {});
    }

    // Desktop: add the icon lazily when the pointer enters a cell.
    document.addEventListener("mouseover", function (e) {
      var td = e.target.closest(CELL);
      if (!td || td.classList.contains("copyable") || cellText(td) === "") return;
      td.classList.add("copyable");
      var btn = document.createElement("button");
      btn.type = "button";
      btn.className = "copy-cell-btn";
      btn.setAttribute("aria-label", document.body.getAttribute("data-i18n-copy") || "Copy");
      btn.innerHTML = '<i class="ph ph-copy"></i>';
      td.appendChild(btn);
    });

    document.addEventListener("click", function (e) {
      var btn = e.target.closest(".copy-cell-btn");
      if (!btn) return;
      e.preventDefault();
      copyCell(btn.closest("td"));
    });

    // Touch: long press.
    var pressTimer = null, startX = 0, startY = 0, pressedTd = null, suppressClick = false;

    document.addEventListener("touchstart", function (e) {
      var td = e.target.closest(CELL);
      if (!td || e.touches.length !== 1) return;
      pressedTd = td;
      startX = e.touches[0].clientX;
      startY = e.touches[0].clientY;
      td.classList.add("copyable");
      pressTimer = setTimeout(function () {
        suppressClick = true;
        copyCell(pressedTd);
      }, 550);
    }, { passive: true });

    function cancelPress() {
      clearTimeout(pressTimer);
      pressTimer = null;
    }

    document.addEventListener("touchmove", function (e) {
      if (!pressTimer) return;
      var t = e.touches[0];
      if (Math.abs(t.clientX - startX) > 10 || Math.abs(t.clientY - startY) > 10) cancelPress();
    }, { passive: true });

    document.addEventListener("touchend", cancelPress);
    document.addEventListener("touchcancel", cancelPress);

    // A long press must not also follow a link inside the cell.
    document.addEventListener("click", function (e) {
      if (suppressClick) {
        suppressClick = false;
        if (e.target.closest(CELL)) {
          e.preventDefault();
          e.stopPropagation();
        }
      }
    }, true);

    document.addEventListener("contextmenu", function (e) {
      if (e.target.closest(CELL) && window.matchMedia("(hover: none)").matches) e.preventDefault();
    });
  }

  // ---------------------------------------------------------------
  // Bootstrap tooltips for elements with data-bs-toggle="tooltip"
  // (as in the template's tooltip.php).
  // ---------------------------------------------------------------
  function initTooltips() {
    if (typeof bootstrap === "undefined" || !bootstrap.Tooltip) return;
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
      bootstrap.Tooltip.getOrCreateInstance(el);
    });
  }

  // ---------------------------------------------------------------
  // "Back to top" button: shown after scrolling down one screen.
  // ---------------------------------------------------------------
  function initGoTop() {
    var button = document.querySelector("[data-go-top]");
    if (!button) return;

    function update() {
      button.classList.toggle("is-visible", window.scrollY > window.innerHeight);
    }
    window.addEventListener("scroll", update, { passive: true });
    update();

    button.addEventListener("click", function () {
      var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      window.scrollTo({ top: 0, behavior: reduce ? "auto" : "smooth" });
    });
  }

  // ---------------------------------------------------------------
  // <select data-hint-target="#id">: show the selected option's
  // data-hint text in the target element (e.g. "next code").
  // ---------------------------------------------------------------
  function initSelectHints() {
    document.querySelectorAll("select[data-hint-target]").forEach(function (select) {
      var target = document.querySelector(select.getAttribute("data-hint-target"));
      if (!target) return;
      function update() {
        var option = select.options[select.selectedIndex];
        target.textContent = option ? (option.getAttribute("data-hint") || "") : "";
      }
      select.addEventListener("change", update);
      update();
    });
  }

  ready(function () {
    initTwinSidebar();
    initThemeToggle();
    initPasswordToggle();
    initAlerts();
    initCopyCells();
    initTooltips();
    initGoTop();
    initSelectHints();
  });
})();
