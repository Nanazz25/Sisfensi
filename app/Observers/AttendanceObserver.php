<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\PointRule;
use App\Models\PointLedger;
use App\Models\UserToken;
use App\Models\User;
use Carbon\Carbon;

class AttendanceObserver
{
    /**
     * Berjalan otomatis saat data Absen baru saja dibuat di database.
     */
    public function created(Attendance $attendance): void
    {
        $this->processPoints($attendance);
    }

    /**
     * Berjalan otomatis saat data Absen diubah (diupdate) oleh Admin/Guru.
     */
    public function updated(Attendance $attendance): void
    {
        // Jika status berubah dari hadir/terlambat ke status lain, kembalikan voucher
        if ($attendance->isDirty('status') && !in_array($attendance->status, ['hadir', 'terlambat'])) {
            $tokens = \DB::table('user_tokens')->where('used_at_attendance_id', $attendance->id)->get();
            
            if ($tokens->isNotEmpty()) {
                \DB::table('user_tokens')->where('used_at_attendance_id', $attendance->id)
                         ->update(['status' => 'AVAILABLE', 'used_at_attendance_id' => null, 'updated_at' => now()]);
                
                // Bersihkan remarks yang mungkin mengandung informasi token agar tidak membingungkan
                if ($attendance->remarks && str_contains($attendance->remarks, 'Token Bebas Terlambat')) {
                    // Regex untuk menghapus pattern (Token Bebas Terlambat ...m Digunakan. Terlambat: ...m)
                    $cleanRemarks = preg_replace('/\(Token Bebas Terlambat .*?m Digunakan\. Terlambat: .*?m\)/', '', $attendance->remarks);
                    $attendance->remarks = trim($cleanRemarks);
                    $attendance->saveQuietly();
                }
            }
        }

        // Hanya jalankan koreksi jika status, waktu absen, atau tanggalnya berubah
        if ($attendance->isDirty(['status', 'waktu_absen', 'tanggal'])) {
            $this->processPoints($attendance, true);
        }
    }

    /**
     * Berjalan SEBELUM data Absen dihapus. 
     * Penting menggunakan 'deleting' (bukan deleted) karena DB sudah punya FK set null.
     */
    public function deleting(Attendance $attendance): void
    {
        // Kembalikan Voucher/Token jika ada yang pernah terpakai oleh record ini
        \DB::table('user_tokens')->where('used_at_attendance_id', $attendance->id)
                 ->update(['status' => 'AVAILABLE', 'used_at_attendance_id' => null, 'updated_at' => now()]);
    }

    public function deleted(Attendance $attendance): void
    {
        // Voucher sudah diurus di hook 'deleting' agar koneksi ID masih ada
        $this->processPoints($attendance, true, true);
    }

    /**
     * LOGIKA INTI: Memproses perhitungan poin berdasarkan data presensi.
     */
    private function processPoints(Attendance $attendance, bool $isAdjustment = false, bool $isDeletion = false): void
    {
        $user = $attendance->anggotaRombel?->pesertaDidik?->user;
        if (!$user || $user->role !== 'siswa') return;

        /**
         * FITUR 1: TOKEN INTERCEPTOR (Item "Bebas Terlambat")
         * Jika siswa terlambat tapi punya item khusus di inventori, 
         * maka status absen akan otomatis diubah kembali jadi "Hadir".
         */
        if (!$isDeletion && $attendance->jenis_absensi === 'masuk' && $attendance->status === 'terlambat' && $attendance->waktu_absen) {
            $settings = \DB::table('school_settings')->pluck('value', 'key')->toArray();
            $jamMasukResmi = $settings['jam_masuk'] ?? '07:00';
            
            try {
                // Hitung keterlambatan (dalam menit) dari Jam Masuk Resmi
                $targetTime = Carbon::createFromFormat('H:i', $jamMasukResmi);
                $targetTime->setDate($attendance->waktu_absen->year, $attendance->waktu_absen->month, $attendance->waktu_absen->day);
                
                // minutesLate = Waktu Absen - Jam Masuk
                $minutesLate = round($targetTime->diffInMinutes($attendance->waktu_absen, false), 1);

                if ($minutesLate > 0) {
                    // Cari token yang sanggup menutupi menit terlambat (effect_value >= minutesLate)
                    $token = UserToken::where('user_id', $user->id)
                        ->where('status', 'AVAILABLE')
                        ->whereHas('item', function ($q) use ($minutesLate) {
                            $q->where('item_type', 'LATE_EXEMPTION')
                              ->where('effect_value', '>=', $minutesLate);
                        })
                        ->first();

                    if ($token) {
                        // Ambil ID Absensi seakurat mungkin (beberapa server delay dalam menyimpan ID di observer)
                        $attendanceId = $attendance->id ?: \DB::table('attendance')
                            ->where('anggota_rombel_id', $attendance->anggota_rombel_id)
                            ->where('waktu_absen', $attendance->waktu_absen->format('Y-m-d H:i:s'))
                            ->value('id');

                        // Hubungkan token dengan presensi ini menggunakan Query Builder agar lebih instan dan pasti
                        \DB::table('user_tokens')->where('id', $token->id)->update([
                            'status' => 'USED',
                            'used_at_attendance_id' => $attendanceId,
                            'updated_at' => now()
                        ]);

                        $attendance->status = 'hadir';
                        $attendance->remarks = ($attendance->remarks ? $attendance->remarks . ' ' : '') . 
                                             "(Token Bebas Terlambat {$token->item->effect_value}m Digunakan. Terlambat: {$minutesLate}m)";
                        $attendance->saveQuietly();
                    }
                }
            } catch (\Exception $e) {
                // Jika parsing gagal, fallback ke logika lama (tanpa cek menit)
            }
        }

        /**
         * FITUR 2: MESIN ATURAN (Rule Engine)
         * Mengambil semua aturan integritas yang bertipe 'attendance'.
         */
        $rules = PointRule::where('target_role', 'siswa')->where('trigger_type', 'attendance')->get();
        $settings = \DB::table('school_settings')->pluck('value', 'key')->toArray();
        
            // Filter Aturan: 
        $calculatePoints = function($attRecord) use ($rules, $settings, $attendance) {
            $statusRules = [];
            $timeRules = [];
            
            foreach ($rules as $rule) {
                // 1. Cek Kategori Absensi: Rule harus 'all' atau cocok dengan jenis_absensi saat ini
                if ($rule->attendance_type !== 'all' && $rule->attendance_type !== $attendance->jenis_absensi) {
                    continue;
                }

                // 2. Proteksi Double: Jika bukan absen masuk, tetap skip aturan yang pakai referensi 'jam_masuk'
                if ($attendance->jenis_absensi !== 'masuk' && ($rule->reference_key === 'jam_masuk' || $rule->reference_key === 'jam_masuk_toleransi')) {
                    continue;
                }

                if ($this->evaluateRule($rule, $attRecord, $settings)) {
                    if ($rule->basis_type === 'status') {
                        $statusRules[] = $rule;
                    } else {
                        $timeRules[] = $rule;
                    }
                }
            }

            // Urutkan Aturan Waktu: Ambil yang paling ekstrim (Offset menit tertinggi)
            $bestTimeRule = null;
            if (count($timeRules) > 0) {
                usort($timeRules, function($a, $b) {
                    return $b->offset_minutes <=> $a->offset_minutes;
                });
                $bestTimeRule = $timeRules[0];
            }

            $score = 0;
            $names = [];

            if ($attendance->jenis_absensi === 'pelajaran') {
                // KHUSUS MAPEL: Gunakan sistem Override (Prioritas Waktu)
                if ($bestTimeRule) {
                    $score = $bestTimeRule->point_modifier;
                    $names[] = $bestTimeRule->rule_name;
                } else {
                    foreach ($statusRules as $r) {
                        $score += $r->point_modifier;
                        $names[] = $r->rule_name;
                    }
                }
            } else {
                // ABSEN LAIN (Masuk/Pulang): Gunakan sistem Akumulatif (Status + Bonus Waktu)
                foreach ($statusRules as $r) {
                    $score += $r->point_modifier;
                    $names[] = $r->rule_name;
                }
                if ($bestTimeRule) {
                    $score += $bestTimeRule->point_modifier;
                    $names[] = $bestTimeRule->rule_name;
                }
            }

            return ['score' => $score, 'names' => $names];
        };

        $newData = $calculatePoints($attendance);
        $totalModifier = $newData['score'];

        // Ambil nama mapel jika ada untuk deskripsi yang lebih spesifik
        $subjectName = $attendance->schedule?->subject?->nama_mapel ?: null;

        // Tentukan Label Kategori untuk Deskripsi
        $categoryLabel = match($attendance->jenis_absensi) {
            'masuk' => '[Harian] ',
            'pelajaran' => '[Mapel] ',
            'pulang' => '[Pulang] ',
            default => '[Manual] '
        };

        // Gembok Keamanan: Pastikan aturan yang sama tidak cair berkali-kali untuk KEY yang sama
        if (!$isAdjustment && !$isDeletion) {
            $ruleNamesString = implode(', ', $newData['names'] ?? []);
            
            // Jika tidak ada aturan yang terpenuhi, ya tidak usah lanjut
            if (empty($ruleNamesString)) return;

            // Hitung berapa kali poin ini sudah didapat (Cek dengan Label Kategori)
            $awardedCount = PointLedger::where('user_id', $user->id)
                ->whereDate('created_at', $attendance->tanggal)
                ->where('description', 'like', '%' . $categoryLabel . $ruleNamesString . '%')
                ->where('description', 'not like', '%[Dibatalkan]%')
                ->where('description', 'not like', '%[Koreksi]%');
            
            // Hitung berapa kali poin ini SUDAH DIBATALKAN
            $cancelledCount = PointLedger::where('user_id', $user->id)
                ->whereDate('created_at', $attendance->tanggal)
                ->where('description', 'like', '%[Dibatalkan]%' . $categoryLabel . $ruleNamesString . '%');
            
            // Filter per Mapel jika ada
            if ($subjectName) {
                $awardedCount->where('description', 'like', '%[' . $subjectName . ']%');
                $cancelledCount->where('description', 'like', '%[' . $subjectName . ']%');
            }

            // Jika jumlah dapet >= jumlah batal, berarti gembok aktif (tidak boleh dapet lagi)
            if ($awardedCount->count() > $cancelledCount->count()) return;
        }

        /**
         * FITUR 3: LOGIKA KOREKSI (Adjustment)
         * Jika ini adalah update (bukan baru), hitung selisih antara data lama dan baru.
         */
        if ($isDeletion) {
            $data = $calculatePoints($attendance);
            $ruleNamesString = implode(', ', $data['names'] ?? []);
            
            if (empty($ruleNamesString)) return;

            // CEK: Apakah dia benar-benar dapet poin ini sebelumnya?
            $awardedCount = PointLedger::where('user_id', $user->id)
                ->whereDate('created_at', $attendance->tanggal)
                ->where('description', 'like', '%' . $categoryLabel . $ruleNamesString . '%')
                ->where('description', 'not like', '%[Dibatalkan]%')
                ->count();
            
            $cancelledCount = PointLedger::where('user_id', $user->id)
                ->whereDate('created_at', $attendance->tanggal)
                ->where('description', 'like', '%[Dibatalkan]%')
                ->where('description', 'like', '%' . $categoryLabel . $ruleNamesString . '%')
                ->count();

            // Jika jumlah dapet <= jumlah batal, berarti tidak ada poin aktif yang perlu ditarik balik
            if ($awardedCount <= $cancelledCount) return;
            
            $totalModifier = -$data['score'];
        } elseif ($isAdjustment) {
            // Bayangkan data absen SEBELUM diedit
            $oldAtt = clone $attendance;
            $oldAtt->status = $attendance->getOriginal('status');
            $oldAtt->waktu_absen = $attendance->getOriginal('waktu_absen');
            
            $oldData = $calculatePoints($oldAtt);
            $totalModifier = $newData['score'] - $oldData['score'];
        }

        // Jika ada perubahan poin, catat ke mutasi (Ledger)
        if ($totalModifier !== 0) {
            $currentBalance = $user->current_points + $totalModifier;

            PointLedger::create([
                'user_id' => $user->id,
                'transaction_type' => $totalModifier > 0 ? 'EARN' : 'PENALTY',
                'amount' => $totalModifier,
                'current_balance' => $currentBalance,
                'description' => ($isDeletion ? "[Dibatalkan] " : ($isAdjustment ? "[Koreksi] " : "")) . 
                                 $categoryLabel . 
                                 (implode(', ', $newData['names']) ?: ($isDeletion ? 'Pembatalan Presensi' : 'Penyesuaian Presensi')) . 
                                 ($subjectName ? " [" . $subjectName . "]" : "") .
                                 ($attendance->remarks ? " (" . $attendance->remarks . ")" : "") .
                                 " pada " . $attendance->tanggal->format('d/m/Y')
            ]);
        }
    }

    protected function evaluateRule(PointRule $rule, Attendance $attendance, array $settings = []): bool
    {
        $basis = $rule->basis_type;
        $operator = $rule->condition_operator;
        $value = $rule->condition_value;

        // 1. Basis Status (alfa, hadir, terlambat, dll)
        if ($basis === 'status') {
            return strtolower($attendance->status) === strtolower($value);
        }

        // Basis Waktu: PROTEKSI - Hanya berlaku jika statusnya Hadir atau Terlambat
        // Siswa Alfa tidak boleh kena denda keterlambatan waktu lagi
        if (!in_array(strtolower($attendance->status), ['hadir', 'terlambat'])) {
            return false;
        }

        // Basis Waktu membutuhkan waktu_absen
        if (!$attendance->waktu_absen) return false;
        $attendanceTime = $attendance->waktu_absen->format('H:i:s');

        // 2. Basis Setting (Dinamis berdasarkan Jam Sekolah + Offset)
        if ($basis === 'setting') {
            $settingRaw = $settings[$rule->reference_key] ?? null;
            if (!$settingRaw) return false;

            try {
                // Parse setting jam sekolah (format H:i)
                $baseTime = Carbon::createFromFormat('H:i', $settingRaw);
                // Sesuaikan tanggal agar sama dengan waktu absen untuk komparasi akurat
                $baseTime->setDate($attendance->waktu_absen->year, $attendance->waktu_absen->month, $attendance->waktu_absen->day);
                $baseTime->second = 0;

                // LOGIKA OFFSET:
                // Jika operator adalah '<' (sebelum), maka target = Jam - Offset (misal 30m sebelum jam 7 ya jam 6:30)
                // Jika operator adalah '>' (sesudah), maka target = Jam + Offset (misal 50m sesudah jam 7 ya jam 7:50)
                if ($rule->offset_minutes != 0) {
                    if ($operator === '<') {
                        $baseTime->subMinutes($rule->offset_minutes);
                    } else {
                        $baseTime->addMinutes($rule->offset_minutes);
                    }
                }
                
                if ($operator === '<') return $attendance->waktu_absen->lessThan($baseTime);
                if ($operator === '>') return $attendance->waktu_absen->greaterThan($baseTime);
                if ($operator === '=') return $attendance->waktu_absen->equalTo($baseTime);
                
                return false;
            } catch (\Exception $e) {
                return false;
            }
        }

        // 3. Basis Jadwal Mapel (Dinamis berdasarkan Jam Mulai/Selesai Mapel + Offset)
        if ($basis === 'schedule') {
            $schedule = $attendance->schedule;
            if (!$schedule) return false;

            $targetKey = ($rule->reference_key === 'jam_mulai_mapel') ? 'jam_mulai' : 'jam_selesai';
            $rawTime = $schedule->$targetKey; // Format H:i:s

            try {
                $baseTime = Carbon::createFromFormat('H:i:s', $rawTime);
                $baseTime->setDate($attendance->waktu_absen->year, $attendance->waktu_absen->month, $attendance->waktu_absen->day);
                
                if ($rule->offset_minutes != 0) {
                    if ($operator === '<') {
                        $baseTime->subMinutes($rule->offset_minutes);
                    } else {
                        $baseTime->addMinutes($rule->offset_minutes);
                    }
                }

                if ($operator === '<') return $attendance->waktu_absen->lessThan($baseTime);
                if ($operator === '>') return $attendance->waktu_absen->greaterThan($baseTime);
                if ($operator === '=') return $attendance->waktu_absen->equalTo($baseTime);

                return false;
            } catch (\Exception $e) {
                return false;
            }
        }

        // 4. Basis Fixed (Jam Manual / Jam Pasti)
        if ($basis === 'fixed' && str_contains($value, ':')) {
            switch ($operator) {
                case '<': return $attendanceTime < $value;
                case '>': return $attendanceTime > $value;
                case '=': return $attendanceTime == $value;
                case 'BETWEEN':
                    $parts = explode(',', $value);
                    if (count($parts) === 2) {
                        return $attendanceTime >= trim($parts[0]) && $attendanceTime <= trim($parts[1]);
                    }
                    return false;
            }
        }
        
        return false;
    }
}
