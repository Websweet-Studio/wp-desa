<?php
/**
 * Template: Produk Hukum (CPT list view)
 *
 * @package WP_Desa
 */

$per_page   = 20;
$paged      = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

$query_args = [
    'post_type'      => 'desa_produk_hukum',
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'post_status'    => 'any',
];

// Filter by category
if (!empty($_GET['cat'])) {
    $query_args['tax_query'] = [
        [
            'taxonomy' => 'desa_produk_hukum_cat',
            'field'    => 'slug',
            'terms'    => sanitize_key($_GET['cat']),
        ],
    ];
}

$query      = new WP_Query($query_args);
$items      = $query->posts;
$total_items = (int) $query->found_posts;
$total_pages = max(1, (int) $query->max_num_pages);

$categories = get_terms([
    'taxonomy'   => 'desa_produk_hukum_cat',
    'hide_empty' => false,
]);
?>
<div class="wp-desa-wrapper">

    <?php if (isset($_GET['trashed']) || isset($_GET['untrashed']) || isset($_GET['saved'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>Data berhasil diperbarui.</p>
        </div>
    <?php endif; ?>

    <div class="wp-desa-card">
        <!-- Action Bar -->
        <div class="wp-desa-filter-bar" style="display:flex;align-items:center;justify-content:space-between;padding:var(--sp-md);border-bottom:1px solid var(--fog);">
            <div style="display:flex;gap:var(--sp-sm);align-items:center;flex-wrap:wrap;">
                <span class="wp-desa-pagination-info">Total: <?php echo (int) $total_items; ?> produk hukum</span>
                <?php if (!empty($categories) && !is_wp_error($categories)): ?>
                    <select id="wp-desa-filter-cat" style="margin-left:var(--sp-sm);padding:4px 8px;border:1px solid var(--fog);border-radius:var(--radius-sm);font-size:12px;background:#fff;" onchange="if(this.value) location.href='?page=wp-desa-pemerintahan&tab=produk-hukum&cat='+this.value;else location.href='?page=wp-desa-pemerintahan&tab=produk-hukum';">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo esc_attr($cat->slug); ?>" <?php selected(!empty($_GET['cat']) && $_GET['cat'] === $cat->slug); ?>><?php echo esc_html($cat->name); ?> (<?php echo (int) $cat->count; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=desa_produk_hukum')); ?>" class="wp-desa-btn wp-desa-btn-primary" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 .83.18 2 2 0 0 0 .83-.18l8.58-3.9a1 1 0 0 0 0-1.831z"/><path d="M16 17h6"/><path d="M19 14v6"/><path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 .825.178"/><path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l2.116-.962"/></svg> Tambah Produk Hukum
                </a>
            </div>
        </div>

        <div style="overflow-x:auto">
            <table class="wp-desa-table">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php $no = 1 + (($paged - 1) * $per_page); ?>
                        <?php foreach ($items as $post): ?>
                            <?php
                            $post_cats = wp_get_post_terms($post->ID, 'desa_produk_hukum_cat');
                            $cat_name  = !empty($post_cats) && !is_wp_error($post_cats) ? $post_cats[0]->name : '-';
                            $status    = get_post_status_object($post->post_status);
                            $status_label = $status ? $status->label : $post->post_status;
                            $status_class = $post->post_status === 'publish' ? 'success' : ($post->post_status === 'draft' ? 'warning' : 'default');
                            $edit_url     = admin_url('post.php?post=' . $post->ID . '&action=edit');
                            $trash_url    = admin_url('post.php?post=' . $post->ID . '&action=trash');
                            $view_url     = get_permalink($post->ID);
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo esc_html($post->post_title); ?></strong></td>
                                <td><?php echo esc_html($cat_name); ?></td>
                                <td><?php echo get_the_date('d/m/Y', $post->ID); ?></td>
                                <td><span class="wp-desa-badge wp-desa-badge-<?php echo $status_class; ?>"><?php echo esc_html($status_label); ?></span></td>
                                <td style="text-align:right;">
                                    <div class="wp-desa-inline-actions-end">
                                        <a href="<?php echo esc_url($view_url); ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" title="Lihat" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="<?php echo esc_url($edit_url); ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" title="Edit" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v2"/><path d="M21.34 15.664a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/><path d="M8 22H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg>
                                        </a>
                                        <a href="<?php echo esc_url($trash_url); ?>" class="wp-desa-btn wp-desa-btn-danger-outline wp-desa-btn-sm" title="Hapus" onclick="return confirm('Yakin hapus produk hukum ini?')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="wp-desa-empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <div class="wp-desa-mt-8">Belum ada produk hukum. <a href="<?php echo esc_url(admin_url('post-new.php?post_type=desa_produk_hukum')); ?>" target="_blank" style="text-decoration:underline;">Tambah sekarang</a>.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_items > $per_page): ?>
            <div class="wp-desa-pagination">
                <div class="wp-desa-pagination-info">
                    Menampilkan <?php echo (($paged - 1) * $per_page) + 1; ?>–<?php echo min($paged * $per_page, $total_items); ?> dari <?php echo $total_items; ?> data
                </div>
                <div class="wp-desa-pagination-controls">
                    <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged <= 1 ? 'wp-desa-btn-disabled' : ''; ?>" href="?page=wp-desa-pemerintahan&tab=produk-hukum<?php echo $paged > 2 ? '&paged=' . ($paged - 1) : ''; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                    <span class="wp-desa-pagination-page">Halaman <?php echo $paged; ?> dari <?php echo $total_pages; ?></span>
                    <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged >= $total_pages ? 'wp-desa-btn-disabled' : ''; ?>" href="?page=wp-desa-pemerintahan&tab=produk-hukum&paged=<?php echo $paged + 1; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
