/**
 * WP Desa — Frontend jQuery Components
 * Replaces Alpine.js: keuanganDesa, bantuanDesa, aduanWarga, layananSurat
 *
 * Depends on jQuery and Chart.js (for keuangan).
 */
(function ($, window, document) {
  "use strict";

  // Read restBase from global injected by PHP
  var restBase =
    (window.wpDesaFrontend && window.wpDesaFrontend.restBase) ||
    "/wp-json/wp-desa/v1";

  // ==========================================================================
  // Shared helper functions
  // ==========================================================================
  function formatCurrency(val) {
    val = parseFloat(val) || 0;
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      maximumFractionDigits: 0,
    }).format(val);
  }

  function escapeHtml(str) {
    if (!str) return "";
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function formatDate(dateString) {
    if (!dateString) return "-";
    return new Date(dateString).toLocaleDateString("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
  }

  // ==========================================================================
  // 1. initKeuanganDesa($el)
  // ==========================================================================
  function initKeuanganDesa($el) {
    var state = {
      filterYear: new Date().getFullYear(),
      years: [],
      summary: {
        totals: [],
        income_sources: [],
        expense_sources: [],
        yearly_trend: [],
      },
      items: [],
      incomeChart: null,
      expenseChart: null,
      trendChart: null,
    };

    // DOM refs — all relative to $el
    var $yearSelect = $el.find(".wp-desa-select-year");
    var $summaryCards = $el.find(".wp-desa-stat-value"); // 3 h3 elements
    var $summarySubs = $el.find(".wp-desa-stat-sub span"); // 2 span elements (budget labels)

    // Remove inert Alpine templates from tbody
    var $tbody = $el.find("tbody");
    $tbody.find("template").remove();

    // Canvas refs
    var $canvasIncome = $el.find("canvas").eq(0);
    var $canvasExpense = $el.find("canvas").eq(1);
    var $canvasTrend = $el.find("canvas").eq(2);

    function calcPct(realization, budget) {
      if (!budget || budget === 0) return 0;
      return Math.round((realization / budget) * 100);
    }

    function getSurplus() {
      var income = 0,
        expense = 0;
      var totals = state.summary.totals || [];
      var inc = totals.find(function (t) {
        return t.type === "income";
      });
      var exp = totals.find(function (t) {
        return t.type === "expense";
      });
      if (inc) income = parseFloat(inc.total_realization) || 0;
      if (exp) expense = parseFloat(exp.total_realization) || 0;
      return income - expense;
    }

    // ---- init year options ----
    function buildYears() {
      var cy = new Date().getFullYear();
      state.years = [];
      for (var i = cy; i >= cy - 5; i--) {
        state.years.push(i);
      }
      state.filterYear = cy;
      var html = "";
      $.each(state.years, function (_, y) {
        html += '<option value="' + y + '">' + y + "</option>";
      });
      $yearSelect.html(html).val(state.filterYear);
    }

    // ---- render summary cards ----
    function renderSummary() {
      var totals = state.summary.totals || [];
      var inc = totals.find(function (t) {
        return t.type === "income";
      });
      var exp = totals.find(function (t) {
        return t.type === "expense";
      });

      // Card 0: Total Pendapatan
      if ($summaryCards.length >= 1) {
        $summaryCards
          .eq(0)
          .text(formatCurrency(inc ? inc.total_realization : 0));
      }
      // Card 1: Total Belanja
      if ($summaryCards.length >= 2) {
        $summaryCards
          .eq(1)
          .text(formatCurrency(exp ? exp.total_realization : 0));
      }
      // Card 2: Sisa Lebih (SiLPA)
      if ($summaryCards.length >= 3) {
        var surplus = getSurplus();
        $summaryCards
          .eq(2)
          .text(formatCurrency(surplus))
          .css("color", surplus >= 0 ? "#1f6b3c" : "#b3262b");
      }
      // Budget subtitles
      if ($summarySubs.length >= 1) {
        $summarySubs.eq(0).text(formatCurrency(inc ? inc.total_budget : 0));
      }
      if ($summarySubs.length >= 2) {
        $summarySubs.eq(1).text(formatCurrency(exp ? exp.total_budget : 0));
      }
    }

    // ---- render charts ----
    function renderCharts() {
      // Destroy existing
      if (state.incomeChart) {
        state.incomeChart.destroy();
        state.incomeChart = null;
      }
      if (state.expenseChart) {
        state.expenseChart.destroy();
        state.expenseChart = null;
      }
      if (state.trendChart) {
        state.trendChart.destroy();
        state.trendChart = null;
      }

      if (typeof Chart === "undefined") {
        setTimeout(renderCharts, 500);
        return;
      }

      // Income pie chart
      var incomeCtx = $canvasIncome.length ? $canvasIncome[0] : null;
      if (
        incomeCtx &&
        state.summary.income_sources &&
        state.summary.income_sources.length > 0
      ) {
        state.incomeChart = new Chart(incomeCtx, {
          type: "pie",
          data: {
            labels: state.summary.income_sources.map(function (i) {
              return i.category;
            }),
            datasets: [
              {
                data: state.summary.income_sources.map(function (i) {
                  return i.total;
                }),
                backgroundColor: [
                  "#024ad8",
                  "#4361ee",
                  "#7aa5f5",
                  "#c9e0fc",
                  "#636363",
                ],
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: "bottom",
                labels: { padding: 16, usePointStyle: true, boxHeight: 6 },
              },
              tooltip: {
                callbacks: {
                  label: function (ctx) {
                    return ctx.label + ": " + formatCurrency(ctx.parsed);
                  },
                },
              },
            },
          },
        });
      }

      // Expense doughnut chart
      var expenseCtx = $canvasExpense.length ? $canvasExpense[0] : null;
      if (
        expenseCtx &&
        state.summary.expense_sources &&
        state.summary.expense_sources.length > 0
      ) {
        state.expenseChart = new Chart(expenseCtx, {
          type: "doughnut",
          data: {
            labels: state.summary.expense_sources.map(function (i) {
              return i.category;
            }),
            datasets: [
              {
                data: state.summary.expense_sources.map(function (i) {
                  return i.total;
                }),
                backgroundColor: [
                  "#b3262b",
                  "#ff5050",
                  "#e0734a",
                  "#9a5b1e",
                  "#636363",
                ],
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: "bottom",
                labels: { padding: 16, usePointStyle: true, boxHeight: 6 },
              },
              tooltip: {
                callbacks: {
                  label: function (ctx) {
                    return ctx.label + ": " + formatCurrency(ctx.parsed);
                  },
                },
              },
            },
          },
        });
      }

      // Trend line chart
      var trendCtx = $canvasTrend.length ? $canvasTrend[0] : null;
      if (
        trendCtx &&
        state.summary.yearly_trend &&
        state.summary.yearly_trend.length > 0
      ) {
        var years = [];
        var seen = {};
        $.each(state.summary.yearly_trend, function (_, item) {
          if (!seen[item.year]) {
            seen[item.year] = true;
            years.push(item.year);
          }
        });
        years.sort();

        var incomeMap = {};
        var expenseMap = {};
        $.each(state.summary.yearly_trend, function (_, item) {
          if (item.type === "income")
            incomeMap[item.year] = item.total_realization;
          else if (item.type === "expense")
            expenseMap[item.year] = item.total_realization;
        });

        var incomeData = years.map(function (y) {
          return incomeMap[y] || 0;
        });
        var expenseData = years.map(function (y) {
          return expenseMap[y] || 0;
        });

        state.trendChart = new Chart(trendCtx, {
          type: "line",
          data: {
            labels: years,
            datasets: [
              {
                label: "Pendapatan",
                data: incomeData,
                borderColor: "#1f6b3c",
                backgroundColor: "rgba(22, 163, 74, 0.1)",
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointRadius: 3,
              },
              {
                label: "Belanja",
                data: expenseData,
                borderColor: "#b3262b",
                backgroundColor: "rgba(220, 38, 38, 0.08)",
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointRadius: 3,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            stacked: false,
            plugins: {
              legend: { position: "bottom" },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    var value = context.parsed.y || 0;
                    return context.dataset.label + ": " + formatCurrency(value);
                  },
                },
              },
            },
            scales: {
              y: {
                ticks: {
                  callback: function (value) {
                    return formatCurrency(value);
                  },
                },
              },
            },
          },
        });
      }
    }

    // ---- render table ----
    function renderTable() {
      var items = state.items || [];
      var rows = "";

      if (items.length === 0) {
        rows =
          '<tr><td colspan="4" class="wp-desa-empty-state">Belum ada data keuangan untuk tahun ini.</td></tr>';
      } else {
        $.each(items, function (_, item) {
          var pct = calcPct(item.realization_amount, item.budget_amount);
          var badgeBg, badgeColor;
          if (pct > 90) {
            badgeBg = "#e6f4ea";
            badgeColor = "#1f6b3c";
          } else if (pct > 50) {
            badgeBg = "#fef3e4";
            badgeColor = "#9a5b1e";
          } else {
            badgeBg = "#fce8e6";
            badgeColor = "#b3262b";
          }

          rows += "<tr>";
          rows +=
            '<td><div class="wp-desa-row-title">' +
            escapeHtml(item.category || "") +
            '</div><div class="wp-desa-row-subtitle">' +
            escapeHtml(item.description || "") +
            "</div></td>";
          rows +=
            '<td class="wp-desa-cell-number">' +
            formatCurrency(item.budget_amount) +
            "</td>";
          rows +=
            '<td class="wp-desa-cell-number wp-desa-cell-number-strong">' +
            formatCurrency(item.realization_amount) +
            "</td>";
          rows +=
            '<td class="wp-desa-cell-percentage"><div class="wp-desa-percentage" style="background:' +
            badgeBg +
            ";color:" +
            badgeColor +
            ';">' +
            pct +
            "%</div></td>";
          rows += "</tr>";
        });
      }
      $tbody.html(rows);
    }

    // ---- fetch ----
    function fetchSummary() {
      $.getJSON(_summaryUrl(), function (data) {
        state.summary = data || {
          totals: [],
          income_sources: [],
          expense_sources: [],
          yearly_trend: [],
        };
        renderSummary();
        renderCharts();
      });
    }

    function fetchData() {
      $.getJSON(_dataUrl(), function (data) {
        state.items = Array.isArray(data && data.data)
          ? data.data
          : Array.isArray(data)
            ? data
            : [];
        renderTable();
      });
    }

    function _summaryUrl() {
      return restBase + "/finances/summary?year=" + state.filterYear;
    }

    function _dataUrl() {
      return restBase + "/finances?year=" + state.filterYear;
    }

    // ---- events ----
    $yearSelect.on("change", function () {
      state.filterYear = parseInt($(this).val(), 10);
      fetchSummary();
      fetchData();
    });

    // ---- boot ----
    buildYears();
    fetchSummary();
    fetchData();
  }

  // ==========================================================================
  // 2. initBantuanDesa($el)
  // ==========================================================================
  function initBantuanDesa($el) {
    var state = {
      programs: [],
      activeProgramId: null,
      recipients: [],
    };

    // The grid div that holds program cards (immediately after the h2)
    var $grid = $el.children("div").first();

    // ---- helpers ----
    function formatStatus(status) {
      var map = {
        pending: "Menunggu",
        approved: "Disetujui",
        rejected: "Ditolak",
        distributed: "Disalurkan",
      };
      return map[status] || status;
    }

    // ---- render programs ----
    function renderPrograms() {
      $grid.empty();

      if (!state.programs.length) {
        $grid.html(
          '<div style="text-align:center;padding:60px 20px;background:var(--cloud);border-radius:var(--rounded-xl);border:1px solid var(--fog);color:var(--graphite);">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:10px;"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>' +
            '<p style="margin:0;font-size:1.1em;">Belum ada program bantuan aktif saat ini.</p></div>',
        );
        return;
      }

      $.each(state.programs, function (_, p) {
        var isActive = state.activeProgramId === p.id;
        var card =
          '<div class="wp-desa-stat-card" style="text-align:left;padding:0;overflow:hidden;border:1px solid var(--fog);margin-bottom:0;">' +
          '<div style="padding:var(--sp-xl)">' +
          '<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:var(--sp-lg);flex-wrap:wrap;">' +
          '<div style="flex:1;min-width:250px;">' +
          '<div style="display:flex;align-items:center;gap:var(--sp-xs);margin-bottom:var(--sp-xs);">' +
          '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#024ad8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>' +
          '<h3 style="margin:0;font-family:var(--font-display);font-size:20px;font-weight:500;line-height:1.0;color:var(--ink);">' +
          escapeHtml(p.name) +
          "</h3>" +
          "</div>" +
          '<p style="margin:0 0 var(--sp-sm) 0;font-size:14px;color:var(--graphite);line-height:1.5;">' +
          escapeHtml(p.description) +
          "</p>" +
          '<div style="display:flex;gap:var(--sp-xs);flex-wrap:wrap;">' +
          '<span style="background:var(--primary-soft);color:var(--primary-deep);padding:4px 12px;border-radius:var(--rounded-pill);font-size:12px;font-weight:500;display:inline-flex;align-items:center;gap:4px;">' +
          '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>' +
          "<span>" +
          escapeHtml(p.origin) +
          "</span></span>" +
          '<span style="background:var(--cloud);color:var(--ink);padding:4px 12px;border-radius:var(--rounded-pill);font-size:12px;font-weight:500;display:inline-flex;align-items:center;gap:4px;">' +
          '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>' +
          "<span>" +
          escapeHtml(p.year) +
          "</span></span>" +
          "</div>" +
          "</div>" +
          '<div style="text-align:right;min-width:150px;display:flex;flex-direction:column;align-items:flex-end;">' +
          '<div style="font-weight:500;font-size:24px;line-height:1.17;color:var(--success);">' +
          formatCurrency(p.amount_per_recipient) +
          "</div>" +
          '<div style="font-size:14px;color:var(--graphite);margin-top:var(--sp-xxs);margin-bottom:var(--sp-sm);">Kuota: ' +
          (p.quota || 0) +
          " Penerima</div>" +
          '<button class="wp-desa-btn ' +
          (isActive ? "wp-desa-btn-secondary" : "wp-desa-btn-primary") +
          ' btn-view-recipients" data-program-id="' +
          p.id +
          '" style="font-size:14px;padding:8px 16px;">' +
          "<span>" +
          (isActive ? "Tutup Daftar" : "Lihat Penerima") +
          "</span>" +
          "</button>" +
          "</div>" +
          "</div>" +
          "</div>";

        // Recipients panel
        if (isActive) {
          card +=
            '<div class="recipients-panel" style="border-top:1px solid var(--fog);background:var(--cloud);">' +
            '<div style="padding:var(--sp-lg);">' +
            '<h4 style="margin:0 0 var(--sp-sm) 0;font-family:var(--font-display);font-size:20px;font-weight:500;color:var(--ink);">Daftar Penerima Bantuan</h4>' +
            '<div style="overflow-x:auto;background:var(--canvas);border-radius:var(--rounded-lg);border:1px solid var(--fog);">' +
            '<table style="width:100%;border-collapse:collapse;font-size:14px;">' +
            '<thead><tr style="background:var(--cloud);color:var(--graphite);text-transform:uppercase;font-size:12px;font-weight:600;letter-spacing:0.7px;">' +
            '<th style="text-align:left;padding:12px 15px;">Nama</th>' +
            '<th style="text-align:left;padding:12px 15px;">Alamat</th>' +
            '<th style="text-align:center;padding:12px 15px;">Status</th>' +
            "</tr></thead>" +
            "<tbody>" +
            renderRecipientRows() +
            "</tbody>" +
            "</table>" +
            "</div></div></div>";
        }

        card += "</div>";
        $grid.append(card);
      });
    }

    function renderRecipientRows() {
      if (!state.recipients.length) {
        return '<tr><td colspan="3" style="text-align:center;padding:var(--sp-xl);color:var(--graphite);">Belum ada data penerima yang ditampilkan.</td></tr>';
      }
      var rows = "";
      $.each(state.recipients, function (idx, r) {
        var bg = idx % 2 === 0 ? "var(--canvas)" : "var(--cloud)";
        rows +=
          '<tr style="background:' +
          bg +
          ';border-bottom:1px solid var(--fog);">';
        rows +=
          '<td style="padding:12px 15px;font-weight:500;color:var(--ink);">' +
          escapeHtml(r.nama_lengkap) +
          "</td>";
        rows +=
          '<td style="padding:12px 15px;color:var(--graphite);">' +
          escapeHtml(r.alamat) +
          "</td>";
        rows +=
          '<td style="text-align:center;padding:12px 15px;"><span class="status-badge status-' +
          (r.status || "") +
          '">' +
          formatStatus(r.status) +
          "</span></td>";
        rows += "</tr>";
      });
      return rows;
    }

    // ---- view recipients ----
    function viewRecipients(program) {
      if (state.activeProgramId === program.id) {
        state.activeProgramId = null;
        state.recipients = [];
        renderPrograms();
        return;
      }
      state.activeProgramId = program.id;
      state.recipients = [];

      $.getJSON(
        restBase + "/aid-programs/" + program.id + "/recipients",
        function (data) {
          state.recipients = Array.isArray(data) ? data : [];
          renderPrograms();
        },
      );
    }

    // ---- events ----
    $grid.on("click", ".btn-view-recipients", function () {
      var pid = parseInt($(this).data("program-id"), 10);
      var program = state.programs.find(function (p) {
        return p.id === pid;
      });
      if (program) viewRecipients(program);
    });

    // ---- fetch ----
    function fetchPrograms() {
      $.getJSON(restBase + "/aid-programs", function (data) {
        state.programs = Array.isArray(data) ? data : [];
        renderPrograms();
      });
    }

    // ---- boot ----
    fetchPrograms();
  }

  // ==========================================================================
  // 3. initAduanWarga($el)
  // ==========================================================================
  function initAduanWarga($el) {
    var state = {
      tab: "form",
      form: {
        reporter_name: "",
        reporter_contact: "",
        category: "",
        subject: "",
        description: "",
        photo: null,
      },
      message: { type: "", content: "" },
      trackingCode: null,
      submitting: false,
      trackCode: "",
      trackResult: null,
      trackError: null,
      tracking: false,
    };

    // DOM refs — all relative to $el
    var $tabs = $el.find(".wp-desa-tab-btn, .wp-desa-tab-pill");
    var $content = $el.find(".wp-desa-content");
    var $formPanel = $content.children(".wp-desa-tab-panel").eq(0); // tab === 'form'
    var $trackPanel = $content.children(".wp-desa-tab-panel").eq(1); // tab === 'track'

    // Form elements — use name attribute selectors (x-model is just a marker for the JS)
    var $form = $formPanel.find("form");
    var $reporterName = $form.find('[x-model="form.reporter_name"]');
    var $reporterContact = $form.find('[x-model="form.reporter_contact"]');
    var $category = $form.find('[x-model="form.category"]');
    var $subject = $form.find('[x-model="form.subject"]');
    var $description = $form.find('[x-model="form.description"]');
    var $photoInput = $form.find('input[type="file"]');
    var $submitBtn = $form.find('button[type="submit"]');

    // Message / tracking code displays
    var $messageDiv = $formPanel.find('[x-show="message.content"]');
    var $trackingCodeDiv = $formPanel.find('[x-show="trackingCode"]');

    // Tracking panel elements
    var $trackCodeInput = $trackPanel.find('[x-model="trackCode"]');
    var $trackBtn = $trackPanel.find('button[type="submit"]');
    var $trackResultDiv = $trackPanel.find('[x-show="trackResult"]');
    var $trackErrorDiv = $trackPanel.find('[x-show="trackError"]');

    // ---- helpers ----
    function formatStatus(status) {
      var map = {
        pending: "Menunggu",
        in_progress: "Diproses",
        resolved: "Selesai",
        rejected: "Ditolak",
      };
      return map[status] || status;
    }

    // ---- tab switching ----
    function switchTab(tab) {
      state.tab = tab;
      $tabs.removeClass("active");
      $tabs.each(function () {
        var $btn = $(this);
        if (tab === "form" && $btn.text().indexOf("Buat") >= 0)
          $btn.addClass("active");
        if (tab === "track" && $btn.text().indexOf("Cek") >= 0)
          $btn.addClass("active");
      });

      if (tab === "form") {
        $formPanel.show();
        $trackPanel.hide();
      } else {
        $formPanel.hide();
        $trackPanel.show();
      }
    }

    // ---- submit complaint ----
    function submitComplaint(e) {
      e.preventDefault();
      state.submitting = true;
      state.message = { type: "", content: "" };
      state.trackingCode = null;
      updateSubmitUI();

      var formData = new FormData();
      formData.append("reporter_name", $reporterName.val() || "");
      formData.append("reporter_contact", $reporterContact.val() || "");
      formData.append("category", $category.val() || "");
      formData.append("subject", $subject.val() || "");
      formData.append("description", $description.val() || "");
      var photoFile =
        $photoInput[0] && $photoInput[0].files && $photoInput[0].files[0];
      if (photoFile) formData.append("photo", photoFile);

      $.ajax({
        url: restBase + "/complaints/submit",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (data) {
          state.submitting = false;
          if (data.success) {
            state.message = { type: "success", content: data.message };
            state.trackingCode = data.tracking_code;
            // Reset form
            $reporterName.val("");
            $reporterContact.val("");
            $category.val("");
            $subject.val("");
            $description.val("");
            $photoInput.val("");
          } else {
            state.message = {
              type: "error",
              content: data.message || "Terjadi kesalahan.",
            };
          }
          updateSubmitUI();
        },
        error: function () {
          state.submitting = false;
          state.message = {
            type: "error",
            content: "Gagal menghubungi server.",
          };
          updateSubmitUI();
        },
      });
    }

    function updateSubmitUI() {
      // Message box
      if (state.message.content) {
        var bg = state.message.type === "success" ? "#e6f4ea" : "#fce8e6";
        var color = state.message.type === "success" ? "#1f6b3c" : "#b3262b";
        var border = state.message.type === "success" ? "#c3e6cb" : "#fecaca";

        var msgHtml =
          '<span style="font-weight:500;">' +
          escapeHtml(state.message.content) +
          "</span>";

        if (state.trackingCode) {
          msgHtml +=
            '<div style="margin-top:15px;background:var(--canvas);padding:15px;border-radius:8px;border:1px dashed #1f6b3c;">' +
            '<div style="font-size:0.9em;margin-bottom:5px;color:#1f6b3c;">Kode Tracking Anda:</div>' +
            '<div class="wp-desa-tracking-code" style="font-family:monospace;font-size:1.5em;font-weight:700;color:#1a1a1a;letter-spacing:1px;">' +
            escapeHtml(state.trackingCode) +
            "</div>" +
            '<p class="wp-desa-helper" style="margin:5px 0 0 0;">Simpan kode ini untuk mengecek status laporan.</p>' +
            "</div>";
        }

        $messageDiv
          .html(msgHtml)
          .css({ background: bg, color: color, border: "1px solid " + border })
          .show();
      } else {
        $messageDiv.hide().empty();
      }

      // Tracking code box
      if (state.trackingCode) {
        $trackingCodeDiv.show();
      } else {
        $trackingCodeDiv.hide();
      }

      // Submit button state
      var $submittingSpan = $submitBtn.find("span").eq(1);
      var $normalSpan = $submitBtn.find("span").eq(0);
      if (state.submitting) {
        $submitBtn.prop("disabled", true);
        $normalSpan.hide();
        $submittingSpan.show();
      } else {
        $submitBtn.prop("disabled", false);
        $normalSpan.show();
        $submittingSpan.hide();
      }
    }

    // ---- check status ----
    function checkStatus(e) {
      e.preventDefault();
      state.tracking = true;
      state.trackResult = null;
      state.trackError = null;
      updateTrackUI();

      $.getJSON(
        restBase +
          "/complaints/track?code=" +
          encodeURIComponent($trackCodeInput.val()),
        function (data) {
          state.tracking = false;
          if (data && data.id) {
            state.trackResult = data;
          } else {
            state.trackError =
              (data && data.message) || "Data tidak ditemukan.";
          }
          updateTrackUI();
        },
      ).fail(function () {
        state.tracking = false;
        state.trackError = "Gagal menghubungi server.";
        updateTrackUI();
      });
    }

    function updateTrackUI() {
      // Track button
      var $normalText = $trackBtn.find("span").eq(0);
      var $loadingIcon = $trackBtn.find("span").eq(1);
      if (state.tracking) {
        $trackBtn.prop("disabled", true);
        $normalText.hide();
        $loadingIcon.show();
      } else {
        $trackBtn.prop("disabled", false);
        $normalText.show();
        $loadingIcon.hide();
      }

      // Result card
      if (state.trackResult) {
        var r = state.trackResult;
        var statusStyleMap = {
          pending: "background:#fef3c7;color:#92400e;",
          in_progress: "background:#dbeafe;color:#1e40af;",
          resolved: "background:#e6f4ea;color:#1f6b3c;",
          rejected: "background:#fce8e6;color:#b3262b;",
        };
        var statusStyle = statusStyleMap[r.status] || "";

        $trackResultDiv.find(".wp-desa-track-code-label").text(r.code || "");
        $trackResultDiv.find(".wp-desa-track-subject").text(r.subject || "");
        $trackResultDiv.find(".wp-desa-track-category").text(r.category || "");
        $trackResultDiv
          .find(".wp-desa-track-date")
          .text(formatDate(r.created_at));
        $trackResultDiv
          .find(".wp-desa-track-status")
          .text(formatStatus(r.status))
          .attr(
            "style",
            "padding:4px 12px;border-radius:20px;font-size:0.85em;font-weight:600;" +
              statusStyle,
          );

        var $response = $trackResultDiv.find(".wp-desa-track-response");
        if (r.response) {
          $response.find("p").text(r.response);
          $response.show();
        } else {
          $response.hide();
        }

        $trackResultDiv.show();
        $trackErrorDiv.hide().empty();
      } else {
        $trackResultDiv.hide().empty();
      }

      // Error
      if (state.trackError) {
        $trackErrorDiv.text(state.trackError).show();
      } else {
        $trackErrorDiv.hide().empty();
      }
    }

    // ---- events ----
    $tabs.on("click", function () {
      var text = $(this).text().trim();
      if (text.indexOf("Buat") >= 0) switchTab("form");
      else if (text.indexOf("Cek") >= 0) switchTab("track");
    });

    $form.on("submit", submitComplaint);
    $trackPanel.find("form").on("submit", checkStatus);

    // ---- boot ----
    $formPanel.hide();
    $trackPanel.hide();
    $messageDiv.hide();
    $trackingCodeDiv.hide();

    // Hide submit button loading state initially
    $submitBtn.find("span").eq(1).hide();

    // Hide tracking result/error initially
    $trackResultDiv.hide();
    $trackErrorDiv.hide();

    // Show form tab
    switchTab("form");
  }

  // ==========================================================================
  // 4. initLayananSurat($el)
  // ==========================================================================
  function initLayananSurat($el) {
    var state = {
      tab: "request",
      types: [],
      form: { nik: "", name: "", phone: "", letter_type_id: "", details: "" },
      message: { type: "", content: "" },
      trackingCode: null,
      submitting: false,
      trackCode: "",
      trackResult: null,
      trackError: null,
      tracking: false,
    };

    // DOM refs — all relative to $el
    var $tabs = $el.find(".wp-desa-tab-btn");
    var $requestPanel = $el.find("[x-show=\"tab === 'request'\"]");
    var $trackingPanel = $el.find("[x-show=\"tab === 'tracking'\"]");

    // Request form elements — use x-model attribute selectors
    var $form = $requestPanel.find("form");
    var $nik = $requestPanel.find('[x-model="form.nik"]');
    var $name = $requestPanel.find('[x-model="form.name"]');
    var $phone = $requestPanel.find('[x-model="form.phone"]');
    var $letterTypeSelect = $requestPanel.find(
      '[x-model="form.letter_type_id"]',
    );
    var $details = $requestPanel.find('[x-model="form.details"]');
    var $submitBtn = $requestPanel.find('button[type="submit"]');
    var $typeDescription = $requestPanel.find(".wp-desa-layanan-type-desc");

    // Message / tracking displays
    var $msgContent = $requestPanel.find('[x-show="message.content"]');
    var $trackingBox = $requestPanel.find('[x-show="trackingCode"]');

    // Tracking panel elements
    var $trackCodeInput = $trackingPanel.find('[x-model="trackCode"]');
    var $trackBtn = $trackingPanel.find("button");
    var $trackResultDiv = $trackingPanel.find('[x-show="trackResult"]');
    var $trackErrorDiv = $trackingPanel.find('[x-show="trackError"]');

    // ---- helpers ----
    function formatStatus(status) {
      var map = {
        pending: "Menunggu",
        processed: "Diproses",
        ready: "Siap Diambil",
        completed: "Selesai",
        rejected: "Ditolak",
      };
      return map[status] || status;
    }

    // ---- tab switching ----
    function switchTab(tab) {
      state.tab = tab;
      $tabs.removeClass("active");
      $tabs.each(function () {
        var $btn = $(this);
        if (tab === "request" && $btn.text().indexOf("Buat") >= 0)
          $btn.addClass("active");
        if (tab === "tracking" && $btn.text().indexOf("Cek") >= 0)
          $btn.addClass("active");
      });

      if (tab === "request") {
        $requestPanel.show();
        $trackingPanel.hide();
      } else {
        $requestPanel.hide();
        $trackingPanel.show();
      }
    }

    // ---- update type description ----
    function updateTypeDescription() {
      var selectedId = $letterTypeSelect.val();
      var found = state.types.find(function (t) {
        return String(t.id) === String(selectedId);
      });
      $typeDescription.text(found ? found.description || "" : "");
    }

    // ---- submit request ----
    function submitRequest(e) {
      e.preventDefault();
      state.submitting = true;
      state.message = { type: "", content: "" };
      state.trackingCode = null;
      updateRequestUI();

      $.ajax({
        url: restBase + "/letters/request",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
          nik: $nik.val() || "",
          name: $name.val() || "",
          phone: $phone.val() || "",
          letter_type_id: $letterTypeSelect.val() || "",
          details: $details.val() || "",
        }),
        success: function (data) {
          state.submitting = false;
          if (data.success) {
            state.message = { type: "success", content: data.message };
            state.trackingCode = data.tracking_code;
            // Reset form
            $nik.val("");
            $name.val("");
            $phone.val("");
            $letterTypeSelect.val("");
            $details.val("");
            $typeDescription.text("");
          } else {
            state.message = {
              type: "error",
              content: data.message || "Terjadi kesalahan.",
            };
          }
          updateRequestUI();
        },
        error: function () {
          state.submitting = false;
          state.message = {
            type: "error",
            content: "Gagal menghubungi server.",
          };
          updateRequestUI();
        },
      });
    }

    function updateRequestUI() {
      // Message
      if (state.message.content) {
        var isSuccess = state.message.type === "success";
        $msgContent
          .text(state.message.content)
          .removeClass("wp-desa-message-success wp-desa-message-error")
          .addClass(isSuccess ? "wp-desa-message-success" : "wp-desa-message-error")
          .show();
      } else {
        $msgContent.hide().text("").removeClass("wp-desa-message-success wp-desa-message-error");
      }

      // Tracking code box
      if (state.trackingCode) {
        $trackingBox.show();
      } else {
        $trackingBox.hide();
      }

      // Submit button
      var $normalSpan = $submitBtn.find("span").eq(0);
      var $loadingSpan = $submitBtn.find("span").eq(1);
      if (state.submitting) {
        $submitBtn.prop("disabled", true);
        $normalSpan.hide();
        $loadingSpan.show();
      } else {
        $submitBtn.prop("disabled", false);
        $normalSpan.show();
        $loadingSpan.hide();
      }
    }

    // ---- check status ----
    function checkStatus(e) {
      if (e) e.preventDefault();
      state.tracking = true;
      state.trackResult = null;
      state.trackError = null;
      updateTrackUI();

      $.getJSON(
        restBase +
          "/letters/track?code=" +
          encodeURIComponent($trackCodeInput.val()),
        function (data) {
          state.tracking = false;
          if (data && data.id) {
            state.trackResult = data;
          } else {
            state.trackError =
              (data && data.message) || "Data tidak ditemukan.";
          }
          updateTrackUI();
        },
      ).fail(function () {
        state.tracking = false;
        state.trackError = "Gagal menghubungi server.";
        updateTrackUI();
      });
    }

    function updateTrackUI() {
      // Button
      var $normalSpan = $trackBtn.find("span").eq(0);
      var $loadingSpan = $trackBtn.find("span").eq(1);
      if (state.tracking) {
        $trackBtn.prop("disabled", true);
        $normalSpan.hide();
        $loadingSpan.show();
      } else {
        $trackBtn.prop("disabled", false);
        $normalSpan.show();
        $loadingSpan.hide();
      }

      // Result
      if (state.trackResult) {
        var r = state.trackResult;
        var statusStyleMap = {
          pending: "background:#fef3c7;color:#92400e;",
          processed: "background:#dbeafe;color:#1e40af;",
          ready: "background:#e6f4ea;color:#1f6b3c;",
          completed: "background:#d1fae5;color:#065f46;",
          rejected: "background:#fce8e6;color:#b3262b;",
        };
        var statusStyle = statusStyleMap[r.status] || "";

        $trackResultDiv.find(".wp-desa-layanan-track-name").text(r.name || "");
        $trackResultDiv
          .find(".wp-desa-layanan-track-date")
          .text(formatDate(r.created_at));
        $trackResultDiv
          .find(".wp-desa-layanan-track-status")
          .text(formatStatus(r.status))
          .attr("style", statusStyle);

        $trackResultDiv.show();
        $trackErrorDiv.hide().empty();
      } else {
        $trackResultDiv.hide().empty();
      }

      if (state.trackError) {
        $trackErrorDiv.text(state.trackError).show();
      } else {
        $trackErrorDiv.hide().empty();
      }
    }

    // ---- fetch types ----
    function fetchTypes() {
      $.getJSON(restBase + "/letters/types", function (data) {
        state.types = Array.isArray(data) ? data : [];
        var opts = '<option value="">Pilih Jenis Surat</option>';
        $.each(state.types, function (_, t) {
          opts +=
            '<option value="' + t.id + '">' + escapeHtml(t.name) + "</option>";
        });
        $letterTypeSelect.html(opts);
      });
    }

    // ---- events ----
    $tabs.on("click", function () {
      var text = $(this).text().trim();
      if (text.indexOf("Buat") >= 0) switchTab("request");
      else if (text.indexOf("Cek") >= 0) switchTab("tracking");
    });

    $form.on("submit", submitRequest);
    $letterTypeSelect.on("change", updateTypeDescription);
    $trackingPanel.find("form, .wp-desa-form-group").on("submit", checkStatus);
    $trackBtn.on("click", checkStatus);

    // ---- boot ----
    $requestPanel.hide();
    $trackingPanel.hide();
    $msgContent.hide();
    $trackingBox.hide();
    $trackResultDiv.hide();
    $trackErrorDiv.hide();
    $submitBtn.find("span").eq(1).hide(); // loading
    $trackBtn.find("span").eq(1).hide(); // loading

    fetchTypes();
    switchTab("request");
  }

  // ==========================================================================
  // DOM Ready — init all components using data-wp-desa attribute (multi-instance safe)
  // ==========================================================================
  $(function () {
    $('[data-wp-desa="keuangan"]').each(function () {
      initKeuanganDesa($(this));
    });
    $('[data-wp-desa="bantuan"]').each(function () {
      initBantuanDesa($(this));
    });
    $('[data-wp-desa="aduan"]').each(function () {
      initAduanWarga($(this));
    });
    $('[data-wp-desa="layanan"]').each(function () {
      initLayananSurat($(this));
    });
  });
})(jQuery, window, document);
