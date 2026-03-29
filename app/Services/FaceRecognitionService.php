<?php

namespace App\Services;

use App\Models\PesertaDidik;

class FaceRecognitionService
{
    public function match(array $capturedEmbedding, $threshold = 0.5)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $query = PesertaDidik::whereNotNull('face_embedding');

        // Filter siswa berdasarkan role
        if ($user) {
            // Jika user adalah siswa
            if ($user->role === 'siswa') {
                $query->where('user_id', $user->id);
            } elseif ($user->role === 'guru') {
                // Jika user adalah guru
                $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
                if ($teacher) {
                    // Ambil rombel dari jadwal
                    $rombelFromSchedules = \App\Models\Schedule::where('teacher_id', $teacher->id)->pluck('rombongan_belajar_id');
                    // Ambil rombel dari wali kelas
                    $rombelFromWali = \App\Models\RombonganBelajar::where('wali_kelas_id', $teacher->id)->pluck('id');
                    
                    // Gabungkan rombel dari jadwal dan wali kelas
                    $rombelIds = $rombelFromSchedules->concat($rombelFromWali)->unique();
                    
                    // Ambil peserta didik dari rombel
                    $pesertaIds = \App\Models\AnggotaRombel::whereIn('rombongan_belajar_id', $rombelIds)->pluck('peserta_didik_id')->unique();
                    
                    // Filter peserta didik
                    $query->whereIn('id', $pesertaIds);
                } else {
                    $query->whereNull('id'); // Jika guru tidak ditemukan
                }
            }
        }

        // Ambil data siswa yang sudah difilter
        $students = $query->get();

        // Variabel untuk menyimpan hasil terbaik
        $matched = null;
        $bestDistance = 1.0;

        // Loop setiap siswa untuk dicocokkan
        foreach ($students as $siswa) {
            // Ambil data wajah yang tersimpan
            $stored = json_decode($siswa->face_embedding, true);
            // Jika tidak ada data wajah, lanjut ke siswa berikutnya
            if (!$stored)
                continue;
            // Hitung jarak Euclidean
            $distance = $this->euclidean($capturedEmbedding, $stored);
            // Jika jarak lebih kecil dari threshold dan bestDistance, update
            if ($distance < $threshold && $distance < $bestDistance) {
                $bestDistance = $distance;
                $matched = $siswa;
            }
        }

        return $matched;
    }

    private function euclidean($v1, $v2)
    {
        // Cek apakah panjang vector sama
        if (count($v1) !== count($v2))
            return 1.0;

        $sum = 0;
        // Hitung kuadrat selisih setiap elemen
        foreach ($v1 as $i => $val) {
            $sum += pow($val - $v2[$i], 2);
        }

        // Akar kuadrat dari jumlah selisih
        return sqrt($sum);
    }
}
