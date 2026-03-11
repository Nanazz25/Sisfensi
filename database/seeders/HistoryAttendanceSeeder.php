<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\AnggotaRombel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HistoryAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure students/teachers have random passwords if not set
        $this->command->info('Setting random passwords for existing users...');
        User::whereNull('initial_password')->get()->each(function ($user) {
            if ($user->role === 'admin') {
                $user->update(['initial_password' => 'password', 'password' => bcrypt('password')]);
            } else {
                $plain = Str::random(8);
                $user->update(['initial_password' => $plain, 'password' => bcrypt($plain)]);
            }
        });

        // 2. Clear old attendance to avoid duplicates/confusion
        $this->command->warn('Deleting existing attendance data for clean 1-year history...');
        DB::table('attendance')->delete();

        $rombels = \App\Models\RombonganBelajar::all();
        $startDate = Carbon::now()->subYear();
        $endDate = Carbon::now();

        $this->command->info('Generating 1 year of attendance data...');

        $attendanceData = [];
        $batchSize = 500;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $hariEng = strtolower($date->englishDayOfWeek);
            $hariIndo = $this->translateHari($hariEng);

            // Skip Sunday
            if ($hariIndo === 'minggu')
                continue;

            $this->command->info("Processing date: " . $date->toDateString());

            foreach ($rombels as $rombel) {
                $anggota = $rombel->anggotaRombel;
                $schedules = Schedule::where('rombongan_belajar_id', $rombel->id)
                    ->where('hari', $hariIndo)
                    ->get();

                foreach ($anggota as $student) {
                    // Randomly decide status for the day
                    $rand = rand(1, 100);
                    $status = 'hadir';
                    if ($rand > 90)
                        $status = 'alpha';
                    elseif ($rand > 85)
                        $status = 'izin';
                    elseif ($rand > 80)
                        $status = 'sakit';
                    elseif ($rand > 70)
                        $status = 'terlambat';

                    // 1. Daily Entry (Masuk)
                    $attendanceData[] = [
                        'anggota_rombel_id' => $student->id,
                        'schedule_id' => null,
                        'tanggal' => $date->toDateString(),
                        'waktu_absen' => $date->copy()->setTime(7, rand(0, 59))->toDateTimeString(),
                        'jenis_absensi' => 'masuk',
                        'status' => $status,
                        'metode' => 'manual',
                        'remarks' => 'Dummy historical data',
                        'processed_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // 2. Subject Attendance (Mapel) - Only if not ALPHA/IZIN/SAKIT
                    if (!in_array($status, ['alpha', 'izin', 'sakit'])) {
                        foreach ($schedules as $sch) {
                            $attendanceData[] = [
                                'anggota_rombel_id' => $student->id,
                                'schedule_id' => $sch->id,
                                'tanggal' => $date->toDateString(),
                                'waktu_absen' => $date->copy()->setTimeFromTimeString($sch->jam_mulai)->addMinutes(rand(0, 5))->toDateTimeString(),
                                'jenis_absensi' => 'pelajaran',
                                'status' => 'hadir',
                                'metode' => 'manual',
                                'remarks' => 'Dummy historical data',
                                'processed_by' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    // 3. Departure (Pulang) - Only if not ALPHA/IZIN/SAKIT
                    if (!in_array($status, ['alpha', 'izin', 'sakit'])) {
                        $attendanceData[] = [
                            'anggota_rombel_id' => $student->id,
                            'schedule_id' => null,
                            'tanggal' => $date->toDateString(),
                            'waktu_absen' => $date->copy()->setTime(15, rand(30, 59))->toDateTimeString(),
                            'jenis_absensi' => 'pulang',
                            'status' => 'hadir',
                            'metode' => 'manual',
                            'remarks' => 'Dummy historical data',
                            'processed_by' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // Bulk Insert to avoid memory issues
                    if (count($attendanceData) >= $batchSize) {
                        Attendance::insert($attendanceData);
                        $attendanceData = [];
                    }
                }
            }
        }

        // Final Batch
        if (count($attendanceData) > 0) {
            Attendance::insert($attendanceData);
        }

        $this->command->info('Successfully generated 1 year of attendance data!');
    }

    private function translateHari($day)
    {
        $map = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu'
        ];
        return $map[$day] ?? $day;
    }
}
