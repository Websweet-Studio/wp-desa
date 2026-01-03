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
        
        for ($i = 0; $i < $count; $i++) {
            $nik = self::generate_nik();
            
            // Check uniqueness
            if ($wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE nik = %s", $nik))) {
                $i--; // Retry
                continue;
            }

            $gender = rand(0, 1) ? 'Laki-laki' : 'Perempuan';
            $name = $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];
            
            $data = [
                'nik' => $nik,
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

        // Also seed letters
        self::seed_letters(intval($count / 2)); // 50% of resident count

        return $inserted;
    }

    public static function seed_letters($count = 50) {
        global $wpdb;
        $table_letters = $wpdb->prefix . 'desa_letters';
        $table_residents = $wpdb->prefix . 'desa_residents';
        $table_types = $wpdb->prefix . 'desa_letter_types';

        // Get some residents
        $residents = $wpdb->get_results("SELECT nik, nama_lengkap FROM $table_residents ORDER BY RAND() LIMIT $count");
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
            $tracking_code = strtoupper(wp_generate_password(8, false));
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
        
        // Ensure table exists
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
                'photo_url' => '', // Dummy photo URL or empty
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

        $year = date('Y');
        $income_categories = ['Dana Desa', 'Alokasi Dana Desa', 'Bantuan Keuangan Provinsi', 'Bantuan Keuangan Kabupaten', 'Pendapatan Asli Desa (PAD)', 'Lain-lain Pendapatan'];
        $expense_categories = ['Bidang Penyelenggaraan Pemerintahan', 'Bidang Pelaksanaan Pembangunan', 'Bidang Pembinaan Kemasyarakatan', 'Bidang Pemberdayaan Masyarakat', 'Bidang Penanggulangan Bencana'];
        
        $inserted = 0;

        for ($i = 0; $i < $count; $i++) {
            $type = rand(0, 1) ? 'income' : 'expense';
            $category = $type == 'income' ? $income_categories[array_rand($income_categories)] : $expense_categories[array_rand($expense_categories)];
            
            $budget = rand(1000000, 500000000);
            $realization = rand(0, $budget);
            
            $data = [
                'year' => $year,
                'type' => $type,
                'category' => $category,
                'description' => 'Transaksi ' . $category . ' #' . ($i + 1),
                'budget_amount' => $budget,
                'realization_amount' => $realization,
                'transaction_date' => date('Y-m-d', rand(strtotime($year . '-01-01'), strtotime($year . '-12-31'))),
                'created_at' => current_time('mysql'),
            ];

            if ($wpdb->insert($table_finances, $data)) {
                $inserted++;
            }
        }

        return $inserted;
    }

    private static function generate_nik() {
        // Simple mock NIK generator: 16 digits
        // PPKKCCTGDMMYYSSSS
        $prov = sprintf('%02d', rand(11, 99));
        $city = sprintf('%02d', rand(1, 99));
        $kec = sprintf('%02d', rand(1, 99));
        $date = sprintf('%02d', rand(1, 31));
        $month = sprintf('%02d', rand(1, 12));
        $year = sprintf('%02d', rand(0, 99));
        $seq = sprintf('%04d', rand(1, 9999));
        
        return $prov . $city . $kec . $date . $month . $year . $seq;
    }
}
