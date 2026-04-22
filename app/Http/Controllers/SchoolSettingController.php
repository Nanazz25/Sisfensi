<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\FaceLog;
use App\Models\Attendance;
use App\Models\AttendancePermission;
use App\Models\AnggotaRombel;
use App\Services\FaceLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SchoolSettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            function ($request, $next) {
                if (auth()->id() !== 1) {
                    abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengelola pengaturan sistem.');
                }
                return $next($request);
            }
        ];
    }

    public function index()
    {
        $settings = SchoolSetting::all();
        
        // Hitung lampiran yang sudah > 30 hari
        $oldFilesCount = AttendancePermission::whereNotNull('lampiran')
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->count();

        // Cek apakah kemarin hari sekolah untuk UI Sinkronisasi Alpha
        $yesterday = Carbon::yesterday();
        $schoolDaysStr = SchoolSetting::where('key', 'hari_sekolah')->value('value') ?? 'senin,selasa,rabu,kamis,jumat';
        $schoolDays = explode(',', strtolower($schoolDaysStr));
        $dayName = strtolower($yesterday->englishDayOfWeek);
        $map = [
            'monday' => 'senin', 'tuesday' => 'selasa', 'wednesday' => 'rabu',
            'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu', 'sunday' => 'minggu'
        ];
        $isYesterdaySchoolDay = in_array($map[$dayName] ?? $dayName, $schoolDays);
        $yesterdayFormatted = $yesterday->translatedFormat('l, d F Y');

        return view('school-settings.index', compact('settings', 'oldFilesCount', 'isYesterdaySchoolDay', 'yesterdayFormatted'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token');

        // Handle checkboxes (e.g. hari_sekolah) that might be unchecked entirely
        if ($request->isMethod('post') && !$request->has('hari_sekolah')) {
            // Only update if it's not present but should be (assuming it's on the page)
            // For simplicity in this app, we check if it's present in DB and not in request
            if (SchoolSetting::where('key', 'hari_sekolah')->exists()) {
                $data['hari_sekolah'] = '';
            }
        }

        foreach ($data as $key => $value) {
            // Join array if it's from checkboxes (example: hari_sekolah)
            $saveValue = is_array($value) ? implode(',', $value) : $value;
            SchoolSetting::where('key', $key)->update(['value' => $saveValue]);
        }
        return back()->with('success', 'Pengaturan sekolah berhasil diperbarui.');
    }

    public function resetFaceLogs()
    {
        $service = new FaceLogService();
        $count = $service->purgeOld(0); // 0 means everything

        return back()->with('success', "Berhasil membersihkan {$count} data Face Logs.");
    }

    public function syncYesterdayAlpha()
    {
        $yesterday = Carbon::yesterday();

        // --- PROTEKSI HARI LIBUR ---
        $schoolDaysStr = SchoolSetting::where('key', 'hari_sekolah')->value('value') ?? 'senin,selasa,rabu,kamis,jumat';
        $schoolDays = explode(',', strtolower($schoolDaysStr));
        $dayName = strtolower($yesterday->englishDayOfWeek);
        $map = [
            'monday' => 'senin', 'tuesday' => 'selasa', 'wednesday' => 'rabu',
            'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu', 'sunday' => 'minggu'
        ];
        $hariIndo = $map[$dayName] ?? $dayName;

        if (!in_array($hariIndo, $schoolDays)) {
            return back()->with('error', "Gagal! Sinkronisasi alfa tidak dapat dilakukan karena hari kemarin (" . $yesterday->translatedFormat('l, d F Y') . ") adalah hari libur sekolah (Akhir Pekan).");
        }

        // --- PROTEKSI TABEL HARI LIBUR ---
        $holiday = \App\Models\Holiday::where('date', $yesterday->toDateString())->first();
        if ($holiday) {
            return back()->with('error', "Gagal! Sinkronisasi alfa tidak dapat dilakukan karena hari kemarin (" . $yesterday->translatedFormat('d F Y') . ") adalah hari libur: " . $holiday->description);
        }
        $students = AnggotaRombel::whereHas('rombonganBelajar.tahunAjar', function($q) {
            $q->where('is_active', true);
        })->with(['pesertaDidik.user', 'rombonganBelajar.waliKelas.user'])
        ->get()
        ->unique('peserta_didik_id'); // Pastikan satu siswa hanya diproses 1x meskipun ada di daftar rombel ganda
        
        $alphaData = [];

        foreach ($students as $student) {
            // Cek kehadiran kemarin
            $hasAttended = Attendance::where('anggota_rombel_id', $student->id)
                ->where('tanggal', $yesterday->toDateString())
                ->where('jenis_absensi', 'masuk')
                ->exists();

            // Cek izin kemarin
            $hasApprovedPermission = AttendancePermission::where('anggota_rombel_id', $student->id)
                ->where('status', 'approved')
                ->where('tanggal_mulai', '<=', $yesterday)
                ->where('tanggal_selesai', '>=', $yesterday)
                ->exists();

            if (!$hasAttended && !$hasApprovedPermission) {
                // Cek lagi biar ga duplikat
                $isAlreadyAlpha = Attendance::where('anggota_rombel_id', $student->id)
                    ->where('tanggal', $yesterday->toDateString())
                    ->where('status', 'alpha')
                    ->exists();

                if (!$isAlreadyAlpha) {
                    Attendance::create([
                        'anggota_rombel_id' => $student->id,
                        'tanggal' => $yesterday->toDateString(),
                        'waktu_absen' => now(),
                        'jenis_absensi' => 'masuk',
                        'status' => 'alpha',
                        'metode' => 'manual'
                    ]);

                    $alphaData[] = [
                        'name' => $student->pesertaDidik->user->name,
                        'rombel' => $student->rombonganBelajar->nama_rombel,
                        'walas' => $student->rombonganBelajar->waliKelas->user->name ?? 'Tanpa Wali Kelas'
                    ];
                }
            }
        }

        $count = count($alphaData);
        if ($count > 0) {
            return back()->with([
                'success' => "Berhasil sinkronisasi. {$count} siswa kemarin diset sebagai ALPHA.",
                'alpha_data' => $alphaData
            ]);
        }

        return back()->with('success', "Sinkronisasi selesai. Tidak ada siswa tambahan yang diset ALPHA.");
    }

    public function purgeOldPermissions()
    {
        $limitDate = Carbon::now()->subDays(30);
        
        // Ambil data izin yang memiliki lampiran dan sudah lewat 30 hari
        $oldPermissions = AttendancePermission::whereNotNull('lampiran')
            ->where('created_at', '<', $limitDate)
            ->get();

        $count = 0;
        foreach ($oldPermissions as $permit) {
            if (Storage::disk('public')->exists($permit->lampiran)) {
                Storage::disk('public')->delete($permit->lampiran);
                
                // Set kolom lampiran di DB jadi null agar tidak broken link
                $permit->update(['lampiran' => null]);
                $count++;
            }
        }

        return back()->with('success', "Berhasil membersihkan {$count} file lampiran lama (di atas 30 hari).");
    }
}
