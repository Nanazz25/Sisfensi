<?php

namespace App\Services;

use App\Models\FaceLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;

class FaceLogService
{
    /**
     * Simpan log wajah (khusus failure / spoof / confidence rendah)
     */
    public function store(?int $siswaId, ?float $confidence, string $result, string $base64Image): void
    {
        try {

            // Hanya simpan FAILURE (opsi hemat storage)
            if (!in_array($result, ['unrecognized', 'low_confidence', 'spoof'])) {
                return;
            }

            // Bersihkan prefix base64
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
            $imageData = base64_decode($imageData);

            // Kompres & resize biar hemat storage
            $img = Image::make($imageData)
                ->resize(200, 200)
                ->encode('jpg', 50);

            // Encrypt file
            $encrypted = Crypt::encrypt((string) $img);

            // Nama file
            $filename = 'facelogs/' .
                ($siswaId ?? 'unknown') .
                '_' . time() .
                '.enc';

            DB::transaction(function () use ($filename, $encrypted, $siswaId, $confidence, $result) {
                // Simpan ke Storage
                Storage::put($filename, $encrypted);

                // Simpan ke DB
                FaceLog::create([
                    'peserta_didik_id' => $siswaId,
                    'confidence' => $confidence,
                    'result' => $result,
                    'image_path' => $filename
                ]);
            });

        } catch (\Exception $e) {
            // Jika transaksi gagal tapi file sempat terupload, hapus file nya
            if (isset($filename) && Storage::exists($filename)) {
                Storage::delete($filename);
            }
        }
    }

    /**
     * Hapus log lama (dipakai scheduler)
     */
    public function purgeOld(int $days = 7): int
    {
        $logs = FaceLog::where('created_at', '<', now()->subDays($days))->get();

        foreach ($logs as $log) {
            if ($log->image_path && Storage::exists($log->image_path)) {
                Storage::delete($log->image_path);
            }
            $log->delete();
        }

        return $logs->count();
    }
}
