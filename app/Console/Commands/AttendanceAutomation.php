<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\AttendancePermission;
use App\Models\AnggotaRombel;
use App\Models\SchoolSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceAutomation extends Command
{
    protected $signature = 'attendance:automate';
    protected $description = 'Otomatisasi Absensi (Alpha Otomatis & Expire Izin)';

    public function handle()
    {
        $today = Carbon::today();
        $this->info("🚀 Memulai Otomatisasi Absensi - " . $today->toDateString());

        // --- 0. CEK HARI SEKOLAH ---
        $schoolDaysStr = SchoolSetting::where('key', 'hari_sekolah')->value('value') ?? 'senin,selasa,rabu,kamis,jumat';
        $schoolDays = explode(',', strtolower($schoolDaysStr));
        
        $dayName = strtolower($today->englishDayOfWeek);
        $map = [
            'monday' => 'senin', 'tuesday' => 'selasa', 'wednesday' => 'rabu',
            'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu', 'sunday' => 'minggu'
        ];
        $hariIndo = $map[$dayName] ?? $dayName;

        if (!in_array($hariIndo, $schoolDays)) {
            $this->info("ℹ️ Hari ini ({$hariIndo}) adalah hari libur sekolah. Skip otomatisasi.");
            return;
        }

        // --- 1. EXPIRE PENDING PERMISSIONS (> 24 Jam) ---
        $expiredCount = AttendancePermission::where('status', 'pending')
            ->where('created_at', '<', now()->subDay())
            ->update(['status' => 'rejected']);

        if ($expiredCount > 0) {
            $this->warn("⚠️ {$expiredCount} pengajuan izin kadaluarsa diset ke REJECTED.");
        }

        // --- 2. AUTO ALPHA (Hanya jalan setelah jam pulang) ---
        $jamPulang = SchoolSetting::where('key', 'jam_pulang')->value('value') ?? '16:00';

        if (now()->format('H:i') < $jamPulang) {
            $this->info("ℹ️ Belum jam pulang ({$jamPulang}). Skip auto-alpha.");
        } else {
            $students = AnggotaRombel::all();
            $alphaCount = 0;

            foreach ($students as $student) {
                // Cek apakah sudah ada absen masuk hari ini
                $hasAttended = Attendance::where('anggota_rombel_id', $student->id)
                    ->where('tanggal', $today->toDateString())
                    ->where('jenis_absensi', 'masuk')
                    ->exists();

                // Cek apakah ada izin/sakit yang sudah disetujui (Approved)
                $hasApprovedPermission = AttendancePermission::where('anggota_rombel_id', $student->id)
                    ->where('status', 'approved')
                    ->where('tanggal_mulai', '<=', $today)
                    ->where('tanggal_selesai', '>=', $today)
                    ->exists();

                if (!$hasAttended && !$hasApprovedPermission) {
                    // Cek lagi apakah record alpha sudah ada (biar gak double)
                    $isAlreadyAlpha = Attendance::where('anggota_rombel_id', $student->id)
                        ->where('tanggal', $today->toDateString())
                        ->where('status', 'alpha')
                        ->exists();

                    if (!$isAlreadyAlpha) {
                        Attendance::create([
                            'anggota_rombel_id' => $student->id,
                            'tanggal' => $today->toDateString(),
                            'waktu_absen' => now(), // waktu sistem saat auto-alpha
                            'jenis_absensi' => 'masuk',
                            'status' => 'alpha',
                            'metode' => 'manual'
                        ]);
                        $alphaCount++;
                    }
                }
            }
            $this->info("✅ Berhasil meng-ALFA-kan {$alphaCount} siswa.");
        }

        $this->info("🏁 Selesai.");
    }
}
