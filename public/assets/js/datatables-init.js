/**
 * DataTables for every <table data-datatable>, with Ukrainian texts.
 * Per-table options via HTML attributes:
 *   data-page-length="25"         rows per page
 *   <th data-orderable="false">   column without sorting (e.g. actions)
 * Rows keep the server order until the user clicks a column header.
 */
(function () {
  "use strict";

  var uk = {
    processing: "Зачекайте…",
    search: "Пошук:",
    lengthMenu: "_MENU_ на сторінці",
    info: "Записи _START_–_END_ із _TOTAL_",
    infoEmpty: "Записів немає",
    infoFiltered: "(відібрано з _MAX_)",
    zeroRecords: "Нічого не знайдено",
    emptyTable: "Записів поки немає",
    paginate: { first: "«", previous: "‹", next: "›", last: "»" },
    aria: {
      sortAscending: ": сортувати за зростанням",
      sortDescending: ": сортувати за спаданням"
    }
  };

  document.addEventListener("DOMContentLoaded", function () {
    if (typeof DataTable === "undefined") return;
    document.querySelectorAll("table[data-datatable]").forEach(function (table) {
      new DataTable(table, {
        language: uk,
        order: [],
        pageLength: parseInt(table.getAttribute("data-page-length") || "25", 10)
      });
    });
  });
})();
