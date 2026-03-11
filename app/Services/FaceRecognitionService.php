<?php

namespace App\Services;

use App\Models\PesertaDidik;

class FaceRecognitionService
{
    public function match(array $capturedEmbedding, $threshold = 0.5)
    {
        // Ambil semua siswa yang punya data wajah
        $students = PesertaDidik::whereNotNull('face_embedding')->get();

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
