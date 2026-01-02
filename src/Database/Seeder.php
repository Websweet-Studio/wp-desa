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
