/**
 * DataTables for every <table data-datatable>, with Polish texts.
 * Per-table options via HTML attributes:
 *   data-page-length="25"         rows per page
 *   <th data-orderable="false">   column without sorting (e.g. actions)
 * Rows keep the server order until the user clicks a column header.
 */
(function () {
  "use strict";

  var pl = {
    processing: "Proszę czekać…",
    search: "Szukaj:",
    lengthMenu: "_MENU_ na stronie",
    info: "Pozycje _START_–_END_ z _TOTAL_",
    infoEmpty: "Brak pozycji",
    infoFiltered: "(przefiltrowano z _MAX_)",
    zeroRecords: "Nic nie znaleziono",
    emptyTable: "Na razie brak pozycji",
    paginate: { first: "«", previous: "‹", next: "›", last: "»" },
    aria: {
      sortAscending: ": sortuj rosnąco",
      sortDescending: ": sortuj malejąco"
    }
  };

  document.addEventListener("DOMContentLoaded", function () {
    if (typeof DataTable === "undefined") return;
    document.querySelectorAll("table[data-datatable]").forEach(function (table) {
      new DataTable(table, {
        language: pl,
        order: [],
        autoWidth: false,
        pageLength: parseInt(table.getAttribute("data-page-length") || "25", 10)
      });
    });
  });
})();
