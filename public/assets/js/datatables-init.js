/**
 * DataTables for every <table data-datatable>, with Polish texts.
 *
 * - No pagination: all rows are shown; the row counter stays under the table.
 * - Search box and quick filters live in the card header
 *   (components/list-toolbar.php) of the same card.
 * - Rows keep the server order until the user clicks a column header.
 * - Per-table options via HTML attributes:
 *     <th data-orderable="false">   column without sorting (e.g. actions)
 */
(function () {
  "use strict";

  var pl = {
    processing: "Proszę czekać…",
    info: "Pozycje: _TOTAL_",
    infoEmpty: "Pozycje: 0",
    infoFiltered: "(z _MAX_)",
    zeroRecords: "Nic nie znaleziono",
    emptyTable: "Na razie brak pozycji",
    aria: {
      sortAscending: ": sortuj rosnąco",
      sortDescending: ": sortuj malejąco"
    }
  };

  // A regular expression that never matches: used when every checkbox of a
  // group is unchecked (nothing to show).
  var NOTHING = "(?!)";

  function escapeRegex(value) {
    return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  /** Apply the filters that target one column. */
  function applyColumnFilter(dt, toolbar, column) {
    var controls = toolbar.querySelectorAll('[data-filter-column="' + column + '"]');
    var checks = [];
    var pattern = "";

    controls.forEach(function (el) {
      if (el.type === "checkbox") {
        if (el.checked) checks.push(escapeRegex(el.value));
      } else if (el.value !== "") {
        pattern = "^" + escapeRegex(el.value) + "$";
      }
    });

    if (controls.length && controls[0].type === "checkbox") {
      pattern = checks.length ? "^(" + checks.join("|") + ")$" : NOTHING;
    }

    dt.column(column).search(pattern, true, false);
  }

  function initToolbar(dt, table) {
    var card = table.closest(".card");
    var toolbar = card ? card.querySelector("[data-list-toolbar]") : null;
    if (!toolbar) return;

    var search = toolbar.querySelector("[data-list-search]");
    if (search) {
      search.addEventListener("input", function () {
        dt.search(search.value).draw();
      });
    }

    var columns = {};
    toolbar.querySelectorAll("[data-filter-column]").forEach(function (el) {
      var column = el.getAttribute("data-filter-column");
      columns[column] = true;
      el.addEventListener("change", function () {
        applyColumnFilter(dt, toolbar, column);
        dt.draw();
      });
    });

    // Initial state (e.g. a checkbox rendered unchecked, or restored by the browser).
    Object.keys(columns).forEach(function (column) {
      applyColumnFilter(dt, toolbar, column);
    });
    dt.draw();

    // The toolbar sticks under the top bar and the table header under the
    // toolbar: expose both heights to the CSS.
    // A table wider than the card scrolls sideways instead (then its header
    // cannot stick: see panel.css).
    var topbar = document.querySelector(".panel-topbar");
    var wrapper = table.closest(".table-responsive");
    function measure() {
      if (topbar) {
        document.documentElement.style.setProperty("--panel-topbar-h", topbar.offsetHeight + "px");
      }
      card.style.setProperty("--list-toolbar-h", toolbar.offsetHeight + "px");
      if (wrapper) {
        wrapper.classList.remove("is-scrollable");
        wrapper.classList.toggle("is-scrollable", table.scrollWidth > wrapper.clientWidth + 1);
      }
    }
    measure();
    window.addEventListener("resize", measure);
  }

  document.addEventListener("DOMContentLoaded", function () {
    if (typeof DataTable === "undefined") return;
    document.querySelectorAll("table[data-datatable]").forEach(function (table) {
      var dt = new DataTable(table, {
        language: pl,
        order: [],
        autoWidth: false,
        paging: false,
        layout: {
          topStart: null,
          topEnd: null,
          bottomStart: "info",
          bottomEnd: null
        }
      });
      initToolbar(dt, table);
    });
  });
})();
