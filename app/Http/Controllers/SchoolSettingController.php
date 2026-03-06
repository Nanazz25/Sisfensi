<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\FaceLog;
use App\Models\Attendance;
use App\Models\AttendancePermission;
use App\Models\AnggotaRombel;
use App\Services\FaceLogService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SchoolSettingController extends Controller
{
    public function index()
    {
        $settings = SchoolSetting::all();
        return view('school-settings.index', compact('settings'));
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
        $students = AnggotaRombel::with(['pesertaDidik.user', 'rombonganBelajar.waliKelas.user'])->get();
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
}
