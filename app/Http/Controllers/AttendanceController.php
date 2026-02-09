<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PesertaDidik;
use App\Models\Attendance;
use App\Models\SchoolSetting;
use App\Models\Schedule;
use App\Models\AnggotaRombel;
use App\Models\SchoolLocation;
use App\Models\AttendanceLocation;
use App\Models\FaceLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;

class AttendanceController extends Controller
{
    /**
     * Tampilkan menu scan absensi (Single Page)
     */
    public function scanner($type = 'masuk')
    {
        $title = 'Sistem Presensi Biometrik';
        return view('attendance.scanner', compact('type', 'title'));
    }

    private function storeFaceLog($siswaId, $confidence, $result, $base64Image)
    {
        try {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
            $imageData = base64_decode($imageData);

            $img = Image::make($imageData)
                ->resize(200, 200)
                ->encode('jpg', 50);

            $encrypted = Crypt::encrypt((string) $img);

            $filename = 'facelogs/' . ($siswaId ?? 'unknown') . '_' . time() . '.enc';
            Storage::put($filename, $encrypted);

            FaceLog::create([
                'peserta_didik_id' => $siswaId,
                'confidence' => $confidence,
                'result' => $result,
                'image_path' => $filename
            ]);
        } catch (\Exception $e) {
            // silent fail
        }
    }

    /**
     * Proses verifikasi wajah, lokasi, dan catat absensi
     */
    public function verify(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'face_embedding' => 'required|array',
            'type' => 'required|in:masuk,mapel,pulang',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $capturedEmbedding = $request->face_embedding;
        $type = $request->type;
        $latSiswa = $request->latitude;
        $lngSiswa = $request->longitude;

        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();

        // 1. TAHAP: VERIFIKASI WAJAH
        $matchedSiswa = null;
        $bestDistance = 1.0;
        $threshold = 0.5;

        $students = PesertaDidik::whereNotNull('face_embedding')->get();
        foreach ($students as $siswa) {
            $storedEmbedding = json_decode($siswa->face_embedding, true);
            if (!$storedEmbedding)
                continue;

            $distance = $this->euclideanDistance($capturedEmbedding, $storedEmbedding);

            if ($distance < $threshold && $distance < $bestDistance) {
                $bestDistance = $distance;
                $matchedSiswa = $siswa;
            }
        }

        // TAHAP: LOG WAJAH & ENKRIPSI (Solusi Keamanan & Efisiensi)
        $logPath = null;
        try {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
            $imageData = base64_decode($imageData);

            // Kompres gambar sebelum dienkripsi agar tidak membebani server
            $img = Image::make($imageData)->resize(200, 200)->encode('jpg', 50);
            $encrypted = Crypt::encrypt((string) $img);

            $logFilename = 'facelogs/' . ($matchedSiswa ? $matchedSiswa->id : 'unknown') . '_' . time() . '.enc';
            Storage::put($logFilename, $encrypted);
            $logPath = $logFilename;
        } catch (\Exception $e) { /* ignore log saving errors */
        }

        if (!$matchedSiswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak dikenali atau belum terdaftar!'
            ], 404);
        }

        if (!$matchedSiswa) {

            $this->storeFaceLog(
                null,
                null,
                'unrecognized',
                $request->image
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak dikenali atau belum terdaftar!'
            ], 404);
        }

        // 2. TAHAP: VERIFIKASI LOKASI
        $schoolLocations = SchoolLocation::all();
        $nearestLocation = null;
        $isInsideRadius = false;
        $minDistance = 999999;

        foreach ($schoolLocations as $loc) {
            $distance = $this->calculateDistance($latSiswa, $lngSiswa, $loc->latitude, $loc->longitude);
            if ($distance <= $loc->radius_maks) {
                $isInsideRadius = true;
                $nearestLocation = $loc;
                break;
            }
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestLocation = $loc;
            }
        }

        if (!$isInsideRadius) {
            return response()->json([
                'status' => 'error',
                'message' => "Radius tidak cocok! Jarak: " . round($minDistance) . "m."
            ], 403);
        }

        // 3. TAHAP: PENGECEKAN KELAS & JADWAL
        $anggotaRombel = AnggotaRombel::where('peserta_didik_id', $matchedSiswa->id)->latest('id')->first();
        if (!$anggotaRombel) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak terdaftar di kelas.'], 400);
        }

        $scheduleId = null;
        $status = 'hadir';

        if ($type === 'masuk') {
            $existing = Attendance::where('anggota_rombel_id', $anggotaRombel->id)
                ->where('tanggal', $today)
                ->where('jenis_absensi', 'masuk')
                ->first();

            if ($existing) {
                return response()->json(['status' => 'info', 'message' => 'Anda sudah absen masuk (' . $existing->status . ').']);
            }

            // ATURAN WAKTU MASUK
            $jamMasuk = SchoolSetting::getValue('jam_masuk', '07:00');
            $jamToleransi = SchoolSetting::getValue('jam_masuk_toleransi', '07:30'); // Pastikan kunci di DB sesuai. Sepertinya 'jam_masuk_toleransi'

            if ($currentTime <= $jamMasuk) {
                // Tepat waktu
                $status = 'hadir';
            } elseif ($currentTime > $jamMasuk && $currentTime <= $jamToleransi) {
                // Terlambat tapi masih boleh absen
                $status = 'terlambat';
            } else {
                // Lewat jam toleransi => TIDAK BOLEH ABSEN WAJAH
                return response()->json([
                    'status' => 'error',
                    'message' => 'Batas waktu scan habis! Silakan ajukan izin/sakit.'
                ], 422);
            }
        } elseif ($type === 'mapel') {
            $hariIndo = $this->translateHari(strtolower($now->englishDayOfWeek));
            $schedule = Schedule::where('rombongan_belajar_id', $anggotaRombel->rombongan_belajar_id)
                ->where('hari', $hariIndo)
                ->where('jam_mulai', '<=', $currentTime)
                ->where('jam_selesai', '>=', $currentTime)
                ->first();

            if (!$schedule) {
                return response()->json(['status' => 'error', 'message' => 'Tidak ada mapel aktif saat ini.'], 404);
            }

            if (Attendance::where('anggota_rombel_id', $anggotaRombel->id)->where('schedule_id', $schedule->id)->where('tanggal', $today)->exists()) {
                return response()->json(['status' => 'info', 'message' => 'Sudah absen mapel ini.']);
            }
            $scheduleId = $schedule->id;
        } elseif ($type === 'pulang') {
            $jamPulang = SchoolSetting::where('key', 'jam_pulang')->value('value') ?? '15:00';
            if ($currentTime < $jamPulang) {
                return response()->json(['status' => 'error', 'message' => 'Belum jam pulang: ' . $jamPulang], 400);
            }
            if (Attendance::where('anggota_rombel_id', $anggotaRombel->id)->where('tanggal', $today)->where('jenis_absensi', 'pulang')->exists()) {
                return response()->json(['status' => 'info', 'message' => 'Anda sudah absen pulang.']);
            }
        }

        DB::beginTransaction();
        try {
            $attendance = Attendance::create([
                'anggota_rombel_id' => $anggotaRombel->id,
                'schedule_id' => $scheduleId,
                'tanggal' => $today,
                'waktu_absen' => $now,
                'jenis_absensi' => $type,
                'status' => $status,
                'metode' => 'wajah',
            ]);

            AttendanceLocation::create([
                'attendance_id' => $attendance->id,
                'school_location_id' => $nearestLocation->id,
                'latitude' => $latSiswa,
                'longitude' => $lngSiswa,
                'radius' => round($minDistance),
                'lokasi_valid' => true,
            ]);

            DB::commit();

            $lateMessage = '';
            if ($status === 'terlambat') {
                $masuk = Carbon::parse($jamMasuk);
                $diffInMinutes = $masuk->diffInMinutes($now);

                $hours = floor($diffInMinutes / 60);
                $minutes = $diffInMinutes % 60;

                $str = "";
                if ($hours > 0)
                    $str .= "{$hours} jam ";
                if ($minutes > 0)
                    $str .= "{$minutes} menit";

                $lateMessage = " (Terlambat " . trim($str) . ")";
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Absensi ' . $type . ' berhasil!' . $lateMessage,
                'nama' => $matchedSiswa->nama_lengkap,
                'waktu' => $now->format('H:i:s'),
                'is_late' => $status === 'terlambat',
                'late_info' => $lateMessage
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function euclideanDistance($v1, $v2)
    {
        if (count($v1) != count($v2))
            return 1.0;
        $sum = 0;
        for ($i = 0; $i < count($v1); $i++) {
            $sum += pow($v1[$i] - $v2[$i], 2);
        }
        return sqrt($sum);
    }

    private function translateHari($day)
    {
        $map = ['monday' => 'senin', 'tuesday' => 'selasa', 'wednesday' => 'rabu', 'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu', 'sunday' => 'minggu'];
        return $map[$day] ?? $day;
    }
}
