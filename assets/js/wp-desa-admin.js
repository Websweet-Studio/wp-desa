/**
 * WP Desa — Admin jQuery Components
 * Replaces all Alpine.js in admin templates (dashboard, residents, kk, statistik,
 * complaints, letters, finances, aid, settings).
 *
 * Depends on jQuery and Chart.js (dashboard, statistik, keuangan).
 */
(function ($, window, document) {
  'use strict';

  // Read nonce from global injected by PHP
  var wpNonce = (window.wpDesaSettings && window.wpDesaSettings.nonce) || '';
  var wpApiUrl = (window.wpDesaSettings && window.wpDesaSettings.apiUrl) || '';

  // ===== Helpers =====
  function formatRupiah(val) {
    val = parseFloat(val) || 0;
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
  }
  function formatNumber(val) {
    if (val === null || val === undefined) return '0';
    return new Intl.NumberFormat('id-ID').format(val);
  }
  function formatDate(dateString) {
    if (!dateString) return '-';
    var d = new Date(dateString);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
  }
  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  function showToast(msg, type) {
    type = type || 'success';
    var $t = $('.wp-desa-toast').first();
    if (!$t.length) return;
    $t.find('span').last().text(msg);
    $t.removeClass('error success').addClass(type);
    $t.fadeIn(200);
    clearTimeout($t.data('_timeout'));
    $t.data('_timeout', setTimeout(function () { $t.fadeOut(200); }, 3000));
  }

  // ====================================================================
  // Dashboard
  // ====================================================================
  function initDashboard() {
    var $wrap = $('.wp-desa-hero').closest('.wrap');
    if (!$wrap.length) return;

    var stats = {};
    var charts = {};

    function fetchStats() {
      $.ajax({
        url: '/wp-json/wp-desa/v1/dashboard/stats',
        headers: { 'X-WP-Nonce': wpNonce },
        success: function (data) {
          stats = data;
          renderStats();
          initCharts();
        }
      });
    }

    function renderStats() {
      // Hero metrics
      var $metrics = $wrap.find('.wp-desa-hero__value');
      if ($metrics.length >= 1) $metrics.eq(0).text(stats.total_residents || 0);
      if ($metrics.length >= 2) $metrics.eq(1).text(stats.total_letters || 0);
      if ($metrics.length >= 3) $metrics.eq(2).text(stats.pending_letters || 0);
      if ($metrics.length >= 4) $metrics.eq(3).text(stats.total_complaints || 0);

      // Stat cards
      var $statValues = $wrap.find('.wp-desa-stat-value');
      $statValues.eq(0).text(stats.total_potensi || 0);   // Potensi
      $statValues.eq(1).text(stats.total_umkm || 0);       // UMKM
      // Finance year
      var $yearSpan = $wrap.find('.wp-desa-stat-title span');
      if ($yearSpan.length) $yearSpan.text(stats.finance_stats && stats.finance_stats.year || '');
      // Finance bars
      var $green = $wrap.find('.wp-desa-text-green');
      var $red = $wrap.find('.wp-desa-text-red');
      if ($green.length) $green.text(formatRupiah((stats.finance_stats && stats.finance_stats.income) || 0));
      if ($red.length) $red.text(formatRupiah((stats.finance_stats && stats.finance_stats.expense) || 0));

      // Expense bar width
      var pct = 0;
      if (stats.finance_stats && stats.finance_stats.income > 0) {
        pct = Math.min((stats.finance_stats.expense / stats.finance_stats.income) * 100, 100);
      }
      $wrap.find('.wp-desa-bar-expense').css('width', pct + '%');

      // Letter status rows
      var $statusList = $wrap.find('.wp-desa-stat-list--status');
      var ls = stats.letter_stats || [];
      if (ls.length > 0) {
        var rows = '';
        $.each(ls, function (_, row) {
          var dotClass = '';
          if (row.label === 'pending') dotClass = 'is-pending';
          else if (row.label === 'processed') dotClass = 'is-progress';
          else if (row.label === 'completed') dotClass = 'is-resolved';
          else if (row.label === 'rejected') dotClass = 'is-rejected';
          var labelMap = { pending: 'Pending', processed: 'Diproses', completed: 'Selesai', rejected: 'Ditolak' };
          rows += '<div class="wp-desa-status-row">' +
            '<span class="wp-desa-list-dot ' + dotClass + '"></span>' +
            '<span class="wp-desa-status-name">' + (labelMap[row.label] || row.label) + '</span>' +
            '<span class="wp-desa-status-count">' + row.count + '</span></div>';
        });
        $statusList.html(rows);
      } else {
        $statusList.html('<p class="wp-desa-empty-state">Belum ada permohonan surat.</p>');
      }

      // Recent complaints
      var $complaints = $wrap.find('.wp-desa-stat-list').not('.wp-desa-stat-list--status');
      var rc = stats.recent_complaints || [];
      if (rc.length > 0) {
        var crow = '';
        $.each(rc, function (_, c) {
          var dotClass = '';
          if (c.status === 'pending') dotClass = 'is-pending';
          else if (c.status === 'in_progress') dotClass = 'is-progress';
          else if (c.status === 'resolved') dotClass = 'is-resolved';
          else if (c.status === 'rejected') dotClass = 'is-rejected';
          var badgeClass = '';
          if (c.status === 'pending') badgeClass = 'wp-desa-badge-pending';
          else if (c.status === 'in_progress') badgeClass = 'wp-desa-badge-warning';
          else if (c.status === 'resolved') badgeClass = 'wp-desa-badge-success';
          else if (c.status === 'rejected') badgeClass = 'wp-desa-badge-danger';
          crow += '<div class="wp-desa-list-row">' +
            '<span class="wp-desa-list-dot ' + dotClass + '"></span>' +
            '<div class="wp-desa-list-main"><p class="wp-desa-list-title">' + escapeHtml(c.subject) + '</p>' +
            '<p class="wp-desa-row-subtitle">' + formatDate(c.created_at) + '</p></div>' +
            '<span class="wp-desa-badge ' + badgeClass + '">' + c.status.replace('_', ' ') + '</span></div>';
        });
        $complaints.html(crow);
      } else {
        $complaints.html('<p class="wp-desa-empty-state">Belum ada aspirasi masuk.</p>');
      }
    }

    function initCharts() {
      $.each(['gender', 'marital', 'job', 'letter', 'aid'], function (_, k) {
        if (charts[k]) { charts[k].destroy(); charts[k] = null; }
      });
      if (typeof Chart === 'undefined') { setTimeout(initCharts, 500); return; }

      if (stats.gender_stats) {
        charts.gender = createChart('genderChart', 'doughnut',
          stats.gender_stats.map(function (i) { return i.label; }),
          stats.gender_stats.map(function (i) { return i.count; }),
          ['#024ad8', '#ff5050']);
      }
      if (stats.marital_stats) {
        charts.marital = createChart('maritalChart', 'pie',
          stats.marital_stats.map(function (i) { return i.label; }),
          stats.marital_stats.map(function (i) { return i.count; }),
          ['#1a1a1a', '#ff5050', '#024ad8', '#636363']);
      }
      if (stats.job_stats) {
        charts.job = createChart('jobChart', 'bar',
          stats.job_stats.map(function (i) { return i.label; }),
          stats.job_stats.map(function (i) { return i.count; }),
          ['#024ad8']);
      }
      if (stats.letter_stats) {
        var lc = { pending: '#ff5050', processed: '#024ad8', completed: '#1f6b3c', rejected: '#b3262b' };
        charts.letter = createChart('letterChart', 'doughnut',
          stats.letter_stats.map(function (i) { return i.label.charAt(0).toUpperCase() + i.label.slice(1); }),
          stats.letter_stats.map(function (i) { return i.count; }),
          stats.letter_stats.map(function (i) { return lc[i.label] || '#93939f'; }));
      }
      if (stats.program_stats) {
        charts.aid = createDualBarChart('aidChart',
          stats.program_stats.map(function (i) { return i.name; }),
          stats.program_stats.map(function (i) { return i.quota; }),
          stats.program_stats.map(function (i) { return i.distributed; }));
      }
    }

    function createChart(id, type, labels, data, colors) {
      var ctx = document.getElementById(id);
      if (!ctx) return null;
      return new Chart(ctx, {
        type: type,
        data: { labels: labels, datasets: [{ label: 'Jumlah', data: data, backgroundColor: colors, borderWidth: 0 }] },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } },
          scales: type === 'bar' ? { y: { beginAtZero: true } } : {}
        }
      });
    }

    function createDualBarChart(id, labels, d1, d2) {
      var ctx = document.getElementById(id);
      if (!ctx) return null;
      return new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            { label: 'Kuota', data: d1, backgroundColor: '#636363', borderWidth: 0 },
            { label: 'Tersalurkan', data: d2, backgroundColor: '#024ad8', borderWidth: 0 }
          ]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } },
          scales: { y: { beginAtZero: true }, x: { ticks: { callback: function (v, i) { var lbl = this.getLabelForValue(v); return lbl.length > 15 ? lbl.substr(0, 15) + '...' : lbl; } } } }
        }
      });
    }

    // Generate dummy
    var $genBtn = $wrap.find('.wp-desa-hero__metric--cta button');
    $genBtn.on('click', function () {
      if (!confirm('Buat data dummy untuk SEMUA fitur?')) return;
      $.ajax({
        url: '/wp-json/wp-desa/v1/dashboard/seed-all',
        method: 'POST',
        headers: { 'X-WP-Nonce': wpNonce },
        success: function (data) {
          if (data.success) { alert(data.message); fetchStats(); }
          else alert('Gagal membuat data dummy.');
        }
      });
    });

    fetchStats();
  }

  // ====================================================================
  // Residents Statistik
  // ====================================================================
  function initResidentsStats() {
    var $wrap = $('#wp-desa-residents-stats');
    if (!$wrap.length) $wrap = $('.wrap').filter(function () { return $(this).find('#genderChart').length && !$(this).find('.wp-desa-hero').length && $(this).find('h1').text().indexOf('Statistik') >= 0; });
    if (!$wrap.length) return;

    var stats = { total: 0, male: 0, female: 0, families: 0, age_groups: null, jobs: [], maritals: [] };
    var genderChart = null, ageChart = null;
    var statsApiUrl = (window.wpDesaResidentsStats && window.wpDesaResidentsStats.apiUrl) || '/wp-json/wp-desa/v1/residents';
    var statsNonce = (window.wpDesaResidentsStats && window.wpDesaResidentsStats.nonce) || wpNonce;

    function fetchStats() {
      $.ajax({
        url: statsApiUrl + '/stats',
        headers: { 'X-WP-Nonce': statsNonce },
        success: function (data) {
          stats = data;
          renderStats();
          setTimeout(renderCharts, 100);
        }
      });
    }

    function renderStats() {
      var $vals = $wrap.find('.wp-desa-stat-value');
      if ($vals.length >= 1) $vals.eq(0).text(formatNumber(stats.total));
      if ($vals.length >= 2) $vals.eq(1).text(formatNumber(stats.families));
      if ($vals.length >= 3) $vals.eq(2).text(formatNumber(stats.male));
      if ($vals.length >= 4) $vals.eq(3).text(formatNumber(stats.female));

      // Jobs table
      var jobsTbody = $wrap.find('table').eq(0).find('tbody');
      if (stats.jobs && stats.jobs.length > 0) {
        var jrows = '';
        $.each(stats.jobs, function (_, j) {
          jrows += '<tr><td>' + escapeHtml(j.label || 'Tidak Diisi') + '</td><td style="text-align:right;font-weight:600;">' + formatNumber(j.count) + '</td></tr>';
        });
        jobsTbody.html(jrows);
      }

      // Marital table
      var marTbody = $wrap.find('table').eq(1).find('tbody');
      if (stats.maritals && stats.maritals.length > 0) {
        var mrows = '';
        $.each(stats.maritals, function (_, m) {
          mrows += '<tr><td>' + escapeHtml(m.label) + '</td><td style="text-align:right;font-weight:600;">' + formatNumber(m.count) + '</td></tr>';
        });
        marTbody.html(mrows);
      }
    }

    function renderCharts() {
      if (genderChart) genderChart.destroy();
      if (ageChart) ageChart.destroy();
      if (typeof Chart === 'undefined') { setTimeout(renderCharts, 500); return; }

      var gc = document.getElementById('genderChart');
      if (gc) {
        genderChart = new Chart(gc, {
          type: 'doughnut',
          data: { labels: ['Laki-laki', 'Perempuan'], datasets: [{ data: [stats.male || 0, stats.female || 0], backgroundColor: ['#024ad8', '#b3262b'], borderWidth: 0 }] },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } } } }
        });
      }

      var ac = document.getElementById('ageChart');
      if (ac && stats.age_groups) {
        ageChart = new Chart(ac, {
          type: 'bar',
          data: {
            labels: ['Anak (<18)', 'Dewasa (18-60)', 'Lansia (>60)'],
            datasets: [{ label: 'Jumlah', data: [parseInt(stats.age_groups.anak) || 0, parseInt(stats.age_groups.dewasa) || 0, parseInt(stats.age_groups.lansia) || 0], backgroundColor: ['#c9e0fc', '#024ad8', '#0e3191'], borderRadius: 6, borderWidth: 0 }]
          },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
      }
    }

    fetchStats();
  }

  // ====================================================================
  // Settings Notif
  // ====================================================================
  function initSettingsNotif() {
    var $toast = $('.wp-desa-toast');
    if (!$toast.length) return;
    var params = new URLSearchParams(window.location.search);
    if (params.get('settings-updated') === 'true') {
      $toast.find('span').last().text('Pengaturan berhasil disimpan!');
      $toast.fadeIn(200);
      setTimeout(function () { $toast.fadeOut(200); }, 3000);
      // Clean URL
      var newUrl = window.location.pathname + '?page=wp-desa-settings&tab=' + (params.get('tab') || 'identitas');
      window.history.replaceState({}, document.title, newUrl);
    }
  }

  // ====================================================================
  // Residents CRUD
  // ====================================================================
  function initResidents() {
    var $wrap;
    $('.wrap').each(function () {
      if ($(this).find('h1').text().trim() === 'Data Penduduk' && $(this).find('.wp-desa-table').length) {
        $wrap = $(this); return false;
      }
    });
    if (!$wrap || !$wrap.length) return;

    var residentsUrl = wpApiUrl || '/wp-json/wp-desa/v1/residents';
    var residents = [], loading = true, isModalOpen = false, modalMode = 'add', saving = false;
    var pagination = { currentPage: 1, perPage: 20, totalPages: 1, totalItems: 0 };
    var form = { id: null, nik: '', no_kk: '', nama_lengkap: '', jenis_kelamin: 'Laki-laki', tempat_lahir: '', tanggal_lahir: '', alamat: '', status_perkawinan: 'Belum Kawin', pekerjaan: '' };

    var $tbody = $wrap.find('table tbody');
    var $pagination = $wrap.find('.wp-desa-pagination');
    var $modal = $wrap.find('.wp-desa-modal-overlay');
    var $modalTitle = $wrap.find('.wp-desa-modal-title');

    // Form fields in modal
    var $formNik = $wrap.find('#nik');
    var $formKk = $wrap.find('#no_kk');
    var $formNama = $wrap.find('#nama_lengkap');
    var $formJk = $wrap.find('#jenis_kelamin');
    var $formSp = $wrap.find('#status_perkawinan');
    var $formTl = $wrap.find('#tempat_lahir');
    var $formTgl = $wrap.find('#tanggal_lahir');
    var $formPkj = $wrap.find('#pekerjaan');
    var $formAlamat = $wrap.find('#alamat');
    var $saveBtn = $modal.find('button[type="submit"]');
    var $closeBtn = $modal.find('.wp-desa-icon-btn, .wp-desa-btn-secondary').first();

    function fetchResidents(page) {
      page = page || 1;
      loading = true;
      renderTable();
      $.ajax({
        url: residentsUrl + '?page=' + page + '&per_page=' + pagination.perPage,
        headers: { 'X-WP-Nonce': wpNonce },
        success: function (data) {
          loading = false;
          if (data.meta) {
            residents = data.data || [];
            pagination = { currentPage: parseInt(data.meta.current_page), perPage: parseInt(data.meta.per_page), totalPages: parseInt(data.meta.total_pages), totalItems: parseInt(data.meta.total_items) };
          } else {
            residents = Array.isArray(data) ? data : [];
            pagination.totalItems = residents.length;
            pagination.totalPages = 1;
          }
          renderTable();
        },
        error: function () { loading = false; renderTable(); showToast('Gagal memuat data.', 'error'); }
      });
    }

    function renderTable() {
      if (loading) {
        $tbody.html('<tr><td colspan="7" class="wp-desa-empty-state">Memuat data...</td></tr>');
        $pagination.hide();
        return;
      }
      if (!residents.length) {
        $tbody.html('<tr><td colspan="7" class="wp-desa-empty-state"><div>Belum ada data penduduk.</div></td></tr>');
        $pagination.hide();
        return;
      }
      var rows = '';
      $.each(residents, function (_, r) {
        var jkClass = r.jenis_kelamin === 'Laki-laki' ? 'wp-desa-badge-default' : 'wp-desa-badge-danger';
        rows += '<tr>' +
          '<td class="wp-desa-mono">' + escapeHtml(r.nik) + '</td>' +
          '<td class="wp-desa-mono">' + escapeHtml(r.no_kk || '-') + '</td>' +
          '<td><div style="font-weight:600;color:var(--ink);">' + escapeHtml(r.nama_lengkap) + '</div><div class="wp-desa-row-subtitle">' + escapeHtml(r.status_perkawinan) + '</div></td>' +
          '<td><span class="wp-desa-badge ' + jkClass + '">' + escapeHtml(r.jenis_kelamin) + '</span></td>' +
          '<td><div>' + escapeHtml(r.tempat_lahir) + '</div><div class="wp-desa-row-subtitle">' + formatDate(r.tanggal_lahir) + '</div></td>' +
          '<td>' + escapeHtml(r.pekerjaan) + '</td>' +
          '<td style="text-align:right;"><div class="wp-desa-inline-actions-end">' +
          '<button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm btn-edit-resident" data-id="' + r.id + '">Edit</button> ' +
          '<button class="wp-desa-btn wp-desa-btn-danger-outline wp-desa-btn-sm btn-delete-resident" data-id="' + r.id + '">Hapus</button></div></td></tr>';
      });
      $tbody.html(rows);

      // Pagination
      var pi = (pagination.currentPage - 1) * pagination.perPage + 1;
      var pa = Math.min(pagination.currentPage * pagination.perPage, pagination.totalItems);
      $pagination.find('span').eq(0).text(pi);
      $pagination.find('span').eq(1).text(pa);
      $pagination.find('span').eq(2).text(pagination.totalItems);
      $pagination.find('span').eq(3).text(pagination.currentPage);
      $pagination.find('span').eq(4).text(pagination.totalPages);
      $pagination.show();
    }

    function openModal(mode, resident) {
      modalMode = mode;
      if (mode === 'edit' && resident) {
        form = $.extend({}, resident, { no_kk: resident.no_kk || '' });
      } else {
        form = { id: null, nik: '', no_kk: '', nama_lengkap: '', jenis_kelamin: 'Laki-laki', tempat_lahir: '', tanggal_lahir: '', alamat: '', status_perkawinan: 'Belum Kawin', pekerjaan: '' };
      }
      $modalTitle.text(mode === 'add' ? 'Tambah Penduduk' : 'Edit Penduduk');
      populateForm();
      $modal.fadeIn(200);
      isModalOpen = true;
    }

    function closeModal() {
      $modal.fadeOut(200);
      isModalOpen = false;
    }

    function populateForm() {
      $formNik.val(form.nik);
      $formKk.val(form.no_kk);
      $formNama.val(form.nama_lengkap);
      $formJk.val(form.jenis_kelamin);
      $formSp.val(form.status_perkawinan);
      $formTl.val(form.tempat_lahir);
      $formTgl.val(form.tanggal_lahir);
      $formPkj.val(form.pekerjaan);
      $formAlamat.val(form.alamat);
    }

    function readForm() {
      form.nik = $formNik.val();
      form.no_kk = $formKk.val();
      form.nama_lengkap = $formNama.val();
      form.jenis_kelamin = $formJk.val();
      form.status_perkawinan = $formSp.val();
      form.tempat_lahir = $formTl.val();
      form.tanggal_lahir = $formTgl.val();
      form.pekerjaan = $formPkj.val();
      form.alamat = $formAlamat.val();
    }

    function saveResident(e) {
      e.preventDefault();
      readForm();
      saving = true;
      $saveBtn.prop('disabled', true);
      var url = modalMode === 'edit' ? residentsUrl + '/' + form.id : residentsUrl;
      $.ajax({
        url: url,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(form),
        headers: { 'X-WP-Nonce': wpNonce },
        success: function (data) {
          saving = false;
          $saveBtn.prop('disabled', false);
          if (data.code) { showToast(data.message || 'Error.', 'error'); return; }
          closeModal();
          fetchResidents(pagination.currentPage);
          showToast(modalMode === 'edit' ? 'Data berhasil diperbarui.' : 'Data berhasil ditambahkan.');
        },
        error: function () { saving = false; $saveBtn.prop('disabled', false); showToast('Gagal menyimpan.', 'error'); }
      });
    }

    // Events
    $wrap.on('click', '.btn-edit-resident', function () {
      var id = parseInt($(this).data('id'), 10);
      var r = residents.find(function (x) { return x.id === id; });
      if (r) openModal('edit', r);
    });
    $wrap.on('click', '.btn-delete-resident', function () {
      var id = parseInt($(this).data('id'), 10);
      if (!confirm('Hapus data ini?')) return;
      $.ajax({
        url: residentsUrl + '/' + id,
        method: 'DELETE',
        headers: { 'X-WP-Nonce': wpNonce },
        success: function () { fetchResidents(pagination.currentPage); showToast('Data berhasil dihapus.'); },
        error: function () { showToast('Gagal menghapus.', 'error'); }
      });
    });
    $wrap.find('.wp-desa-actions button').eq(3).on('click', function () { openModal('add'); }); // Tambah
    $closeBtn.on('click', closeModal);
    $modal.find('form').on('submit', saveResident);
    $modal.on('click', function (e) { if ($(e.target).is($modal)) closeModal(); });

    // Pagination
    $wrap.on('click', '.wp-desa-pagination button', function () {
      var $btn = $(this);
      if ($btn.prop('disabled')) return;
      if ($btn.find('.dashicons-arrow-left-alt2').length) {
        if (pagination.currentPage > 1) fetchResidents(pagination.currentPage - 1);
      } else {
        if (pagination.currentPage < pagination.totalPages) fetchResidents(pagination.currentPage + 1);
      }
    });

    // Export / Import
    $wrap.find('.wp-desa-actions button').eq(0).on('click', function () { // Generate Dummy
      if (!confirm('Buat 100 data penduduk dummy?')) return;
      $.ajax({ url: residentsUrl + '/seed', method: 'POST', headers: { 'X-WP-Nonce': wpNonce }, success: function () { fetchResidents(); } });
    });
    $wrap.find('.wp-desa-actions button').eq(1).on('click', function () { // Export
      window.open(residentsUrl + '/export?_wpnonce=' + wpNonce, '_blank');
    });
    $wrap.find('.wp-desa-actions button').eq(2).on('click', function () { // Import trigger
      var $fileInput = $wrap.find('input[type="file"]');
      if ($fileInput.length) $fileInput.click();
    });
    $wrap.find('input[type="file"]').on('change', function () {
      var file = this.files[0];
      if (!file) return;
      if (!confirm('Import file CSV?')) { $(this).val(''); return; }
      var fd = new FormData();
      fd.append('file', file);
      loading = true;
      renderTable();
      $.ajax({
        url: residentsUrl + '/import',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        headers: { 'X-WP-Nonce': wpNonce },
        success: function (data) {
          loading = false;
          $(this).val('');
          if (data.code) { showToast(data.message, 'error'); return; }
          fetchResidents();
          showToast(data.message || 'Import selesai.');
        },
        error: function () { loading = false; showToast('Gagal import.', 'error'); }
      });
    });

    fetchResidents();
  }

  // ====================================================================
  // KK
  // ====================================================================
  function initKK() {
    var $wrap;
    $('.wrap').each(function () {
      if ($(this).find('h1').text().trim().indexOf('Kartu Keluarga') >= 0) { $wrap = $(this); return false; }
    });
    if (!$wrap || !$wrap.length) return;

    var kkUrl = '/wp-json/wp-desa/v1/residents/kk';
    var kkList = [], loading = true, isModalOpen = false, selectedKK = '', anggotaList = [], anggotaLoading = false;
    var pagination = { currentPage: 1, perPage: 20, totalPages: 1, totalItems: 0 };

    var $tbody = $wrap.find('table tbody');
    var $pagination = $wrap.find('.wp-desa-pagination');
    var $modal = $wrap.find('.wp-desa-modal-overlay');
    var $modalTitle = $wrap.find('.wp-desa-modal-title');
    var $modalTbody = $modal.find('table tbody');

    function fetchKK(page) {
      page = page || 1;
      loading = true;
      renderKK();
      $.ajax({
        url: kkUrl + '?page=' + page + '&per_page=' + pagination.perPage,
        headers: { 'X-WP-Nonce': wpNonce },
        success: function (data) {
          loading = false;
          kkList = data.data || [];
          if (data.meta) {
            pagination = { currentPage: parseInt(data.meta.current_page), perPage: parseInt(data.meta.per_page), totalPages: parseInt(data.meta.total_pages), totalItems: parseInt(data.meta.total_items) };
          }
          renderKK();
        }
      });
    }

    function renderKK() {
      if (loading) {
        $tbody.html('<tr><td colspan="4" class="wp-desa-empty-state">Memuat data...</td></tr>');
        $pagination.hide();
        return;
      }
      if (!kkList.length) {
        $tbody.html('<tr><td colspan="4" class="wp-desa-empty-state">Belum ada data KK.</td></tr>');
        $pagination.hide();
        return;
      }
      var rows = '';
      $.each(kkList, function (_, kk) {
        rows += '<tr>' +
          '<td class="wp-desa-mono">' + escapeHtml(kk.no_kk) + '</td>' +
          '<td>' + escapeHtml(kk.kepala_keluarga || '-') + '</td>' +
          '<td>' + (kk.jumlah_anggota || 0) + '</td>' +
          '<td style="text-align:right;"><button class="wp-desa-btn wp-desa-btn-primary wp-desa-btn-sm btn-view-kk" data-no-kk="' + escapeHtml(kk.no_kk) + '">Lihat Anggota</button></td></tr>';
      });
      $tbody.html(rows);
      $pagination.show();
    }

    function viewAnggota(no_kk) {
      selectedKK = no_kk;
      anggotaList = [];
      anggotaLoading = true;
      $modalTitle.text('Anggota KK: ' + no_kk);
      $modalTbody.html('<tr><td colspan="6" style="text-align:center;padding:30px;">Memuat data...</td></tr>');
      $modal.fadeIn(200);
      $.ajax({
        url: kkUrl + '/' + no_kk,
        headers: { 'X-WP-Nonce': wpNonce },
        success: function (data) {
          anggotaLoading = false;
          anggotaList = Array.isArray(data) ? data : [];
          var arows = '';
          if (!anggotaList.length) {
            arows = '<tr><td colspan="6" style="text-align:center;">Tidak ada anggota.</td></tr>';
          } else {
            $.each(anggotaList, function (_, a) {
              var jkClass = a.jenis_kelamin === 'Laki-laki' ? 'wp-desa-badge-default' : 'wp-desa-badge-danger';
              arows += '<tr>' +
                '<td>' + escapeHtml(a.nik) + '</td>' +
                '<td>' + escapeHtml(a.nama_lengkap) + '</td>' +
                '<td><span class="wp-desa-badge ' + jkClass + '">' + escapeHtml(a.jenis_kelamin) + '</span></td>' +
                '<td>' + escapeHtml(a.tempat_lahir) + '</td>' +
                '<td>' + formatDate(a.tanggal_lahir) + '</td>' +
                '<td>' + escapeHtml(a.status_perkawinan) + '</td></tr>';
            });
          }
          $modalTbody.html(arows);
        },
        error: function () { anggotaLoading = false; $modalTbody.html('<tr><td colspan="6" style="text-align:center;">Gagal memuat data.</td></tr>'); }
      });
    }

    $wrap.on('click', '.btn-view-kk', function () { viewAnggota($(this).data('no-kk')); });
    $modal.find('.wp-desa-icon-btn, button').last().on('click', function () { $modal.fadeOut(200); });
    $modal.on('click', function (e) { if ($(e.target).is($modal)) $modal.fadeOut(200); });
    $wrap.on('click', '.wp-desa-pagination button', function () {
      var $btn = $(this);
      if ($btn.prop('disabled')) return;
      if ($btn.find('.dashicons-arrow-left-alt2').length) {
        if (pagination.currentPage > 1) fetchKK(pagination.currentPage - 1);
      } else {
        if (pagination.currentPage < pagination.totalPages) fetchKK(pagination.currentPage + 1);
      }
    });

    fetchKK();
  }

  // ====================================================================
  // Complaints
  // ====================================================================
  function initComplaints() {
    var $wrap;
    $('.wrap').each(function () {
      if ($(this).find('h1').text().trim().indexOf('Aspirasi') >= 0 || $(this).find('h1').text().trim().indexOf('Aduan') >= 0) { $wrap = $(this); return false; }
    });
    if (!$wrap || !$wrap.length) return;

    var baseUrl = '/wp-json/wp-desa/v1/complaints';
    var complaints = [], loading = true, currentStatus = '';
    var pagination = { currentPage: 1, perPage: 20, totalPages: 1, totalItems: 0 };
    var counts = { all: 0, pending: 0, in_progress: 0, resolved: 0, rejected: 0 };
    var isModalOpen = false, selectedItem = null;

    var $tbody = $wrap.find('table tbody');
    var $pagination = $wrap.find('.wp-desa-pagination');
    var $statusTabs = $wrap.find('.wp-desa-tabs button, .wp-desa-tab-btn, .wp-desa-filter-btn');
    var $modal = $wrap.find('.wp-desa-modal-overlay');

    function fetchComplaints() {
      loading = true;
      renderTable();
      var url = baseUrl + '?page=' + pagination.currentPage + '&per_page=' + pagination.perPage;
      if (currentStatus) url += '&status=' + currentStatus;
      $.ajax({
        url: url,
        headers: { 'X-WP-Nonce': wpNonce },
        success: function (data) {
          loading = false;
          complaints = data.data || [];
          if (data.meta) {
            pagination = { currentPage: parseInt(data.meta.current_page), perPage: parseInt(data.meta.per_page), totalPages: parseInt(data.meta.total_pages), totalItems: parseInt(data.meta.total_items) };
          }
          counts = data.counts || {};
          updateCountTabs();
          renderTable();
        },
        error: function () { loading = false; renderTable(); }
      });
    }

    function updateCountTabs() {
      $statusTabs.each(function () {
        var $t = $(this);
        var label = $t.text().replace(/[0-9]/g, '').trim().toLowerCase();
        var count = 0;
        if (label === 'semua') count = counts.all || 0;
        else if (label === 'pending' || label === 'menunggu') count = counts.pending || 0;
        else if (label === 'diproses') count = counts.in_progress || 0;
        else if (label === 'selesai') count = counts.resolved || 0;
        else if (label === 'ditolak') count = counts.rejected || 0;
        var span = $t.find('span, .count');
        if (span.length) span.text(count);
      });
    }

    function renderTable() {
      if (loading) { $tbody.html('<tr><td colspan="6" class="wp-desa-empty-state">Memuat...</td></tr>'); $pagination.hide(); return; }
      if (!complaints.length) { $tbody.html('<tr><td colspan="6" class="wp-desa-empty-state">Tidak ada data.</td></tr>'); $pagination.hide(); return; }
      var rows = '';
      $.each(complaints, function (_, c) {
        var badgeClass = '';
        if (c.status === 'pending') badgeClass = 'wp-desa-badge-pending';
        else if (c.status === 'in_progress') badgeClass = 'wp-desa-badge-warning';
        else if (c.status === 'resolved') badgeClass = 'wp-desa-badge-success';
        else if (c.status === 'rejected') badgeClass = 'wp-desa-badge-danger';
        var statusLabel = c.status.replace('_', ' ');
        rows += '<tr>' +
          '<td>' + formatDate(c.created_at) + '</td>' +
          '<td class="wp-desa-mono">' + escapeHtml(c.tracking_code) + '</td>' +
          '<td>' + escapeHtml(c.category) + '</td>' +
          '<td>' + escapeHtml(c.reporter_name) + '</td>' +
          '<td>' + escapeHtml(c.subject) + '</td>' +
          '<td><span class="wp-desa-badge ' + badgeClass + '">' + statusLabel + '</span></td>' +
          '<td style="text-align:right;"><button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm btn-detail-complaint" data-id="' + c.id + '">Detail</button></td></tr>';
      });
      $tbody.html(rows);
      $pagination.show();
    }

    $statusTabs.on('click', function () {
      var label = $(this).text().replace(/[0-9]/g, '').trim().toLowerCase();
      if (label === 'semua') currentStatus = '';
      else if (label === 'pending' || label === 'menunggu') currentStatus = 'pending';
      else if (label === 'diproses') currentStatus = 'in_progress';
      else if (label === 'selesai') currentStatus = 'resolved';
      else if (label === 'ditolak') currentStatus = 'rejected';
      pagination.currentPage = 1;
      $statusTabs.removeClass('active');
      $(this).addClass('active');
      fetchComplaints();
    });

    $wrap.on('click', '.btn-detail-complaint', function () {
      var id = parseInt($(this).data('id'), 10);
      selectedItem = $.extend({}, complaints.find(function (x) { return x.id === id; }));
      // Populate modal
      $modal.find('select').val(selectedItem.status);
      $modal.find('textarea').val(selectedItem.response || '');
      $modal.fadeIn(200);
    });

    $modal.find('form').on('submit', function (e) {
      e.preventDefault();
      if (!selectedItem) return;
      selectedItem.status = $modal.find('select').val();
      selectedItem.response = $modal.find('textarea').val();
      $.ajax({
        url: baseUrl + '/' + selectedItem.id,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ status: selectedItem.status, response: selectedItem.response }),
        headers: { 'X-WP-Nonce': wpNonce },
        success: function (data) {
          if (data.success) { showToast('Status diperbarui.'); fetchComplaints(); $modal.fadeOut(200); }
          else showToast('Gagal update.', 'error');
        }
      });
    });

    $modal.find('.wp-desa-icon-btn, .close-btn').on('click', function () { $modal.fadeOut(200); });
    $modal.on('click', function (e) { if ($(e.target).is($modal)) $modal.fadeOut(200); });

    $wrap.on('click', '.wp-desa-pagination button', function () {
      var $btn = $(this);
      if ($btn.prop('disabled')) return;
      if ($btn.find('.dashicons-arrow-left-alt2').length) {
        if (pagination.currentPage > 1) { pagination.currentPage--; fetchComplaints(); }
      } else {
        if (pagination.currentPage < pagination.totalPages) { pagination.currentPage++; fetchComplaints(); }
      }
    });

    fetchComplaints();
  }

  // ====================================================================
  // Letters
  // ====================================================================
  function initLetters() {
    var $wrap;
    $('.wrap').each(function () {
      if ($(this).find('h1').text().trim().indexOf('Layanan Surat') >= 0 || $(this).find('h1').text().trim().indexOf('Surat') >= 0) {
        $wrap = $(this); return false;
      }
    });
    if (!$wrap || !$wrap.length) return;

    var baseUrl = '/wp-json/wp-desa/v1/letters';
    var letters = [], loading = true, currentStatus = '';
    var pagination = { currentPage: 1, perPage: 20, totalPages: 1, totalItems: 0 };
    var counts = { all: 0, pending: 0, processed: 0, completed: 0, rejected: 0 };
    var isModalOpen = false, selectedLetter = null;

    var $tbody = $wrap.find('table tbody');
    var $pagination = $wrap.find('.wp-desa-pagination');
    var $statusTabs = $wrap.find('.wp-desa-tabs button, .wp-desa-tab-btn, .wp-desa-filter-btn');
    var $modal = $wrap.find('.wp-desa-modal-overlay');

    function fetchLetters() {
      loading = true;
      renderTable();
      var url = baseUrl + '?page=' + pagination.currentPage + '&per_page=' + pagination.perPage;
      if (currentStatus) url += '&status=' + currentStatus;
      $.ajax({
        url: url, headers: { 'X-WP-Nonce': wpNonce },
        success: function (data) {
          loading = false;
          letters = data.data || [];
          if (data.meta) pagination = {
            currentPage: parseInt(data.meta.current_page), perPage: parseInt(data.meta.per_page),
            totalPages: parseInt(data.meta.total_pages), totalItems: parseInt(data.meta.total_items)
          };
          counts = data.counts || {};
          renderTable();
        },
        error: function () { loading = false; renderTable(); }
      });
    }

    function renderTable() {
      if (loading) { $tbody.html('<tr><td colspan="7" class="wp-desa-empty-state">Memuat...</td></tr>'); $pagination.hide(); return; }
      if (!letters.length) { $tbody.html('<tr><td colspan="7" class="wp-desa-empty-state">Tidak ada data.</td></tr>'); $pagination.hide(); return; }
      var rows = '';
      var labelMap = { pending: 'Pending', processed: 'Diproses', completed: 'Selesai', rejected: 'Ditolak' };
      $.each(letters, function (_, l) {
        var badgeClass = ''; if (l.status === 'pending') badgeClass = 'wp-desa-badge-pending'; else if (l.status === 'processed') badgeClass = 'wp-desa-badge-warning'; else if (l.status === 'completed') badgeClass = 'wp-desa-badge-success'; else badgeClass = 'wp-desa-badge-danger';
        rows += '<tr>' +
          '<td>' + formatDate(l.created_at) + '</td>' +
          '<td class="wp-desa-mono">' + escapeHtml(l.tracking_code) + '</td>' +
          '<td>' + escapeHtml(l.type_name) + '</td>' +
          '<td>' + escapeHtml(l.name) + '</td>' +
          '<td>' + escapeHtml(l.nik) + '</td>' +
          '<td><span class="wp-desa-badge ' + badgeClass + '">' + (labelMap[l.status] || l.status) + '</span></td>' +
          '<td style="text-align:right;">' +
          '<button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm btn-detail-letter" data-id="' + l.id + '">Detail</button> ' +
          '<button class="wp-desa-btn wp-desa-btn-primary wp-desa-btn-sm btn-print-letter" data-id="' + l.id + '">Cetak</button></td></tr>';
      });
      $tbody.html(rows);
      $pagination.show();
    }

    $statusTabs.on('click', function () {
      var label = $(this).text().replace(/[0-9]/g, '').trim().toLowerCase();
      if (label === 'semua') currentStatus = '';
      else if (label === 'pending' || label === 'menunggu') currentStatus = 'pending';
      else if (label === 'processed' || label === 'diproses') currentStatus = 'processed';
      else if (label === 'completed' || label === 'selesai') currentStatus = 'completed';
      else if (label === 'rejected' || label === 'ditolak') currentStatus = 'rejected';
      pagination.currentPage = 1;
      $statusTabs.removeClass('active'); $(this).addClass('active');
      fetchLetters();
    });

    $wrap.on('click', '.btn-detail-letter', function () {
      var id = parseInt($(this).data('id'), 10);
      selectedLetter = $.extend({}, letters.find(function (x) { return x.id === id; }));
      $modal.find('select').val(selectedLetter.status);
      $modal.fadeIn(200);
    });

    $modal.find('.btn-save-status, button[type="submit"]').on('click', function (e) {
      e.preventDefault();
      if (!selectedLetter) return;
      var newStatus = $modal.find('select').val();
      $.ajax({
        url: baseUrl + '/' + selectedLetter.id,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ status: newStatus }),
        headers: { 'X-WP-Nonce': wpNonce },
        success: function () { showToast('Status diperbarui.'); fetchLetters(); $modal.fadeOut(200); }
      });
    });

    $modal.find('.wp-desa-icon-btn, .close-btn, .wp-desa-btn-secondary').on('click', function () { $modal.fadeOut(200); });
    $modal.on('click', function (e) { if ($(e.target).is($modal)) $modal.fadeOut(200); });

    $wrap.on('click', '.btn-print-letter', function () {
      var id = $(this).data('id');
      window.open('/wp-admin/admin-post.php?action=wp_desa_print_letter&id=' + id + '&_wpnonce=' + wpNonce, '_blank');
    });

    $wrap.on('click', '.wp-desa-pagination button', function () {
      var $btn = $(this);
      if ($btn.prop('disabled')) return;
      if ($btn.find('.dashicons-arrow-left-alt2').length) {
        if (pagination.currentPage > 1) { pagination.currentPage--; fetchLetters(); }
      } else {
        if (pagination.currentPage < pagination.totalPages) { pagination.currentPage++; fetchLetters(); }
      }
    });

    fetchLetters();
  }

  // ====================================================================
  // Finances
  // ====================================================================
  function initFinances() {
    var $wrap;
    $('.wrap').each(function () {
      if ($(this).find('h1').text().indexOf('Keuangan') >= 0) { $wrap = $(this); return false; }
    });
    if (!$wrap || !$wrap.length) return;

    var baseUrl = '/wp-json/wp-desa/v1/finances';
    var tab = 'dashboard', filterYear = new Date().getFullYear(), filterType = '';
    var items = [], loading = false, years = [], summary = {};
    var pagination = { currentPage: 1, perPage: 20, totalPages: 1, totalItems: 0 };
    var isModalOpen = false, editMode = false;
    var form = { id: null, year: filterYear, transaction_date: '', type: 'income', category: '', description: '', budget_amount: '', realization_amount: '' };

    var $tabs = $wrap.find('.wp-desa-tabs button, .wp-desa-tab-btn');
    var $dataPanel = $wrap.find('.wp-desa-tab-content, [data-tab="data"]');
    var $dashboardPanel = $wrap.find('[data-tab="dashboard"], .wp-desa-summary-grid').parent();

    var $yearSelect = $wrap.find('.wp-desa-select-year, select[data-filter="year"]');
    var $typeSelect = $wrap.find('select[data-filter="type"], select:nth-of-type(2)');
    var $tbody = $wrap.find('table tbody');
    var $pagination = $wrap.find('.wp-desa-pagination');
    var $modal = $wrap.find('.wp-desa-modal-overlay');

    // Init years
    var cy = new Date().getFullYear();
    for (var i = cy; i >= cy - 5; i--) years.push(i);
    filterYear = cy;

    function fetchSummary() {
      $.getJSON(baseUrl + '/summary?year=' + filterYear, function (data) {
        summary = data || {};
        // Update summary cards
        var totals = summary.totals || [];
        var inc = totals.find(function (t) { return t.type === 'income'; });
        var exp = totals.find(function (t) { return t.type === 'expense'; });
        var $cards = $dashboardPanel.find('.wp-desa-stat-value, h3');
        if (inc) $cards.eq(0).text(formatRupiah(inc.total_realization || 0));
        if (exp) $cards.eq(1).text(formatRupiah(exp.total_realization || 0));
        var surplus = (inc ? inc.total_realization || 0 : 0) - (exp ? exp.total_realization || 0 : 0);
        $cards.eq(2).text(formatRupiah(surplus)).css('color', surplus >= 0 ? '#1f6b3c' : '#b3262b');
      });
    }

    function fetchData() {
      loading = true;
      renderTable();
      var url = baseUrl + '?page=' + pagination.currentPage + '&per_page=' + pagination.perPage + '&year=' + filterYear;
      if (filterType) url += '&type=' + filterType;
      $.ajax({
        url: url, headers: { 'X-WP-Nonce': wpNonce },
        success: function (data) {
          loading = false;
          items = data.data || [];
          if (data.meta) pagination = {
            currentPage: parseInt(data.meta.current_page), perPage: parseInt(data.meta.per_page),
            totalPages: parseInt(data.meta.total_pages), totalItems: parseInt(data.meta.total_items)
          };
          renderTable();
        }
      });
    }

    function renderTable() {
      if (loading) { $tbody.html('<tr><td colspan="6" class="wp-desa-empty-state">Memuat...</td></tr>'); $pagination.hide(); return; }
      if (!items.length) { $tbody.html('<tr><td colspan="6" class="wp-desa-empty-state">Tidak ada data.</td></tr>'); $pagination.hide(); return; }
      var rows = '';
      $.each(items, function (_, it) {
        var typeBadge = it.type === 'income' ? 'wp-desa-badge-income' : 'wp-desa-badge-expense';
        rows += '<tr>' +
          '<td>' + escapeHtml(it.category) + '</td>' +
          '<td>' + escapeHtml(it.description) + '</td>' +
          '<td><span class="' + typeBadge + '">' + (it.type === 'income' ? 'Pendapatan' : 'Belanja') + '</span></td>' +
          '<td style="text-align:right;">' + formatRupiah(it.budget_amount) + '</td>' +
          '<td style="text-align:right;">' + formatRupiah(it.realization_amount) + '</td>' +
          '<td style="text-align:right;">' +
          '<button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm btn-edit-finance" data-id="' + it.id + '">Edit</button> ' +
          '<button class="wp-desa-btn wp-desa-btn-danger-outline wp-desa-btn-sm btn-delete-finance" data-id="' + it.id + '">Hapus</button></td></tr>';
      });
      $tbody.html(rows);
      $pagination.show();
    }

    // Tab switching
    $tabs.on('click', function () {
      var label = $(this).text().trim().toLowerCase();
      if (label.indexOf('dashboard') >= 0) { tab = 'dashboard'; $dashboardPanel.show(); $dataPanel.hide(); fetchSummary(); }
      else { tab = 'data'; $dashboardPanel.hide(); $dataPanel.show(); fetchData(); }
      $tabs.removeClass('active'); $(this).addClass('active');
    });

    // Init year select
    if ($yearSelect.length) {
      var yhtml = '';
      $.each(years, function (_, y) { yhtml += '<option value="' + y + '">' + y + '</option>'; });
      $yearSelect.html(yhtml).val(filterYear);
    }

    $yearSelect.on('change', function () { filterYear = parseInt($(this).val()); fetchSummary(); fetchData(); });
    if ($typeSelect.length) $typeSelect.on('change', function () { filterType = $(this).val(); pagination.currentPage = 1; fetchData(); });

    $wrap.on('click', '.btn-delete-finance', function () {
      var id = $(this).data('id');
      if (!confirm('Hapus data ini?')) return;
      $.ajax({ url: baseUrl + '/' + id, method: 'DELETE', headers: { 'X-WP-Nonce': wpNonce }, success: function () { fetchData(); showToast('Data dihapus.'); } });
    });

    $wrap.on('click', '.wp-desa-pagination button', function () {
      var $btn = $(this);
      if ($btn.prop('disabled')) return;
      if ($btn.find('.dashicons-arrow-left-alt2').length) { if (pagination.currentPage > 1) { pagination.currentPage--; fetchData(); } }
      else { if (pagination.currentPage < pagination.totalPages) { pagination.currentPage++; fetchData(); } }
    });

    // Initial load
    if ($dashboardPanel.is(':visible') || !$dataPanel.length) { fetchSummary(); }
    else { fetchData(); }
  }

  // ====================================================================
  // Aid
  // ====================================================================
  function initAid() {
    var $wrap;
    $('.wrap').each(function () {
      if ($(this).find('h1').text().indexOf('Bantuan') >= 0) { $wrap = $(this); return false; }
    });
    if (!$wrap || !$wrap.length) return;

    var baseUrl = '/wp-json/wp-desa/v1/aid-programs';
    var view = 'programs', programs = [], recipients = [], activeProgram = null;
    var progPagination = { currentPage: 1, perPage: 20, totalPages: 1, totalItems: 0 };
    var recPagination = { currentPage: 1, perPage: 20, totalPages: 1, totalItems: 0 };
    var isModalOpen = false, modalMode = 'add';

    var $programGrid = $wrap.find('.wp-desa-stats-grid, .wp-desa-card').first();
    var $recipientsPanel = $wrap.find('[data-view="recipients"]');
    var $modal = $wrap.find('.wp-desa-modal-overlay');

    fetchPrograms();

    function fetchPrograms(page) {
      page = page || 1;
      $.ajax({ url: baseUrl + '?page=' + page + '&per_page=' + progPagination.perPage, headers: { 'X-WP-Nonce': wpNonce }, success: function (d) { programs = d.data || []; if (d.meta) progPagination = { currentPage: parseInt(d.meta.current_page), perPage: parseInt(d.meta.per_page), totalPages: parseInt(d.meta.total_pages), totalItems: parseInt(d.meta.total_items) }; renderPrograms(); } });
    }

    function renderPrograms() {
      var html = '';
      if (!programs.length) { html = '<p class="wp-desa-empty-state">Belum ada program bantuan.</p>'; }
      else {
        $.each(programs, function (_, p) { html += buildProgramCard(p); });
      }
      $programGrid.html(html);
    }

    function buildProgramCard(p) {
      return '<div class="wp-desa-stat-card">' +
        '<h4>' + escapeHtml(p.name) + '</h4>' +
        '<p>' + escapeHtml(p.description) + '</p>' +
        '<div>' + escapeHtml(p.origin) + ' | ' + escapeHtml(p.year) + '</div>' +
        '<div>Kuota: ' + (p.quota || 0) + ' | Dana: ' + formatRupiah(p.amount_per_recipient) + '</div>' +
        '<button class="wp-desa-btn wp-desa-btn-primary btn-view-aid-recipients" data-id="' + p.id + '">Lihat Penerima</button> ' +
        '<button class="wp-desa-btn wp-desa-btn-secondary btn-edit-aid" data-id="' + p.id + '">Edit</button> ' +
        '<button class="wp-desa-btn wp-desa-btn-danger btn-delete-aid" data-id="' + p.id + '">Hapus</button></div>';
    }

    $wrap.on('click', '.btn-view-aid-recipients', function () {
      var id = $(this).data('id');
      activeProgram = programs.find(function (p) { return p.id === id; });
      $.ajax({ url: baseUrl + '/' + id + '/recipients', headers: { 'X-WP-Nonce': wpNonce }, success: function (d) { recipients = Array.isArray(d.data) ? d.data : (Array.isArray(d) ? d : []); renderRecipients(); } });
    });

    function renderRecipients() {
      var html = '<h3>Penerima: ' + (activeProgram ? escapeHtml(activeProgram.name) : '') + '</h3><table class="wp-desa-table"><thead><tr><th>NIK</th><th>Nama</th><th>Alamat</th><th>Status</th></tr></thead><tbody>';
      if (!recipients.length) { html += '<tr><td colspan="4" class="wp-desa-empty-state">Belum ada penerima.</td></tr>'; }
      else {
        $.each(recipients, function (_, r) { html += '<tr><td>' + escapeHtml(r.nik) + '</td><td>' + escapeHtml(r.nama_lengkap) + '</td><td>' + escapeHtml(r.alamat) + '</td><td>' + (r.status || '-') + '</td></tr>'; });
      }
      html += '</tbody></table><button class="wp-desa-btn wp-desa-btn-secondary btn-back-aid">Kembali</button>';
      $recipientsPanel.html(html).show();
      $programGrid.hide();
    }

    $wrap.on('click', '.btn-back-aid', function () { $recipientsPanel.hide(); $programGrid.show(); });
    $wrap.on('click', '.btn-delete-aid', function () { if (!confirm('Hapus program ini?')) return; $.ajax({ url: baseUrl + '/' + $(this).data('id'), method: 'DELETE', headers: { 'X-WP-Nonce': wpNonce }, success: function () { fetchPrograms(); showToast('Program dihapus.'); } }); });
  }

  // ====================================================================
  // Boot
  // ====================================================================
  $(function () {
    // Detect which page we're on and init the right component
    var h1 = $('.wrap h1').first().text().trim();

    // Dashboard: has wp-desa-hero
    if ($('.wp-desa-hero').length) initDashboard();

    // Settings: has wp-desa-toast and identitas tab
    if ($('.wp-desa-card-settings').length) initSettingsNotif();

    // Statistik Penduduk
    if (h1.indexOf('Statistik Penduduk') >= 0) initResidentsStats();

    // Data Penduduk
    if (h1.indexOf('Data Penduduk') >= 0) initResidents();

    // Kartu Keluarga
    if (h1.indexOf('Kartu Keluarga') >= 0) initKK();

    // Aspirasi / Aduan
    if (h1.indexOf('Aspirasi') >= 0 || h1.indexOf('Aduan') >= 0) initComplaints();

    // Layanan Surat
    if (h1.indexOf('Layanan Surat') >= 0 || h1.indexOf('Surat') >= 0) initLetters();

    // Keuangan Desa
    if (h1.indexOf('Keuangan Desa') >= 0 || h1.indexOf('Keuangan') >= 0) initFinances();

    // Bantuan
    if (h1.indexOf('Bantuan') >= 0) initAid();
  });

})(jQuery, window, document);