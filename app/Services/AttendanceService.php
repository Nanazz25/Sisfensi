<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\SchoolSetting;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function process($siswa, $anggotaRombel, $type, $now, $locationData)
    {
        // Ambil tanggal dan waktu saat ini
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();

        $scheduleId = null;
        $status = 'hadir';

        // Cek jenis absensi
        if ($type === 'masuk') {
            // Ambil semua ID anggota_rombel milik siswa ini
            $allSiswaMemberships = \App\Models\AnggotaRombel::where('peserta_didik_id', $siswa->id)->pluck('id');

            // Cek apakah siswa sudah absen masuk (di kelas manapun)
            $existing = Attendance::whereIn('anggota_rombel_id', $allSiswaMemberships)
                ->where('tanggal', $today)
                ->where('jenis_absensi', 'masuk')
                ->first();

            if ($existing && !in_array($existing->status, ['alpha', 'pending'])) {
                throw new \Exception("Anda sudah terdaftar sebagai " . ucfirst($existing->status) . ".");
            }

            // Jika ada status alpha/pending yang tersisa, hapus bersih
            if ($existing) {
                Attendance::whereIn('anggota_rombel_id', $allSiswaMemberships)
                    ->where('tanggal', $today)
                    ->where('jenis_absensi', 'masuk')
                    ->delete();
            }
            // Ambil jam masuk dan toleransi dari database
            $jamMasuk = SchoolSetting::where('key', 'jam_masuk')->value('value') ?? '07:00';
            $jamToleransi = SchoolSetting::where('key', 'jam_masuk_toleransi')->value('value') ?? '07:30';
            // Cek status absensi
            if ($currentTime <= $jamMasuk) {
                $status = 'hadir';
            } elseif ($currentTime <= $jamToleransi) {
                $status = 'terlambat';
            } else {
                throw new \Exception('Batas waktu scan habis.');
            }
        } elseif ($type === 'mapel') {
            // VALIDASI: Wajib absen masuk dulu sebelum absen mapel
            $hasMasuk = Attendance::where('anggota_rombel_id', $anggotaRombel->id)
                ->where('tanggal', $today)
                ->where('jenis_absensi', 'masuk')
                ->whereIn('status', ['hadir', 'terlambat'])
                ->exists();

            if (!$hasMasuk) {
                throw new \Exception('Harap melakukan Presensi Masuk terlebih dahulu sebelum Presensi Mapel!');
            }

            // Ambil hari ini dalam bahasa Indonesia
            $hariIndo = strtolower($now->locale('id')->dayName);
            // Cari jadwal yang sesuai
            $schedule = Schedule::where('rombongan_belajar_id', $anggotaRombel->rombongan_belajar_id)
                ->where('hari', $hariIndo)
                ->where('jam_mulai', '<=', $currentTime)
                ->where('jam_selesai', '>=', $currentTime)
                ->first();
            // Jika tidak ada jadwal, throw exception
            if (!$schedule) {
                throw new \Exception('Tidak ada jadwal aktif saat ini.');
            }
            // Cek apakah siswa sudah absen mapel ini
            if (
                Attendance::where('anggota_rombel_id', $anggotaRombel->id)
                    ->where('schedule_id', $schedule->id)
                    ->where('tanggal', $today)
                    ->where('jenis_absensi', 'pelajaran')
                    ->exists()
                ) {
                throw new \Exception('Anda sudah presensi mapel ini.');
            }
            // Set schedule id
            $scheduleId = $schedule->id;
        } elseif ($type === 'pulang') {
            // VALIDASI: Wajib absen masuk dulu sebelum absen pulang
            $hasMasuk = Attendance::where('anggota_rombel_id', $anggotaRombel->id)
                ->where('tanggal', $today)
                ->where('jenis_absensi', 'masuk')
                ->whereIn('status', ['hadir', 'terlambat'])
                ->exists();

            if (!$hasMasuk) {
                throw new \Exception('Harap melakukan Presensi Masuk terlebih dahulu sebelum Presensi Pulang!');
            }

            // Ambil jam pulang dari database
            $jamPulang = SchoolSetting::where('key', 'jam_pulang')->value('value') ?? '15:00';
            $pulangDateTime = Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . substr($jamPulang, 0, 5));
            $limitDateTime = $pulangDateTime->copy()->addHours(3);

            // Cek apakah sudah jam pulang
            if ($now->lt($pulangDateTime)) {
                throw new \Exception('Belum jam pulang.');
            }

            // Batas waktu absen pulang: Max 3 jam setelah jam pulang
            if ($now->gt($limitDateTime)) {
                throw new \Exception('Batas waktu presensi pulang sudah habis (Maksimal 3 jam setelah jam pulang).');
            }
            // Cek apakah siswa sudah absen pulang
            if (
                Attendance::where('anggota_rombel_id', $anggotaRombel->id)
                    ->where('tanggal', $today)
                    ->where('jenis_absensi', 'pulang')
                    ->exists()
            ) {
                throw new \Exception('Anda sudah absen pulang.');
            }
        }

        $lateInfo = null;
        if ($status === 'terlambat') {
            // Samakan tanggal dan timezone agar perhitungan akurat
            $startTime = $now->copy()->setTimeFromTimeString($jamMasuk);

            // Hitung selisih
            $diff = $now->diff($startTime);
            $hours = $diff->h;
            $minutes = $diff->i;

            $lateInfo = "Terlambat: " . ($hours > 0 ? "$hours jam " : "") . "$minutes menit";
        }

        // Transaksi database
        return DB::transaction(function () use ($lateInfo, $anggotaRombel, $scheduleId, $type, $status, $today, $now, $locationData) {
            // Buat absensi
            $attendance = Attendance::create([
                'anggota_rombel_id' => $anggotaRombel->id,
                'schedule_id' => $scheduleId,
                'tanggal' => $today,
                'waktu_absen' => $now,
                'jenis_absensi' => ($type === 'mapel' ? 'pelajaran' : $type),
                'status' => $status,
                'metode' => 'wajah',
            ]);
            // Buat lokasi absensi
            AttendanceLocation::create([
                'attendance_id' => $attendance->id,
                'school_location_id' => $locationData['location']->id,
                'latitude' => $locationData['lat'],
                'longitude' => $locationData['lng'],
                'radius' => round($locationData['distance']),
                'lokasi_valid' => true,
            ]);

            // Refresh model agar mendapatkan data terbaru dari observer (misal status berubah karena token)
            $attendance->refresh();

            // Return hasil
            return [
                'attendance' => $attendance,
                'is_late' => $attendance->status === 'terlambat',
                'is_exempted' => str_contains($attendance->remarks ?? '', 'Token'), // Deteksi apakah token digunakan
                'late_info' => $lateInfo,
                'status' => $attendance->status
            ];
        });
    }
}
