<?php
global $wpdb;
$table_name = $wpdb->prefix . 'desa_finances';
$settings   = get_option('wp_desa_settings', []);
$per_page   = 20;
$view       = isset($_GET['view']) ? $_GET['view'] : 'data';
$action     = isset($_GET['action']) ? $_GET['action'] : 'list';

// ============================================================
// Handle POST: save / update finance record
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_desa_save_finance'])) {
    $id                = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $year              = isset($_POST['year']) ? intval($_POST['year']) : 0;
    $transaction_date  = isset($_POST['transaction_date']) ? sanitize_text_field($_POST['transaction_date']) : '';
    $type              = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
    $category          = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $description       = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
    $budget_amount     = isset($_POST['budget_amount']) ? floatval($_POST['budget_amount']) : 0;
    $realization_amount = isset($_POST['realization_amount']) ? floatval($_POST['realization_amount']) : 0;

    $data = [
        'year'              => $year,
        'transaction_date'  => $transaction_date,
        'type'              => $type,
        'category'          => $category,
        'description'       => $description,
        'budget_amount'     => $budget_amount,
        'realization_amount' => $realization_amount,
    ];

    if ($id > 0) {
        $data['updated_at'] = current_time('mysql');
        $wpdb->update($table_name, $data, ['id' => $id]);
    } else {
        $data['created_at'] = current_time('mysql');
        $wpdb->insert($table_name, $data);
    }

    wp_redirect(admin_url('admin.php?page=wp-desa-keuangan&tab=keuangan&view=data&saved=1'));
    exit;
}

// ============================================================
// Edit mode: fetch record
// ============================================================
$edit_finance = null;
if ($action === 'edit') {
    $edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($edit_id > 0) {
        $edit_finance = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $edit_id));
    }
    if (!$edit_finance) {
        wp_redirect(admin_url('admin.php?page=wp-desa-keuangan&tab=keuangan&view=data'));
        exit;
    }
}

// ============================================================
// List mode: query finances with pagination & filters
// ============================================================
$paged        = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset       = ($paged - 1) * $per_page;
$filter_year  = isset($_GET['filter_year']) ? intval($_GET['filter_year']) : 0;
$filter_type  = isset($_GET['filter_type']) ? sanitize_text_field($_GET['filter_type']) : '';

$conditions = [];
$params     = [];

if ($filter_year > 0) {
    $conditions[] = 'year = %d';
    $params[]     = $filter_year;
}
if (!empty($filter_type)) {
    $conditions[] = 'type = %s';
    $params[]     = $filter_type;
}

$where = '';
if (!empty($conditions)) {
    $where = ' WHERE ' . implode(' AND ', $conditions);
}

if (!empty($conditions)) {
    $total_items = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name$where", $params));
    $all_params  = array_merge($params, [$per_page, $offset]);
    $finances    = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name$where ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $all_params
    ));
} else {
    $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $finances    = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));
}

$total_pages = max(1, ceil($total_items / $per_page));
$years       = $wpdb->get_col("SELECT DISTINCT year FROM $table_name ORDER BY year DESC");

// ============================================================
// Helper functions
// ============================================================
function wp_desa_finance_badge($type)
{
    if ($type === 'income') {
        return '<span class="wp-desa-badge wp-desa-badge-success">Pendapatan</span>';
    }
    return '<span class="wp-desa-badge wp-desa-badge-danger">Belanja</span>';
}

function wp_desa_format_rp($amount)
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}
?>
<div class="wrap wp-desa-wrapper">

    <?php if (isset($_GET['saved']) && $_GET['saved'] == 1): ?>
        <div class="notice notice-success is-dismissible">
            <p>Data berhasil disimpan.</p>
        </div>
    <?php endif; ?>

    <!-- Tabs Navigation -->
    <div class="wp-desa-card wp-desa-mb-20">
        <div class="wp-desa-tabs wp-desa-tab-counts">
            <a href="?page=wp-desa-keuangan&tab=keuangan&view=data"
               class="wp-desa-tab <?php echo $view === 'data' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;"><path d="M3 3v18h18"/><rect x="7" y="7" width="4" height="14" rx="1"/><rect x="13" y="4" width="4" height="17" rx="1"/></svg> Data APBDes
            </a>
            <a href="?page=wp-desa-keuangan&tab=keuangan&view=dashboard"
               class="wp-desa-tab <?php echo $view === 'dashboard' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg> Dashboard & Grafik
            </a>
        </div>
    </div>

    <?php if ($view === 'data'): ?>
        <!-- ======== DATA TAB ======== -->
        <?php if ($action === 'list'): ?>
            <!-- Table View -->
            <div class="wp-desa-card">
                <!-- Filters -->
                <div class="wp-desa-filter-bar">
                    <select class="wp-desa-select wp-desa-select-sm" name="filter_year" onchange="window.location.href='?page=wp-desa-keuangan&tab=keuangan&view=data&filter_year='+this.value+'&filter_type=<?php echo esc_attr($filter_type); ?>'">
                        <option value="0">Semua Tahun</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?php echo (int) $y; ?>" <?php selected($filter_year, $y); ?>><?php echo (int) $y; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="wp-desa-select wp-desa-select-sm" name="filter_type" onchange="window.location.href='?page=wp-desa-keuangan&tab=keuangan&view=data&filter_type='+this.value+'&filter_year=<?php echo (int) $filter_year; ?>'">
                        <option value="">Semua Jenis</option>
                        <option value="income" <?php selected($filter_type, 'income'); ?>>Pendapatan</option>
                        <option value="expense" <?php selected($filter_type, 'expense'); ?>>Belanja</option>
                    </select>
                    <div class="wp-desa-flex-grow"></div>
                    <a href="?page=wp-desa-keuangan&tab=keuangan&view=data&action=add" class="wp-desa-btn wp-desa-btn-primary">
                        <span class="dashicons dashicons-plus-alt2"></span> Tambah Data
                    </a>
                </div>

                <div style="overflow-x:auto">
                    <table class="wp-desa-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tahun</th>
                                <th>Jenis</th>
                                <th>Kategori</th>
                                <th>Uraian</th>
                                <th>Anggaran</th>
                                <th>Realisasi</th>
                                <th class="wp-desa-text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($finances)): ?>
                                <?php foreach ($finances as $f): ?>
                                    <tr>
                                        <td><?php echo esc_html($f->transaction_date); ?></td>
                                        <td><?php echo (int) $f->year; ?></td>
                                        <td><?php echo wp_desa_finance_badge($f->type); ?></td>
                                        <td><?php echo esc_html($f->category); ?></td>
                                        <td><?php echo esc_html($f->description); ?></td>
                                        <td><?php echo wp_desa_format_rp($f->budget_amount); ?></td>
                                        <td><?php echo wp_desa_format_rp($f->realization_amount); ?></td>
                                        <td class="wp-desa-text-right">
                                            <div class="wp-desa-inline-actions-end">
                                                <a href="?page=wp-desa-keuangan&tab=keuangan&view=data&action=edit&id=<?php echo (int) $f->id; ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v2"/><path d="M21.34 15.664a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/><path d="M8 22H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></a>
                                                <button class="wp-desa-btn wp-desa-btn-danger-outline wp-desa-btn-sm btn-delete-finance" data-id="<?php echo (int) $f->id; ?>" title="Hapus"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="wp-desa-empty-state">
                                        <span class="dashicons dashicons-warning"></span>
                                        <div class="wp-desa-mt-8">Tidak ada data keuangan.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_items > $per_page): ?>
                    <div class="wp-desa-pagination">
                        <div class="wp-desa-pagination-info">
                            Menampilkan <?php echo $offset + 1; ?>–<?php echo min($offset + $per_page, $total_items); ?> dari <?php echo $total_items; ?> data
                        </div>
                        <div class="wp-desa-pagination-controls">
                            <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged <= 1 ? 'wp-desa-btn-disabled' : ''; ?>"
                               href="?page=wp-desa-keuangan&tab=keuangan&view=data&filter_year=<?php echo (int) $filter_year; ?>&filter_type=<?php echo esc_attr($filter_type); ?><?php echo $paged > 2 ? '&paged=' . ($paged - 1) : ''; ?>">
                                <span class="dashicons dashicons-arrow-left-alt2"></span>
                            </a>
                            <span class="wp-desa-pagination-page">Halaman <?php echo $paged; ?> dari <?php echo $total_pages; ?></span>
                            <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged >= $total_pages ? 'wp-desa-btn-disabled' : ''; ?>"
                               href="?page=wp-desa-keuangan&tab=keuangan&view=data&filter_year=<?php echo (int) $filter_year; ?>&filter_type=<?php echo esc_attr($filter_type); ?>&paged=<?php echo $paged + 1; ?>">
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- ======== FORM VIEW (add / edit) ======== -->
            <div class="wp-desa-card">
                <form method="post">
                    <input type="hidden" name="wp_desa_save_finance" value="1">
                    <input type="hidden" name="id" value="<?php echo $edit_finance ? (int) $edit_finance->id : 0; ?>">

                    <div class="wp-desa-form-grid">
                        <div class="wp-desa-grid-2-16">
                            <div class="wp-desa-form-group">
                                <label class="wp-desa-label" for="finance-year">Tahun Anggaran <span class="wp-desa-req">*</span></label>
                                <input type="number" name="year" id="finance-year" required class="wp-desa-input"
                                    value="<?php echo $edit_finance ? esc_attr($edit_finance->year) : date('Y'); ?>">
                            </div>
                            <div class="wp-desa-form-group">
                                <label class="wp-desa-label" for="finance-date">Tanggal Transaksi <span class="wp-desa-req">*</span></label>
                                <input type="date" name="transaction_date" id="finance-date" required class="wp-desa-input"
                                    value="<?php echo $edit_finance ? esc_attr($edit_finance->transaction_date) : ''; ?>">
                            </div>
                        </div>

                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="finance-type">Jenis Transaksi <span class="wp-desa-req">*</span></label>
                            <select name="type" id="finance-type" required class="wp-desa-select">
                                <option value="income" <?php selected($edit_finance && $edit_finance->type === 'income'); ?>>Pendapatan</option>
                                <option value="expense" <?php selected($edit_finance && $edit_finance->type === 'expense'); ?>>Belanja</option>
                            </select>
                        </div>

                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="finance-category">Kategori <span class="wp-desa-req">*</span></label>
                            <input type="text" name="category" id="finance-category" required class="wp-desa-input"
                                placeholder="Contoh: Dana Desa, ADD, Belanja Pegawai"
                                value="<?php echo $edit_finance ? esc_attr($edit_finance->category) : ''; ?>">
                        </div>

                        <div class="wp-desa-form-group full-width">
                            <label class="wp-desa-label" for="finance-desc">Uraian / Keterangan</label>
                            <textarea name="description" id="finance-desc" class="wp-desa-textarea" rows="3"
                                placeholder="Deskripsi detail transaksi..."><?php echo $edit_finance ? esc_textarea($edit_finance->description) : ''; ?></textarea>
                        </div>

                        <div class="wp-desa-grid-2-16">
                            <div class="wp-desa-form-group">
                                <label class="wp-desa-label" for="finance-budget">Jumlah Anggaran (Rp) <span class="wp-desa-req">*</span></label>
                                <input type="number" name="budget_amount" id="finance-budget" required class="wp-desa-input" step="0.01"
                                    value="<?php echo $edit_finance ? esc_attr($edit_finance->budget_amount) : ''; ?>">
                            </div>
                            <div class="wp-desa-form-group">
                                <label class="wp-desa-label" for="finance-realization">Realisasi (Rp) <span class="wp-desa-req">*</span></label>
                                <input type="number" name="realization_amount" id="finance-realization" required class="wp-desa-input" step="0.01"
                                    value="<?php echo $edit_finance ? esc_attr($edit_finance->realization_amount) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="wp-desa-form-actions">
                        <a href="?page=wp-desa-keuangan&tab=keuangan&view=data" class="wp-desa-btn wp-desa-btn-secondary">Batal</a>
                        <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- ======== DASHBOARD TAB ======== -->
        <div class="wp-desa-dashboard">
            <div class="wp-desa-stats-grid">
                <div class="wp-desa-stat-card">
                    <div class="wp-desa-stat-title">Total Pendapatan</div>
                    <div class="wp-desa-stat-value wp-desa-text-green"></div>
                    <div class="wp-desa-stat-desc">Realisasi: <span></span></div>
                </div>
                <div class="wp-desa-stat-card">
                    <div class="wp-desa-stat-title">Total Belanja</div>
                    <div class="wp-desa-stat-value wp-desa-text-red"></div>
                    <div class="wp-desa-stat-desc">Realisasi: <span></span></div>
                </div>
                <div class="wp-desa-stat-card">
                    <div class="wp-desa-stat-title">Surplus/Defisit (Realisasi)</div>
                    <div class="wp-desa-stat-value"></div>
                    <div class="wp-desa-stat-desc">Tahun Anggaran <span></span></div>
                </div>
            </div>

            <div class="wp-desa-grid-2">
                <div class="wp-desa-card wp-desa-card-pad">
                    <h3 class="wp-desa-section-title">Sumber Pendapatan Desa</h3>
                    <canvas id="incomeChart" class="wp-desa-chart"></canvas>
                </div>
                <div class="wp-desa-card wp-desa-card-pad">
                    <h3 class="wp-desa-section-title">Penggunaan Dana (Belanja)</h3>
                    <canvas id="expenseChart" class="wp-desa-chart"></canvas>
                </div>
            </div>
        </div>

<script>
jQuery(function($) {
    function fmt(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    $.getJSON(wpDesaSettings.restBase + '/finances/summary')
        .done(function(res) {
            // Stat cards
            var income = 0, expense = 0;
            $.each(res.totals, function(_, t) {
                if (t.type === 'income') income = parseFloat(t.total_realization);
                if (t.type === 'expense') expense = parseFloat(t.total_realization);
            });

            // Total Pendapatan
            $('.wp-desa-stat-card:eq(0) .wp-desa-stat-value').text(fmt(income));
            $('.wp-desa-stat-card:eq(0) .wp-desa-stat-desc span').text(fmt(income));

            // Total Belanja
            $('.wp-desa-stat-card:eq(1) .wp-desa-stat-value').text(fmt(expense));
            $('.wp-desa-stat-card:eq(1) .wp-desa-stat-desc span').text(fmt(expense));

            // Surplus/Defisit
            var surplus = income - expense;
            var $sd = $('.wp-desa-stat-card:eq(2) .wp-desa-stat-value');
            $sd.text(fmt(Math.abs(surplus))).css('color', surplus >= 0 ? '#1f6b3c' : '#b3262b');
            $('.wp-desa-stat-card:eq(2) .wp-desa-stat-desc span').text(res.year);

            // Income chart
            var ctx1 = document.getElementById('incomeChart');
            if (ctx1 && res.income_sources.length) {
                new Chart(ctx1, {
                    type: 'doughnut',
                    data: {
                        labels: res.income_sources.map(function(s) { return s.category; }),
                        datasets: [{
                            data: res.income_sources.map(function(s) { return parseFloat(s.total); }),
                            backgroundColor: ['#024ad8','#296ef9','#639cff','#9cc0ff','#c9e0fc','#f0f5ff'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true, plugins: { legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } } },
                        cutout: '65%'
                    }
                });
            }

            // Expense chart
            var ctx2 = document.getElementById('expenseChart');
            if (ctx2 && res.expense_sources.length) {
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: res.expense_sources.map(function(s) { return s.category; }),
                        datasets: [{
                            data: res.expense_sources.map(function(s) { return parseFloat(s.total); }),
                            backgroundColor: ['#b3262b','#d65a5e','#e89194','#f2bcbe','#f9d4d2','#fceaea'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true, plugins: { legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } } },
                        cutout: '65%'
                    }
                });
            }
        })
        .fail(function() {
            $('.wp-desa-stat-card .wp-desa-stat-value').text('Error');
        });
});
</script>
    <?php endif; ?>

</div>
