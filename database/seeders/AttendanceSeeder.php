<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\Schedule;
use App\Models\AnggotaRombel;
use App\Models\SchoolLocation;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Simulasikan kehadiran untuk hari ini
        $today = Carbon::now();
        $dayName = strtolower($today->translatedFormat('l'));

        $schedules = Schedule::all();
        $schoolLocation = SchoolLocation::first();

        foreach ($schedules as $schedule) {
            // Dapatkan siswa di kelas ini
            $classMembers = AnggotaRombel::where('rombongan_belajar_id', $schedule->rombongan_belajar_id)->get();

            foreach ($classMembers as $member) {
                // Tentukan status acak
                $status = fake()->randomElement(['hadir', 'hadir', 'hadir', 'izin', 'sakit']);

                // 1. Catatan Kehadiran
                $attendance = Attendance::create([
                    'anggota_rombel_id' => $member->id,
                    'schedule_id' => $schedule->id,
                    'tanggal' => $today->toDateString(),
                    'waktu_absen' => $today->setTime(7, 0, 0), // 07:00 AM
                    'jenis_absensi' => 'masuk', // Sesuai permintaan: Mulai dengan 'masuk'
                    'status' => $status,
                    'metode' => 'wajah',
                ]);

                // 2. Catatan Lokasi (Hanya jika hadir)
                if ($status == 'hadir') {
                    AttendanceLocation::create([
                        'attendance_id' => $attendance->id,
                        'school_location_id' => $schoolLocation->id,
                        'latitude' => $schoolLocation->latitude,
                        'longitude' => $schoolLocation->longitude,
                        'radius' => 5,
                        'lokasi_valid' => true,
                    ]);
                }
            }
        }
    }
}
