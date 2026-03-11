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
            // Cek apakah siswa sudah absen masuk
            if (
                Attendance::where('anggota_rombel_id', $anggotaRombel->id)
                    ->where('tanggal', $today)
                    ->where('jenis_absensi', 'masuk')
                    ->exists()
            ) {
                throw new \Exception('Anda sudah absen masuk.');
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
                    ->exists()
            ) {
                throw new \Exception('Anda sudah presensi mapel ini.');
            }
            // Set schedule id
            $scheduleId = $schedule->id;
        } elseif ($type === 'pulang') {
            // Ambil jam pulang dari database
            $jamPulang = SchoolSetting::where('key', 'jam_pulang')->value('value') ?? '15:00';
            // Cek apakah sudah jam pulang
            if ($currentTime < $jamPulang) {
                throw new \Exception('Belum jam pulang.');
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
            // Return hasil
            return [
                'attendance' => $attendance,
                'is_late' => $status === 'terlambat',
                'late_info' => $lateInfo
            ];
        });
    }
}
