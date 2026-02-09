<?php

namespace Database\Seeders;

use App\Models\TahunAjar;
use App\Models\Subject;
use App\Models\SchoolLocation;
use App\Models\RombonganBelajar;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Tahun Akademik
        $tahunAjar = TahunAjar::create([
            'nama' => '2023/2024',
            'semester' => 'Ganjil',
            'tanggal_mulai' => '2023-07-17',
            'tanggal_selesai' => '2023-12-22',
            'is_active' => true,
        ]);

        // 2. Buat Subjek
        $subjects = [
            'Matematika',
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'Pemrograman Web',
            'Basis Data'
        ];

        foreach ($subjects as $sub) {
            Subject::create(['nama_mapel' => $sub]);
        }

        // 3. Buat Lokasi Sekolah
        SchoolLocation::create([
            'nama_lokasi' => 'SMK Negeri 1 Contoh',
            'latitude' => -6.917464,  // Example coordinates (Bandung)
            'longitude' => 107.619122,
            'radius_maks' => 50, // 50 meters
        ]);


        // 4. Buat Jurusan
        $jurusan = \App\Models\Jurusan::create([
            'kode_jurusan' => 'RPL',
            'nama_jurusan' => 'Rekayasa Perangkat Lunak'
        ]);

        // 5. Buat Rombel (Kelas)
        // Tetapkan guru secara acak sebagai Wali Kelas
        $teachers = Teacher::all();

        if ($teachers->count() > 0) {
            $classes = ['X RPL 1', 'X RPL 2', 'XI RPL 1'];

            foreach ($classes as $index => $className) {
                // Pastikan kita memiliki cukup guru, atau gunakan guru yang sudah ada jika tidak mencukupi.
                $teacher = $teachers[$index % $teachers->count()];

                RombonganBelajar::create([
                    'nama_rombel' => $className,
                    'jurusan_id' => $jurusan->id, // Add this line
                    'tahun_ajar_id' => $tahunAjar->id,
                    'wali_kelas_id' => $teacher->id,
                ]);
            }
        }
    }
}
