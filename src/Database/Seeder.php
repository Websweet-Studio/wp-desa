<?php

namespace WpDesa\Database;

class Seeder {
    public static function run($count = 100) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'desa_residents';

        // Ensure table exists
        \WpDesa\Database\Activator::activate();

        $first_names = ['Budi', 'Siti', 'Agus', 'Dewi', 'Rudi', 'Sri', 'Joko', 'Rina', 'Andi', 'Lina', 'Eko', 'Yani', 'Bambang', 'Nur', 'Iwan', 'Wati', 'Hendra', 'Ratna', 'Yudi', 'Sari'];
        $last_names = ['Santoso', 'Wijaya', 'Saputra', 'Lestari', 'Hidayat', 'Wahyuni', 'Pratama', 'Utami', 'Nugroho', 'Pertiwi', 'Kusuma', 'Rahmawati', 'Setiawan', 'Susanti', 'Purnomo', 'Indah', 'Gunawan', 'Suryani', 'Wibowo', 'Mulyani'];
        $cities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar', 'Palembang', 'Depok', 'Tangerang', 'Bekasi', 'Yogyakarta', 'Malang', 'Solo', 'Denpasar', 'Padang'];
        $jobs = ['PNS', 'Wiraswasta', 'Petani', 'Buruh', 'Guru', 'Dokter', 'Pedagang', 'Karyawan Swasta', 'Mahasiswa', 'Ibu Rumah Tangga', 'Sopir', 'Nelayan'];
        $marital_statuses = ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'];

        $inserted = 0;
        $duplicate_attempts = 0;
        $max_duplicate_attempts = 50;

        for ($i = 0; $i < $count; $i++) {
            $nik = self::generate_nik();
            $no_kk = self::generate_nik();

            // Check uniqueness
            if ($wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE nik = %s", $nik))) {
                $duplicate_attempts++;
                if ($duplicate_attempts >= $max_duplicate_attempts) {
                    break; // Too many duplicates, table likely full
                }
                $i--;
                continue;
            }
            $duplicate_attempts = 0; // Reset on success

            $gender = rand(0, 1) ? 'Laki-laki' : 'Perempuan';
            $name = $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];

            $data = [
                'nik' => $nik,
                'no_kk' => $no_kk,
                'nama_lengkap' => $name,
                'jenis_kelamin' => $gender,
                'tempat_lahir' => $cities[array_rand($cities)],
                'tanggal_lahir' => date('Y-m-d', rand(strtotime('1950-01-01'), strtotime('2005-12-31'))),
                'alamat' => 'Jl. ' . $last_names[array_rand($last_names)] . ' No. ' . rand(1, 999) . ', RT ' . sprintf('%03d', rand(1, 20)) . '/RW ' . sprintf('%03d', rand(1, 20)),
                'status_perkawinan' => $marital_statuses[array_rand($marital_statuses)],
                'pekerjaan' => $jobs[array_rand($jobs)],
                'created_at' => current_time('mysql'),
            ];

            if ($wpdb->insert($table_name, $data)) {
                $inserted++;
            }
        }

        // Seed letters
        self::seed_letters(intval($count / 2));

        // Seed Complaints
        self::seed_complaints(intval($count / 4));

        // Seed Finances
        self::seed_finances(intval($count / 2));

        // Seed Aid
        self::seed_aid(intval($count / 2));

        // Seed Perangkat Desa
        self::seed_perangkat(max(5, intval($count / 5)));

        // Seed CPTs
        self::seed_potensi(max(3, intval($count / 5)));
        self::seed_umkm(max(3, intval($count / 5)));
        self::seed_produk_hukum(max(3, intval($count / 4)));
        self::seed_berita(max(5, intval($count / 3)));
        self::seed_agenda(max(3, intval($count / 4)));
        self::seed_galeri(max(3, intval($count / 5)));
        self::seed_wisata(max(3, intval($count / 5)));

        return $inserted;
    }

    public static function seed_perangkat($count = 10) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_perangkat';

        \WpDesa\Database\Activator::activate();

        $jabatans = [
            'Kepala Desa', 'Sekretaris Desa', 'Kaur Tata Usaha', 'Kaur Keuangan',
            'Kaur Perencanaan', 'Kasi Pemerintahan', 'Kasi Kesejahteraan',
            'Kasi Pelayanan', 'Kadus I', 'Kadus II', 'Kadus III',
            'Kadus IV', 'Ketua BPD', 'Wakil Ketua BPD', 'Sekretaris BPD',
            'Anggota BPD', 'Ketua LPMD', 'Kepala Dusun',
        ];

        $first_names = ['Budi', 'Siti', 'Agus', 'Dewi', 'Rudi', 'Sri', 'Joko', 'Rina', 'Andi', 'Lina', 'Eko', 'Yani', 'Bambang', 'Nur', 'Iwan', 'Wati', 'Hendra', 'Ratna', 'Yudi', 'Sari'];
        $last_names = ['Santoso', 'Wijaya', 'Saputra', 'Lestari', 'Hidayat', 'Wahyuni', 'Pratama', 'Utami', 'Nugroho', 'Pertiwi', 'Kusuma', 'Rahmawati', 'Setiawan', 'Susanti', 'Purnomo', 'Indah', 'Gunawan', 'Suryani', 'Wibowo', 'Mulyani'];

        $inserted = 0;
        for ($i = 0; $i < min($count, count($jabatans)); $i++) {
            $name = $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];
            $jabatan = $jabatans[$i];

            $nip = sprintf('%08d', rand(11111111, 99999999)) . ' ' . sprintf('%06d', rand(111111, 999999));

            $urutan = $i + 1;
            $parent_id = 0;
            if ($i > 0) {
                $parent_id = 1; // Semua dibawah Kepala Desa (id=1)
            }

            $wpdb->insert($table, [
                'nama' => $name,
                'jabatan' => $jabatan,
                'nip' => $nip,
                'foto' => '',
                'parent_id' => $parent_id,
                'urutan' => $urutan,
                'created_at' => current_time('mysql'),
            ]);
            $inserted++;
        }

        return $inserted;
    }

    public static function seed_potensi($count = 10) {
        if (!post_type_exists('desa_potensi')) {
            (new \WpDesa\Core\PostTypes())->register_potensi_desa();
        }

        $categories = ['Pertanian', 'Peternakan', 'Perikanan', 'Pariwisata desa'];
        $titles = [
            'Lahan Padi Organik', 'Peternakan Sapi Perah', 'Tambak Ikan Lele',
            'Wisata Air Terjun', 'Kebun Sayur Hidroponik', 'Sentra Kerajinan Bambu',
            'Perkebunan Kopi Arabika', 'Budidaya Jamur Tiram', 'Ekowisata Mangrove',
            'Lahan Jagung Manis', 'Peternakan Ayam Kampung', 'Budidaya Ikan Nila',
            'Desa Wisata Batik', 'Kebun Buah Naga', 'Tambak Udang Vaname',
        ];
        $descriptions = [
            'Potensi unggulan desa yang dikelola secara berkelanjutan.',
            'Dikembangkan sejak tahun 2010 dan menjadi sumber ekonomi utama warga.',
            'Bekerja sama dengan dinas terkait untuk pengembangan lebih lanjut.',
            'Telah mendapatkan bantuan dari pemerintah provinsi.',
        ];

        $inserted = 0;
        for ($i = 0; $i < $count; $i++) {
            $title = $titles[$i % count($titles)] . ' #' . ($i + 1);
            $post_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => '<p>' . $descriptions[array_rand($descriptions)] . '</p><p>Lokasi di ' . ['Dusun A', 'Dusun B', 'Dusun C', 'Dusun D'][array_rand(['Dusun A', 'Dusun B', 'Dusun C', 'Dusun D'])] . ', desa kami.</p>',
                'post_status' => 'publish',
                'post_type' => 'desa_potensi',
                'post_date' => date('Y-m-d H:i:s', rand(strtotime('-1 year'), time())),
                'post_excerpt' => $descriptions[array_rand($descriptions)],
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                wp_set_object_terms($post_id, $categories[$i % count($categories)], 'desa_potensi_cat', true);
                $inserted++;
            }
        }

        return $inserted;
    }

    public static function seed_umkm($count = 10) {
        if (!post_type_exists('desa_umkm')) {
            (new \WpDesa\Core\PostTypes())->register_umkm_desa();
        }

        $categories = ['Kuliner', 'Fashion', 'Kerajinan', 'Pertanian', 'Jasa'];
        $products = [
            'Kripik Pisang', 'Tahu Baxo', 'Batik Tulis', 'Anyaman Bambu',
            'Kopi Bubuk', 'Abon Sapi', 'Kerupuk Udang', 'Syal Batik',
            'Tas Rotan', 'Lumpia Basah', 'Wedang Jahe', 'Bros Sulam',
            'Lumpia Basah', 'Sambal Pecel', 'Keripik Singkong',
        ];
        $phones = ['081234567890', '081298765432', '085611223344', '087788990011', '082134567890'];

        $inserted = 0;
        for ($i = 0; $i < $count; $i++) {
            $product = $products[array_rand($products)];
            $title = $product . ' - ' . ['Maju Jaya', 'Berkah Abadi', 'Karya Mandiri', 'Sejahtera', 'Makmur Sentosa'][array_rand(['Maju Jaya', 'Berkah Abadi', 'Karya Mandiri', 'Sejahtera', 'Makmur Sentosa'])];

            $post_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => '<p>Produk unggulan ' . $product . ' dari desa kami. Dibuat dengan bahan-bahan pilihan dan berkualitas tinggi.</p><p>Telah dipasarkan hingga ke luar kota.</p>',
                'post_status' => 'publish',
                'post_type' => 'desa_umkm',
                'post_date' => date('Y-m-d H:i:s', rand(strtotime('-1 year'), time())),
                'post_excerpt' => 'Produk ' . $product . ' berkualitas tinggi dari desa.',
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_desa_umkm_phone', $phones[array_rand($phones)]);
                update_post_meta($post_id, '_desa_umkm_location', 'Jl. ' . ['Merdeka', 'Sudirman', 'Ahmad Yani', 'Diponegoro', 'Pahlawan'][array_rand(['Merdeka', 'Sudirman', 'Ahmad Yani', 'Diponegoro', 'Pahlawan'])] . ' No. ' . rand(1, 100) . ', ' . ['RT 01/RW 01', 'RT 02/RW 01', 'RT 03/RW 02', 'RT 01/RW 03'][array_rand(['RT 01/RW 01', 'RT 02/RW 01', 'RT 03/RW 02', 'RT 01/RW 03'])]);
                update_post_meta($post_id, '_desa_umkm_gallery', '');
                wp_set_object_terms($post_id, $categories[array_rand($categories)], 'desa_umkm_cat', true);
                $inserted++;
            }
        }

        return $inserted;
    }

    public static function seed_produk_hukum($count = 10) {
        if (!post_type_exists('desa_produk_hukum')) {
            (new \WpDesa\Core\PostTypes())->register_produk_hukum();
        }

        $categories = ['Peraturan Desa', 'Keputusan Kepala Desa', 'Peraturan Bersama', 'Surat Edaran'];
        $titles = [
            'Peraturan Desa tentang Pengelolaan Sampah',
            'Peraturan Desa tentang Keamanan Lingkungan',
            'Keputusan Kepala Desa tentang Tim Pengelola Kegiatan',
            'Peraturan Desa tentang Retribusi Pasar Desa',
            'Keputusan Kepala Desa tentang Penetapan Kader Posyandu',
            'Peraturan Desa tentang Pengelolaan BUMDes',
            'Surat Edaran tentang Tertib Administrasi',
            'Peraturan Desa tentang Izin Mendirikan Bangunan',
            'Keputusan Kepala Desa tentang Pembentukan Panitia',
            'Peraturan Desa tentang Pajak Bumi dan Bangunan',
        ];

        $inserted = 0;
        for ($i = 0; $i < min($count, count($titles)); $i++) {
            $post_id = wp_insert_post([
                'post_title' => $titles[$i],
                'post_content' => '<p>' . $titles[$i] . ' tahun ' . date('Y') . '.</p><p>Ditetapkan di desa pada tanggal ' . rand(1, 28) . ' ' . ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][array_rand(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'])] . ' ' . date('Y') . '.</p>',
                'post_status' => 'publish',
                'post_type' => 'desa_produk_hukum',
                'post_date' => date('Y-m-d H:i:s', rand(strtotime('-1 year'), time())),
                'post_excerpt' => substr($titles[$i], 0, 100),
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                wp_set_object_terms($post_id, $categories[$i % count($categories)], 'desa_produk_hukum_cat', true);
                $inserted++;
            }
        }

        return $inserted;
    }

    public static function seed_berita($count = 10) {
        $categories = ['Kegiatan Desa', 'Pembangunan', 'Pengumuman', 'Kesehatan', 'Pendidikan'];
        $titles = [
            'Kegiatan Gotong Royong Bulanan',
            'Pembangunan Jalan Desa Tahap II',
            'Pengumuman Penerimaan BLT Dana Desa',
            'Pelaksanaan Posyandu Bulanan',
            'Sosialisasi Bahaya Narkoba',
            'Peresmian Gedung Serbaguna',
            'Pelatihan UMKM Digital',
            'Kegiatan Kerja Bakti Lingkungan',
            'Penyuluhan Pertanian Organik',
            'Pembagian Bantuan Sembako',
            'Musyawarah Perencanaan Pembangunan',
            'Lomba 17 Agustusan Tingkat Desa',
        ];
        $contents = [
            '<p>Kegiatan gotong royong bulan ini dilaksanakan di Dusun A dan diikuti oleh seluruh warga.</p><p>Alhamdulillah kegiatan berjalan lancar dan penuh kekeluargaan.</p>',
            '<p>Pembangunan jalan desa tahap II telah dimulai. Proyek ini didanai dari Dana Desa tahun berjalan.</p><p>Target penyelesaian dalam 3 bulan ke depan.</p>',
            '<p>Pemerintah desa mengumumkan penerima BLT Dana Desa tahun ini sebanyak ' . rand(100, 300) . ' KK.</p><p>Pembayaran dilakukan setiap bulan melalui rekening masing-masing.</p>',
        ];

        $inserted = 0;
        for ($i = 0; $i < $count; $i++) {
            $post_id = wp_insert_post([
                'post_title' => $titles[$i % count($titles)],
                'post_content' => $contents[array_rand($contents)],
                'post_status' => 'publish',
                'post_type' => 'post',
                'post_date' => date('Y-m-d H:i:s', rand(strtotime('-6 months'), time())),
                'post_excerpt' => 'Berita terbaru dari desa kami.',
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                wp_set_object_terms($post_id, $categories[$i % count($categories)], 'category', true);
                $inserted++;
            }
        }

        return $inserted;
    }

    public static function seed_agenda($count = 10) {
        if (!post_type_exists('desa_agenda')) {
            (new \WpDesa\Core\PostTypes())->register_agenda();
        }

        $categories = ['Rapat', 'Kegiatan', 'Acara', 'Pelatihan'];
        $titles = [
            'Rapat Musyawarah Desa',
            'Kegiatan Posyandu Balita',
            'Sosialisasi Program Keluarga Harapan',
            'Pelatihan Pembuatan Pupuk Organik',
            'Acara Peringatan Hari Kemerdekaan',
            'Rapat Koordinasi BPD',
            'Pelatihan Digital Marketing UMKM',
            'Rapat Perencanaan Pembangunan',
            'Acara Halal Bihalal',
            'Kegiatan Kerja Bakti',
        ];
        $locations = ['Kantor Desa', 'Balai Dusun A', 'Balai Dusun B', 'Lapangan Desa', 'Aula Serbaguna'];

        $inserted = 0;
        for ($i = 0; $i < $count; $i++) {
            $start = rand(strtotime('today'), strtotime('+3 months'));
            $end = $start + rand(3600, 86400); // 1 hour to 1 day later

            $post_id = wp_insert_post([
                'post_title' => $titles[$i % count($titles)],
                'post_content' => '<p>' . $titles[$i % count($titles)] . ' akan dilaksanakan pada:</p><p>Tanggal: ' . date('d F Y', $start) . '<br>Waktu: ' . date('H:i', $start) . ' - ' . date('H:i', $end) . '<br>Lokasi: ' . $locations[array_rand($locations)] . '</p>',
                'post_status' => 'publish',
                'post_type' => 'desa_agenda',
                'post_date' => date('Y-m-d H:i:s', rand(strtotime('-1 month'), time())),
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_desa_agenda_date', date('Y-m-d', $start));
                update_post_meta($post_id, '_desa_agenda_time', date('H:i', $start));
                update_post_meta($post_id, '_desa_agenda_location', $locations[array_rand($locations)]);
                update_post_meta($post_id, '_desa_agenda_end_date', date('Y-m-d', $end));
                wp_set_object_terms($post_id, $categories[$i % count($categories)], 'desa_agenda_cat', true);
                $inserted++;
            }
        }

        return $inserted;
    }

    public static function seed_galeri($count = 10) {
        if (!post_type_exists('desa_galeri')) {
            (new \WpDesa\Core\PostTypes())->register_galeri();
        }

        $categories = ['Kegiatan', 'Wisata', 'Pembangunan', 'Kebudayaan'];
        $titles = [
            'Dokumentasi Gotong Royong',
            'Foto Wisata Desa',
            'Kegiatan Pembangunan Jalan',
            'Pentas Seni Budaya',
            'Kegiatan Posyandu',
            'Panen Raya Pertanian',
            'Lomba 17 Agustusan',
            'Kunjungan Dinas',
            'Pelatihan Warga',
            'Musyawarah Desa',
        ];

        $inserted = 0;
        for ($i = 0; $i < $count; $i++) {
            $post_id = wp_insert_post([
                'post_title' => $titles[$i % count($titles)],
                'post_content' => '<p>Galeri foto ' . $titles[$i % count($titles)] . '.</p>',
                'post_status' => 'publish',
                'post_type' => 'desa_galeri',
                'post_date' => date('Y-m-d H:i:s', rand(strtotime('-1 year'), time())),
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_desa_galeri_images', '');
                update_post_meta($post_id, '_desa_galeri_type', ['foto', 'video'][array_rand(['foto', 'video'])]);
                wp_set_object_terms($post_id, $categories[$i % count($categories)], 'desa_galeri_cat', true);
                $inserted++;
            }
        }

        return $inserted;
    }

    public static function seed_wisata($count = 10) {
        if (!post_type_exists('desa_wisata')) {
            (new \WpDesa\Core\PostTypes())->register_wisata();
        }

        $categories = ['Wisata Alam', 'Wisata Budaya', 'Wisata Kuliner', 'Wisata Religi'];
        $titles = [
            'Air Terjun Curug Indah',
            'Desa Wisata Batik Tulis',
            'Bukit Panorama Desa',
            'Sentra Kuliner Tradisional',
            'Makam Keramat Desa',
            'Goa Alam Bersejarah',
            'Pasar Tradisional Desa',
            'Sungai Wisata Keliling',
            'Perbukitan Hutan Pinus',
            'Taman Edukasi Pertanian',
        ];
        $locations = ['Dusun A', 'Dusun B', 'Dusun C', 'Dusun D'];
        $phones = ['081234567890', '081298765432', '085611223344'];

        $inserted = 0;
        for ($i = 0; $i < $count; $i++) {
            $loc = $locations[array_rand($locations)];
            $post_id = wp_insert_post([
                'post_title' => $titles[$i % count($titles)],
                'post_content' => '<p>' . $titles[$i % count($titles)] . ' terletak di ' . $loc . ', desa kami.</p><p>Tempat ini menjadi destinasi favorit wisatawan lokal maupun luar kota.</p><p>Jam buka: 08.00 - 17.00 WIB setiap hari.</p>',
                'post_status' => 'publish',
                'post_type' => 'desa_wisata',
                'post_date' => date('Y-m-d H:i:s', rand(strtotime('-1 year'), time())),
                'post_excerpt' => 'Destinasi wisata ' . $titles[$i % count($titles)] . ' di ' . $loc . '.',
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_desa_wisata_location', $loc);
                update_post_meta($post_id, '_desa_wisata_address', 'Jl. ' . ['Wisata', 'Raya Desa', 'Pariwisata', 'Alternatif'][array_rand(['Wisata', 'Raya Desa', 'Pariwisata', 'Alternatif'])] . ' No. ' . rand(1, 50) . ', ' . $loc);
                update_post_meta($post_id, '_desa_wisata_phone', $phones[array_rand($phones)]);
                wp_set_object_terms($post_id, $categories[$i % count($categories)], 'desa_wisata_cat', true);
                $inserted++;
            }
        }

        return $inserted;
    }

    public static function seed_aid($count = 50) {
        global $wpdb;

        \WpDesa\Database\Activator::activate();

        $table_programs = $wpdb->prefix . 'desa_programs';
        $table_recipients = $wpdb->prefix . 'desa_program_recipients';
        $table_residents = $wpdb->prefix . 'desa_residents';

        $year = date('Y');

        // 1. Create Programs
        $programs = [
            ['name' => 'BLT Dana Desa ' . $year, 'origin' => 'Dana Desa', 'amount' => 300000, 'quota' => 100],
            ['name' => 'PKH (Program Keluarga Harapan)', 'origin' => 'Kemensos', 'amount' => 750000, 'quota' => 50],
            ['name' => 'Bantuan UMKM', 'origin' => 'Kemenkop', 'amount' => 2400000, 'quota' => 30],
            ['name' => 'Bantuan Sembako', 'origin' => 'Pemda', 'amount' => 200000, 'quota' => 150]
        ];

        $program_ids = [];
        foreach ($programs as $prog) {
            $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_programs WHERE name = %s", $prog['name']));
            if ($existing) {
                $program_ids[] = $existing;
                continue;
            }

            $wpdb->insert($table_programs, [
                'name' => $prog['name'],
                'description' => 'Bantuan ' . $prog['name'],
                'origin' => $prog['origin'],
                'year' => $year,
                'status' => 'active',
                'quota' => $prog['quota'],
                'amount_per_recipient' => $prog['amount'],
                'created_at' => current_time('mysql')
            ]);
            $program_ids[] = $wpdb->insert_id;
        }

        // 2. Add Recipients
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_residents");
        if ($total === 0) return;
        $count = min($count, $total);
        $offset = rand(0, max(0, $total - $count));
        $residents = $wpdb->get_col($wpdb->prepare("SELECT id FROM $table_residents ORDER BY id LIMIT %d OFFSET %d", $count, $offset));
        if (empty($residents)) return;

        $statuses = ['pending', 'approved', 'rejected', 'distributed'];

        foreach ($residents as $resident_id) {
            $program_id = $program_ids[array_rand($program_ids)];

            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_recipients WHERE program_id = %d AND resident_id = %d",
                $program_id, $resident_id
            ));
            if ($exists) continue;

            $status = $statuses[array_rand($statuses)];
            $distributed_at = ($status === 'distributed') ? current_time('mysql') : null;

            $wpdb->insert($table_recipients, [
                'program_id' => $program_id,
                'resident_id' => $resident_id,
                'status' => $status,
                'distributed_at' => $distributed_at,
                'created_at' => current_time('mysql')
            ]);
        }
    }

    public static function seed_letters($count = 50) {
        global $wpdb;

        \WpDesa\Database\Activator::activate();

        $table_letters = $wpdb->prefix . 'desa_letters';
        $table_residents = $wpdb->prefix . 'desa_residents';
        $table_types = $wpdb->prefix . 'desa_letter_types';

        // Get some residents
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_residents");
        if ($total === 0) return 0;
        $count = min($count, $total);
        $offset = rand(0, max(0, $total - $count));
        $residents = $wpdb->get_results($wpdb->prepare("SELECT nik, nama_lengkap FROM $table_residents ORDER BY id LIMIT %d OFFSET %d", $count, $offset));
        if (empty($residents)) return 0;

        // Get letter types
        $types = $wpdb->get_col("SELECT id FROM $table_types");
        if (empty($types)) return 0;

        $statuses = ['pending', 'processed', 'completed', 'rejected'];
        $details_list = [
            'Untuk persyaratan melamar pekerjaan',
            'Untuk mengurus rekening bank',
            'Untuk pendaftaran sekolah anak',
            'Untuk keperluan administrasi nikah',
            'Untuk pengurusan BPJS',
            'Untuk pembuatan KTP baru',
            'Untuk pindah domisili'
        ];

        $inserted = 0;

        foreach ($residents as $resident) {
            $tracking_code = 'LTR-' . strtoupper(wp_generate_password(8, false));
            $created_at = date('Y-m-d H:i:s', rand(strtotime('-3 months'), time()));

            $data = [
                'tracking_code' => $tracking_code,
                'letter_type_id' => $types[array_rand($types)],
                'nik' => $resident->nik,
                'name' => $resident->nama_lengkap,
                'phone' => '08' . rand(100000000, 999999999),
                'details' => $details_list[array_rand($details_list)],
                'status' => $statuses[array_rand($statuses)],
                'created_at' => $created_at,
                'updated_at' => $created_at
            ];

            if ($wpdb->insert($table_letters, $data)) {
                $inserted++;
            }
        }

        return $inserted;
    }

    public static function seed_complaints($count = 20) {
        global $wpdb;
        $table_complaints = $wpdb->prefix . 'desa_complaints';

        \WpDesa\Database\Activator::activate();

        $names = ['Anonim', 'Budi Santoso', 'Siti Aminah', 'Warga Peduli', 'Ahmad Dani', 'Rina Nose', 'Joko Anwar', ''];
        $categories = ['Infrastruktur', 'Pelayanan Publik', 'Keamanan', 'Kebersihan', 'Lainnya'];
        $subjects = [
            'Jalan berlubang di RT 05',
            'Lampu jalan mati sudah seminggu',
            'Sampah menumpuk di sungai',
            'Pelayanan kantor desa lambat',
            'Ada orang mencurigakan tiap malam',
            'Saluran air mampet',
            'Permohonan fogging nyamuk'
        ];
        $descriptions = [
            'Mohon segera diperbaiki karena membahayakan pengendara motor.',
            'Tolong diganti lampunya pak, gelap sekali kalau malam.',
            'Baunya sangat menyengat dan mengganggu warga sekitar.',
            'Saya antri dari pagi tapi baru dilayani siang hari.',
            'Sering nongkrong di pos ronda tapi bukan warga sini.',
            'Kalau hujan air meluap ke jalan.',
            'Banyak warga yang terkena demam berdarah.'
        ];
        $statuses = ['pending', 'in_progress', 'resolved', 'rejected'];

        $inserted = 0;

        for ($i = 0; $i < $count; $i++) {
            $tracking_code = 'ADU-' . strtoupper(wp_generate_password(6, false));
            $created_at = date('Y-m-d H:i:s', rand(strtotime('-3 months'), time()));
            $status = $statuses[array_rand($statuses)];

            $data = [
                'tracking_code' => $tracking_code,
                'reporter_name' => $names[array_rand($names)] ?: 'Anonim',
                'reporter_contact' => '08' . rand(100000000, 999999999),
                'category' => $categories[array_rand($categories)],
                'subject' => $subjects[array_rand($subjects)],
                'description' => $descriptions[array_rand($descriptions)],
                'photo_url' => '',
                'status' => $status,
                'response' => ($status == 'resolved' || $status == 'rejected') ? 'Terima kasih atas laporannya. Akan segera kami tindak lanjuti.' : '',
                'created_at' => $created_at,
                'updated_at' => $created_at
            ];

            if ($wpdb->insert($table_complaints, $data)) {
                $inserted++;
            }
        }

        return $inserted;
    }

    public static function seed_finances($count = 50) {
        global $wpdb;
        $table_finances = $wpdb->prefix . 'desa_finances';

        \WpDesa\Database\Activator::activate();

        $income_categories = ['Dana Desa', 'Alokasi Dana Desa', 'Bantuan Keuangan Provinsi', 'Bantuan Keuangan Kabupaten', 'Pendapatan Asli Desa (PAD)', 'Lain-lain Pendapatan'];
        $expense_categories = ['Bidang Penyelenggaraan Pemerintahan', 'Bidang Pelaksanaan Pembangunan', 'Bidang Pembinaan Kemasyarakatan', 'Bidang Pemberdayaan Masyarakat', 'Bidang Penanggulangan Bencana'];

        $inserted = 0;
        $current_year = (int) date('Y');
        $years = range($current_year - 2, $current_year);
        $per_year = max(1, intval($count / count($years)));

        foreach ($years as $year) {
            for ($i = 0; $i < $per_year; $i++) {
                $type = rand(0, 1) ? 'income' : 'expense';
                $category = $type == 'income' ? $income_categories[array_rand($income_categories)] : $expense_categories[array_rand($expense_categories)];

                $budget = rand(1000000, 500000000);
                $realization = rand(0, $budget);

                $data = [
                    'year' => $year,
                    'type' => $type,
                    'category' => $category,
                    'description' => 'Transaksi ' . $category . ' #' . ($i + 1) . ' tahun ' . $year,
                    'budget_amount' => $budget,
                    'realization_amount' => $realization,
                    'transaction_date' => date('Y-m-d', rand(strtotime($year . '-01-01'), min(strtotime($year . '-12-31'), strtotime('today')))),
                    'created_at' => current_time('mysql'),
                ];

                if ($wpdb->insert($table_finances, $data)) {
                    $inserted++;
                }
            }
        }

        return $inserted;
    }

    private static function generate_nik() {
        // Simple mock NIK generator: 16 digits
        // Format: PPKKCCTGDMMYYSSSS
        $max_attempts = 5;
        for ($attempt = 0; $attempt < $max_attempts; $attempt++) {
            $prov = sprintf('%02d', rand(11, 99));
            $city = sprintf('%02d', rand(1, 99));
            $kec = sprintf('%02d', rand(1, 99));
            $day = rand(1, 31);
            $month = rand(1, 12);
            $year = sprintf('%02d', rand(0, 99));
            $seq = sprintf('%04d', rand(1, 9999));

            // Validate date
            if (checkdate($month, $day, 2000)) { // year doesn't matter for checkdate, use 2000
                return $prov . $city . $kec . sprintf('%02d', $day) . sprintf('%02d', $month) . $year . $seq;
            }
        }

        // Fallback: use safe values
        return $prov . $city . $kec . '15' . '06' . $year . $seq;
    }
}
