/**
 * WP Desa — Admin jQuery Components
 * Handles: dashboard (charts/stats), residents (delete/export/import/seed),
 * statistik (charts), finances (dashboard summary + delete), settings (toast).
 *
 * Depends on jQuery and Chart.js (dashboard, statistik, keuangan).
 */
(function ($, window, document) {
  "use strict";

  // Read nonce from global injected by PHP
  var wpNonce = (window.wpDesaSettings && window.wpDesaSettings.nonce) || "";
  var wpApiUrl = (window.wpDesaSettings && window.wpDesaSettings.apiUrl) || "";

  // ===== Helpers =====
  function formatRupiah(val) {
    val = parseFloat(val) || 0;
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(val);
  }
  function formatNumber(val) {
    if (val === null || val === undefined) return "0";
    return new Intl.NumberFormat("id-ID").format(val);
  }
  function formatDate(dateString) {
    if (!dateString) return "-";
    var d = new Date(dateString);
    return d.toLocaleDateString("id-ID", {
      day: "numeric",
      month: "short",
      year: "numeric",
    });
  }
  function escapeHtml(str) {
    if (!str) return "";
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }
  function showToast(msg, type) {
    type = type || "success";
    var $t = $(".wp-desa-toast").first();
    if (!$t.length) return;
    $t.find("span").last().text(msg);
    $t.removeClass("error success").addClass(type);
    $t.fadeIn(200);
    clearTimeout($t.data("_timeout"));
    $t.data(
      "_timeout",
      setTimeout(function () {
        $t.fadeOut(200);
      }, 3000),
    );
  }

  // ====================================================================
  // Dashboard
  // ====================================================================
  function initDashboard() {
    var $wrap = $(".wp-desa-hero").closest(".wrap");
    if (!$wrap.length) return;

    var stats = {};
    var charts = {};

    function fetchStats() {
      $.ajax({
        url: "/wp-json/wp-desa/v1/dashboard/stats",
        headers: { "X-WP-Nonce": wpNonce },
        success: function (data) {
          stats = data;
          renderStats();
          initCharts();
        },
      });
    }

    function renderStats() {
      // Hero metrics
      var $metrics = $wrap.find(".wp-desa-hero__value");
      if ($metrics.length >= 1) $metrics.eq(0).text(stats.total_residents || 0);
      if ($metrics.length >= 2) $metrics.eq(1).text(stats.total_letters || 0);
      if ($metrics.length >= 3) $metrics.eq(2).text(stats.pending_letters || 0);
      if ($metrics.length >= 4)
        $metrics.eq(3).text(stats.total_complaints || 0);

      // Stat cards
      var $statValues = $wrap.find(".wp-desa-stat-value");
      $statValues.eq(0).text(stats.total_potensi || 0); // Potensi
      $statValues.eq(1).text(stats.total_umkm || 0); // UMKM
      // Finance year
      var $yearSpan = $wrap.find(".wp-desa-stat-title span");
      if ($yearSpan.length)
        $yearSpan.text((stats.finance_stats && stats.finance_stats.year) || "");
      // Finance bars
      var $green = $wrap.find(".wp-desa-text-green");
      var $red = $wrap.find(".wp-desa-text-red");
      if ($green.length)
        $green.text(
          formatRupiah(
            (stats.finance_stats && stats.finance_stats.income) || 0,
          ),
        );
      if ($red.length)
        $red.text(
          formatRupiah(
            (stats.finance_stats && stats.finance_stats.expense) || 0,
          ),
        );

      // Expense bar width
      var pct = 0;
      if (stats.finance_stats && stats.finance_stats.income > 0) {
        pct = Math.min(
          (stats.finance_stats.expense / stats.finance_stats.income) * 100,
          100,
        );
      }
      $wrap.find(".wp-desa-bar-expense").css("width", pct + "%");

      // Letter status rows
      var $statusList = $wrap.find(".wp-desa-stat-list--status");
      var ls = stats.letter_stats || [];
      if (ls.length > 0) {
        var rows = "";
        $.each(ls, function (_, row) {
          var dotClass = "";
          if (row.label === "pending") dotClass = "is-pending";
          else if (row.label === "processed") dotClass = "is-progress";
          else if (row.label === "completed") dotClass = "is-resolved";
          else if (row.label === "rejected") dotClass = "is-rejected";
          var labelMap = {
            pending: "Pending",
            processed: "Diproses",
            completed: "Selesai",
            rejected: "Ditolak",
          };
          rows +=
            '<div class="wp-desa-status-row">' +
            '<span class="wp-desa-list-dot ' +
            dotClass +
            '"></span>' +
            '<span class="wp-desa-status-name">' +
            (labelMap[row.label] || row.label) +
            "</span>" +
            '<span class="wp-desa-status-count">' +
            row.count +
            "</span></div>";
        });
        $statusList.html(rows);
      } else {
        $statusList.html(
          '<p class="wp-desa-empty-state">Belum ada permohonan surat.</p>',
        );
      }

      // Recent complaints
      var $complaints = $wrap
        .find(".wp-desa-stat-list")
        .not(".wp-desa-stat-list--status");
      var rc = stats.recent_complaints || [];
      if (rc.length > 0) {
        var crow = "";
        $.each(rc, function (_, c) {
          var dotClass = "";
          if (c.status === "pending") dotClass = "is-pending";
          else if (c.status === "in_progress") dotClass = "is-progress";
          else if (c.status === "resolved") dotClass = "is-resolved";
          else if (c.status === "rejected") dotClass = "is-rejected";
          var badgeClass = "";
          if (c.status === "pending") badgeClass = "wp-desa-badge-pending";
          else if (c.status === "in_progress")
            badgeClass = "wp-desa-badge-warning";
          else if (c.status === "resolved")
            badgeClass = "wp-desa-badge-success";
          else if (c.status === "rejected") badgeClass = "wp-desa-badge-danger";
          crow +=
            '<div class="wp-desa-list-row">' +
            '<span class="wp-desa-list-dot ' +
            dotClass +
            '"></span>' +
            '<div class="wp-desa-list-main"><p class="wp-desa-list-title">' +
            escapeHtml(c.subject) +
            "</p>" +
            '<p class="wp-desa-row-subtitle">' +
            formatDate(c.created_at) +
            "</p></div>" +
            '<span class="wp-desa-badge ' +
            badgeClass +
            '">' +
            c.status.replace("_", " ") +
            "</span></div>";
        });
        $complaints.html(crow);
      } else {
        $complaints.html(
          '<p class="wp-desa-empty-state">Belum ada aspirasi masuk.</p>',
        );
      }
    }

    function initCharts() {
      $.each(["gender", "marital", "job", "letter", "aid"], function (_, k) {
        if (charts[k]) {
          charts[k].destroy();
          charts[k] = null;
        }
      });
      if (typeof Chart === "undefined") {
        setTimeout(initCharts, 500);
        return;
      }

      if (stats.gender_stats) {
        charts.gender = createChart(
          "genderChart",
          "doughnut",
          stats.gender_stats.map(function (i) {
            return i.label;
          }),
          stats.gender_stats.map(function (i) {
            return i.count;
          }),
          ["#024ad8", "#ff5050"],
        );
      }
      if (stats.marital_stats) {
        charts.marital = createChart(
          "maritalChart",
          "pie",
          stats.marital_stats.map(function (i) {
            return i.label;
          }),
          stats.marital_stats.map(function (i) {
            return i.count;
          }),
          ["#1a1a1a", "#ff5050", "#024ad8", "#636363"],
        );
      }
      if (stats.job_stats) {
        charts.job = createChart(
          "jobChart",
          "bar",
          stats.job_stats.map(function (i) {
            return i.label;
          }),
          stats.job_stats.map(function (i) {
            return i.count;
          }),
          ["#024ad8"],
        );
      }
      if (stats.letter_stats) {
        var lc = {
          pending: "#ff5050",
          processed: "#024ad8",
          completed: "#1f6b3c",
          rejected: "#b3262b",
        };
        charts.letter = createChart(
          "letterChart",
          "doughnut",
          stats.letter_stats.map(function (i) {
            return i.label.charAt(0).toUpperCase() + i.label.slice(1);
          }),
          stats.letter_stats.map(function (i) {
            return i.count;
          }),
          stats.letter_stats.map(function (i) {
            return lc[i.label] || "#93939f";
          }),
        );
      }
      if (stats.program_stats) {
        charts.aid = createDualBarChart(
          "aidChart",
          stats.program_stats.map(function (i) {
            return i.name;
          }),
          stats.program_stats.map(function (i) {
            return i.quota;
          }),
          stats.program_stats.map(function (i) {
            return i.distributed;
          }),
        );
      }
    }

    function createChart(id, type, labels, data, colors) {
      var ctx = document.getElementById(id);
      if (!ctx) return null;
      return new Chart(ctx, {
        type: type,
        data: {
          labels: labels,
          datasets: [
            {
              label: "Jumlah",
              data: data,
              backgroundColor: colors,
              borderWidth: 0,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "bottom",
              labels: { usePointStyle: true, padding: 20 },
            },
          },
          scales: type === "bar" ? { y: { beginAtZero: true } } : {},
        },
      });
    }

    function createDualBarChart(id, labels, d1, d2) {
      var ctx = document.getElementById(id);
      if (!ctx) return null;
      return new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Kuota",
              data: d1,
              backgroundColor: "#636363",
              borderWidth: 0,
            },
            {
              label: "Tersalurkan",
              data: d2,
              backgroundColor: "#024ad8",
              borderWidth: 0,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "bottom",
              labels: { usePointStyle: true, padding: 20 },
            },
          },
          scales: {
            y: { beginAtZero: true },
            x: {
              ticks: {
                callback: function (v, i) {
                  var lbl = this.getLabelForValue(v);
                  return lbl.length > 15 ? lbl.substr(0, 15) + "..." : lbl;
                },
              },
            },
          },
        },
      });
    }

    // Generate dummy
    var $genBtn = $wrap.find(".wp-desa-hero__metric--cta button");
    $genBtn.on("click", function () {
      if (!confirm("Buat data dummy untuk SEMUA fitur?")) return;
      $.ajax({
        url: "/wp-json/wp-desa/v1/dashboard/seed-all",
        method: "POST",
        headers: { "X-WP-Nonce": wpNonce },
        success: function (data) {
          if (data.success) {
            alert(data.message);
            fetchStats();
          } else alert("Gagal membuat data dummy.");
        },
      });
    });

    fetchStats();
  }

  // ====================================================================
  // Residents Statistik
  // ====================================================================
  function initResidentsStats() {
    var $wrap = $("#wp-desa-residents-stats");
    if (!$wrap.length)
      $wrap = $(".wrap").filter(function () {
        return (
          $(this).find("#genderChart").length &&
          !$(this).find(".wp-desa-hero").length
        );
      });
    if (!$wrap.length) return;

    var stats = {
      total: 0,
      male: 0,
      female: 0,
      families: 0,
      age_groups: null,
      jobs: [],
      maritals: [],
    };
    var genderChart = null,
      ageChart = null;
    var statsApiUrl =
      (window.wpDesaResidentsStats && window.wpDesaResidentsStats.apiUrl) ||
      "/wp-json/wp-desa/v1/residents";
    var statsNonce =
      (window.wpDesaResidentsStats && window.wpDesaResidentsStats.nonce) ||
      wpNonce;

    function fetchStats() {
      $.ajax({
        url: statsApiUrl + "/stats",
        headers: { "X-WP-Nonce": statsNonce },
        success: function (data) {
          stats = data;
          renderStats();
          setTimeout(renderCharts, 100);
        },
      });
    }

    function renderStats() {
      var $vals = $wrap.find(".wp-desa-stat-value");
      if ($vals.length >= 1) $vals.eq(0).text(formatNumber(stats.total));
      if ($vals.length >= 2) $vals.eq(1).text(formatNumber(stats.families));
      if ($vals.length >= 3) $vals.eq(2).text(formatNumber(stats.male));
      if ($vals.length >= 4) $vals.eq(3).text(formatNumber(stats.female));

      // Jobs table
      var jobsTbody = $wrap.find("table").eq(0).find("tbody");
      if (stats.jobs && stats.jobs.length > 0) {
        var jrows = "";
        $.each(stats.jobs, function (_, j) {
          jrows +=
            "<tr><td>" +
            escapeHtml(j.label || "Tidak Diisi") +
            '</td><td style="text-align:right;font-weight:600;">' +
            formatNumber(j.count) +
            "</td></tr>";
        });
        jobsTbody.html(jrows);
      }

      // Marital table
      var marTbody = $wrap.find("table").eq(1).find("tbody");
      if (stats.maritals && stats.maritals.length > 0) {
        var mrows = "";
        $.each(stats.maritals, function (_, m) {
          mrows +=
            "<tr><td>" +
            escapeHtml(m.label) +
            '</td><td style="text-align:right;font-weight:600;">' +
            formatNumber(m.count) +
            "</td></tr>";
        });
        marTbody.html(mrows);
      }
    }

    function renderCharts() {
      if (genderChart) genderChart.destroy();
      if (ageChart) ageChart.destroy();
      if (typeof Chart === "undefined") {
        setTimeout(renderCharts, 500);
        return;
      }

      var gc = document.getElementById("genderChart");
      if (gc) {
        genderChart = new Chart(gc, {
          type: "doughnut",
          data: {
            labels: ["Laki-laki", "Perempuan"],
            datasets: [
              {
                data: [stats.male || 0, stats.female || 0],
                backgroundColor: ["#024ad8", "#b3262b"],
                borderWidth: 0,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: "bottom",
                labels: { padding: 20, usePointStyle: true },
              },
            },
          },
        });
      }

      var ac = document.getElementById("ageChart");
      if (ac && stats.age_groups) {
        ageChart = new Chart(ac, {
          type: "bar",
          data: {
            labels: ["Anak (<18)", "Dewasa (18-60)", "Lansia (>60)"],
            datasets: [
              {
                label: "Jumlah",
                data: [
                  parseInt(stats.age_groups.anak) || 0,
                  parseInt(stats.age_groups.dewasa) || 0,
                  parseInt(stats.age_groups.lansia) || 0,
                ],
                backgroundColor: ["#c9e0fc", "#024ad8", "#0e3191"],
                borderRadius: 6,
                borderWidth: 0,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
          },
        });
      }
    }

    fetchStats();
  }

  // ====================================================================
  // Settings Notif
  // ====================================================================
  function initSettingsNotif() {
    var $toast = $(".wp-desa-toast");
    if (!$toast.length) return;
    var params = new URLSearchParams(window.location.search);
    if (params.get("settings-updated") === "true") {
      $toast.find("span").last().text("Pengaturan berhasil disimpan!");
      $toast.fadeIn(200);
      setTimeout(function () {
        $toast.fadeOut(200);
      }, 3000);
      // Clean URL
      var newUrl =
        window.location.pathname +
        "?page=wp-desa-settings&tab=" +
        (params.get("tab") || "identitas");
      window.history.replaceState({}, document.title, newUrl);
    }
  }

  // ====================================================================
  // Residents CRUD
  // ====================================================================
  function initResidents() {
    var $wrap;
    $(".wrap").each(function () {
      if (
        $(this).find(".wp-desa__subnav-title").text().trim() ===
          "Data Penduduk" &&
        $(this).find(".wp-desa-table").length
      ) {
        $wrap = $(this);
        return false;
      }
    });
    if (!$wrap || !$wrap.length) return;

    var residentsUrl = wpApiUrl || "/wp-json/wp-desa/v1/residents";

    // Delete
    $wrap.on("click", ".btn-delete-resident", function () {
      var id = parseInt($(this).data("id"), 10);
      if (!confirm("Hapus data ini?")) return;
      $.ajax({
        url: residentsUrl + "/" + id,
        method: "DELETE",
        headers: { "X-WP-Nonce": wpNonce },
        success: function () {
          location.reload();
        },
        error: function () {
          showToast("Gagal menghapus.", "error");
        },
      });
    });

    // Generate Dummy
    $wrap
      .find(".wp-desa-actions button")
      .filter(function () {
        return $(this).text().trim() === "Generate Dummy";
      })
      .on("click", function () {
        if (!confirm("Buat 100 data penduduk dummy?")) return;
        $.ajax({
          url: residentsUrl + "/seed",
          method: "POST",
          headers: { "X-WP-Nonce": wpNonce },
          success: function () {
            location.reload();
          },
        });
      });

    // Export
    $wrap
      .find(".wp-desa-actions button")
      .filter(function () {
        return $(this).text().indexOf("Export") >= 0;
      })
      .on("click", function () {
        window.open(residentsUrl + "/export?_wpnonce=" + wpNonce, "_blank");
      });

    // Import button
    $wrap
      .find(".wp-desa-actions button")
      .filter(function () {
        return $(this).text().indexOf("Import") >= 0;
      })
      .on("click", function () {
        var $fileInput = $wrap.find('input[type="file"]');
        if ($fileInput.length) $fileInput.click();
      });

    // Import file handler
    $wrap.find('input[type="file"]').on("change", function () {
      var file = this.files[0];
      if (!file) return;
      if (!confirm("Import file CSV?")) {
        $(this).val("");
        return;
      }
      var fd = new FormData();
      fd.append("file", file);
      var $btn = $(this);
      $btn.prop("disabled", true);
      $.ajax({
        url: residentsUrl + "/import",
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
        headers: { "X-WP-Nonce": wpNonce },
        success: function () {
          $btn.prop("disabled", false);
          location.reload();
        },
        error: function () {
          $btn.prop("disabled", false);
          showToast("Gagal import.", "error");
        },
      });
    });
  }

  // ====================================================================
  // Finances — dashboard summary + delete
  // ====================================================================
  function initFinances() {
    var $wrap;
    $(".wrap").each(function () {
      if (
        $(this).find(".wp-desa__subnav-title").text().indexOf("Keuangan") >= 0
      ) {
        $wrap = $(this);
        return false;
      }
    });
    if (!$wrap || !$wrap.length) return;

    var baseUrl = "/wp-json/wp-desa/v1/finances";
    var filterYear = new Date().getFullYear();

    function fetchSummary() {
      $.getJSON(baseUrl + "/summary?year=" + filterYear, function (data) {
        var summary = data || {};
        var totals = summary.totals || [];
        var inc = totals.find(function (t) {
          return t.type === "income";
        });
        var exp = totals.find(function (t) {
          return t.type === "expense";
        });
        var $cards = $wrap.find(".wp-desa-stat-value, h3");
        if (inc) $cards.eq(0).text(formatRupiah(inc.total_realization || 0));
        if (exp) $cards.eq(1).text(formatRupiah(exp.total_realization || 0));
        var surplus =
          (inc ? inc.total_realization || 0 : 0) -
          (exp ? exp.total_realization || 0 : 0);
        $cards
          .eq(2)
          .text(formatRupiah(surplus))
          .css("color", surplus >= 0 ? "#1f6b3c" : "#b3262b");
      });
    }

    // Delete
    $wrap.on("click", ".btn-delete-finance", function () {
      var id = $(this).data("id");
      if (!confirm("Hapus data ini?")) return;
      $.ajax({
        url: baseUrl + "/" + id,
        method: "DELETE",
        headers: { "X-WP-Nonce": wpNonce },
        success: function () {
          location.reload();
        },
      });
    });

    fetchSummary();
  }

  // ====================================================================
  // Boot
  // ====================================================================
  $(function () {
    // Dashboard: has wp-desa-hero
    if ($(".wp-desa-hero").length) initDashboard();

    // Settings: has wp-desa-toast and identitas tab
    if ($(".wp-desa-card-settings").length) initSettingsNotif();

    var pageTitle = $(".wp-desa__subnav-title").first().text().trim();

    // Statistik Penduduk: unique chart canvas
    if ($("#genderChart").length) initResidentsStats();

    // Data Penduduk
    if (pageTitle.indexOf("Data Penduduk") >= 0) initResidents();

    // Keuangan Desa
    if (pageTitle.indexOf("Keuangan") >= 0) initFinances();
  });
})(jQuery, window, document);
