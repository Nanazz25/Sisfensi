<?php

namespace App\Services;

use App\Models\PesertaDidik;

class FaceRecognitionService
{
    public function match(array $capturedEmbedding, $threshold = 0.5)
    {
        $students = PesertaDidik::whereNotNull('face_embedding')->get();

        $matched = null;
        $bestDistance = 1.0;

        foreach ($students as $siswa) {
            $stored = json_decode($siswa->face_embedding, true);
            if (!$stored)
                continue;

            $distance = $this->euclidean($capturedEmbedding, $stored);

            if ($distance < $threshold && $distance < $bestDistance) {
                $bestDistance = $distance;
                $matched = $siswa;
            }
        }

        return $matched;
    }

    private function euclidean($v1, $v2)
    {
        if (count($v1) !== count($v2))
            return 1.0;

        $sum = 0;
        foreach ($v1 as $i => $val) {
            $sum += pow($val - $v2[$i], 2);
        }

        return sqrt($sum);
    }
}
