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

  ready(function () {
    initTwinSidebar();
    initThemeToggle();
    initPasswordToggle();
    initAlerts();
  });
})();
