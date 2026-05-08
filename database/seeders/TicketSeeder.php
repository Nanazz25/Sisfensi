<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Masalah Akun & Login',
                'suggested_response' => "Halo! Jika Anda mengalami kendala login, silakan coba reset password melalui menu 'Lupa Password'. Jika masih gagal, pastikan email yang digunakan sudah terdaftar di sistem."
            ],
            [
                'name' => 'Kendala Presensi Wajah',
                'suggested_response' => "Halo! Untuk kendala presensi wajah, pastikan Anda berada di area dengan cahaya yang cukup dan tidak menggunakan masker/kacamata hitam saat proses verifikasi. Jika masalah berlanjut, hubungi admin untuk registrasi ulang wajah."
            ],
            [
                'name' => 'Bug Sistem / Error',
                'suggested_response' => "Terima kasih atas laporannya. Tim teknis kami akan segera memeriksa kendala tersebut. Mohon tunggu informasi selanjutnya."
            ],
            [
                'name' => 'Lainnya',
                'suggested_response' => "Halo! Terima kasih telah menghubungi pusat bantuan. Mohon tunggu sebentar, operator kami akan segera merespon aduan Anda."
            ]
        ];

        foreach ($categories as $category) {
            TicketCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
