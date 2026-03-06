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
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();

        $scheduleId = null;
        $status = 'hadir';

        if ($type === 'masuk') {

            if (
                Attendance::where('anggota_rombel_id', $anggotaRombel->id)
                    ->where('tanggal', $today)
                    ->where('jenis_absensi', 'masuk')
                    ->exists()
            ) {
                throw new \Exception('Anda sudah absen masuk.');
            }

            $jamMasuk = SchoolSetting::where('key', 'jam_masuk')->value('value') ?? '07:00';
            $jamToleransi = SchoolSetting::where('key', 'jam_masuk_toleransi')->value('value') ?? '07:30';

            if ($currentTime <= $jamMasuk) {
                $status = 'hadir';
            } elseif ($currentTime <= $jamToleransi) {
                $status = 'terlambat';
            } else {
                throw new \Exception('Batas waktu scan habis.');
            }
        } elseif ($type === 'mapel') {

            $hariIndo = strtolower($now->locale('id')->dayName);

            $schedule = Schedule::where('rombongan_belajar_id', $anggotaRombel->rombongan_belajar_id)
                ->where('hari', $hariIndo)
                ->where('jam_mulai', '<=', $currentTime)
                ->where('jam_selesai', '>=', $currentTime)
                ->first();

            if (!$schedule) {
                throw new \Exception('Tidak ada jadwal aktif saat ini.');
            }

            if (
                Attendance::where('anggota_rombel_id', $anggotaRombel->id)
                    ->where('schedule_id', $schedule->id)
                    ->where('tanggal', $today)
                    ->exists()
            ) {
                throw new \Exception('Anda sudah presensi mapel ini.');
            }

            $scheduleId = $schedule->id;
        } elseif ($type === 'pulang') {

            $jamPulang = SchoolSetting::where('key', 'jam_pulang')->value('value') ?? '15:00';

            if ($currentTime < $jamPulang) {
                throw new \Exception('Belum jam pulang.');
            }

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

        return DB::transaction(function () use ($lateInfo, $anggotaRombel, $scheduleId, $type, $status, $today, $now, $locationData) {

            $attendance = Attendance::create([
                'anggota_rombel_id' => $anggotaRombel->id,
                'schedule_id' => $scheduleId,
                'tanggal' => $today,
                'waktu_absen' => $now,
                'jenis_absensi' => ($type === 'mapel' ? 'pelajaran' : $type),
                'status' => $status,
                'metode' => 'wajah',
            ]);

            AttendanceLocation::create([
                'attendance_id' => $attendance->id,
                'school_location_id' => $locationData['location']->id,
                'latitude' => $locationData['lat'],
                'longitude' => $locationData['lng'],
                'radius' => round($locationData['distance']),
                'lokasi_valid' => true,
            ]);

            return [
                'attendance' => $attendance,
                'is_late' => $status === 'terlambat',
                'late_info' => $lateInfo
            ];
        });
    }
}
