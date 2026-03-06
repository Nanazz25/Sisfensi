<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FaceRecognitionService;
use App\Services\LocationService;
use App\Services\AttendanceService;
use App\Services\FaceLogService;
use App\Models\AnggotaRombel;
use App\Models\Attendance;
use Carbon\Carbon;

use App\Models\RombonganBelajar;

class AttendanceController extends Controller
{
    protected $faceService;
    protected $locationService;
    protected $attendanceService;
    protected $faceLogService;

    public function __construct(
        FaceRecognitionService $faceService,
        LocationService $locationService,
        AttendanceService $attendanceService,
        FaceLogService $faceLogService
    ) {
        $this->faceService = $faceService;
        $this->locationService = $locationService;
        $this->attendanceService = $attendanceService;
        $this->faceLogService = $faceLogService;
    }

    public function scanner($type = null)
    {
        // Prioritas: Route param -> Query param -> Default 'masuk'
        $type = $type ?? request('type', 'masuk');

        return view('attendance.scanner', [
            'type' => $type,
            'title' => 'Sistem Presensi Biometrik'
        ]);
    }

    public function verify(Request $request)
    {
        // Validasi data dari frontend (kamera, koordinat, tipe absen)
        $request->validate([
            'image' => 'required|string',
            'face_embedding' => 'required|array',
            'type' => 'required|in:masuk,mapel,pulang',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $now = Carbon::now();
        $today = $now->toDateString();

        // Cek apakah hari ini adalah hari sekolah
        $schoolDaysStr = \App\Models\SchoolSetting::where('key', 'hari_sekolah')->first()->value ?? 'senin,selasa,rabu,kamis,jumat';
        $schoolDays = explode(',', $schoolDaysStr);
        $dayName = strtolower($now->englishDayOfWeek);
        $map = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu'
        ];
        $hariIndo = $map[$dayName] ?? $dayName;

        if (!in_array($hariIndo, $schoolDays)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hari ini adalah hari libur sekolah. Tidak dapat melakukan presensi.'
            ], 403);
        }

        // Cari siswa berdasarkan data wajah (Face Embedding) via AI Service
        $siswa = $this->faceService->match($request->face_embedding);

        // Jika wajah tidak ditemukan di database siswa
        if (!$siswa) {
            // Catat log kegagalan (unrecognized) untuk audit keamanan
            $this->faceLogService->store(
                siswaId: null,
                confidence: null,
                result: 'unrecognized',
                base64Image: $request->image
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak dikenali!'
            ], 404);
        }

        // Validasi lokasi apakah siswa berada di dalam radius sekolah (Geofencing)
        $locationResult = $this->locationService->validate(
            $request->latitude,
            $request->longitude
        );

        if (!$locationResult['valid']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda berada di luar radius sekolah.'
            ], 403);
        }

        // Pastikan siswa tersebut terdaftar di sebuah kelas (Rombongan Belajar)
        $anggotaRombel = AnggotaRombel::where('peserta_didik_id', $siswa->id)
            ->latest()
            ->first();

        if (!$anggotaRombel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak terdaftar di kelas.'
            ], 400);
        }

        try {
            // Eksekusi logika inti absensi (simpan ke DB, cek jam masuk, hitung keterlambatan)
            $result = $this->attendanceService->process(
                siswa: $siswa,
                anggotaRombel: $anggotaRombel,
                type: $request->type,
                now: $now,
                locationData: [
                    'location' => $locationResult['location'],
                    'lat' => $request->latitude,
                    'lng' => $request->longitude,
                    'distance' => $locationResult['distance'],
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Absensi berhasil!',
                'nama' => $siswa->nama_lengkap,
                'waktu' => $now->format('H:i:s'),
                'is_late' => $result['is_late'],
                'late_info' => $result['late_info']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function manualView(Request $request)
    {
        $rombels = RombonganBelajar::all();
        $date = $request->date ?? date('Y-m-d');
        $students = [];

        if ($request->filled('rombel_id')) {
            $students = AnggotaRombel::with([
                'pesertaDidik.user',
                'attendances' => function ($q) use ($date) {
                    $q->where('tanggal', $date)->where('jenis_absensi', 'masuk');
                }
            ])->where('rombongan_belajar_id', $request->rombel_id)->get();
        }

        return view('attendance.manual_adjust', compact('rombels', 'students', 'date'));
    }

    public function manualAdjust(Request $request)
    {
        $request->validate([
            'anggota_rombel_id' => 'required|exists:anggota_rombel,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha,pending',
        ]);

        if ($request->status === 'pending') {

            Attendance::where('anggota_rombel_id', $request->anggota_rombel_id)
                ->where('tanggal', $request->tanggal)
                ->where('jenis_absensi', 'masuk')
                ->delete();

            return back()->with('success', 'Status dikembalikan menjadi Belum Absen.');
        }

        Attendance::updateOrCreate(
            [
                'anggota_rombel_id' => $request->anggota_rombel_id,
                'tanggal' => $request->tanggal,
            ],
            [
                'status' => $request->status,
                'jenis_absensi' => 'masuk',
                'metode' => 'manual',
                'waktu_absen' => now(),
            ]
        );

        return back()->with('success', 'Status kehadiran berhasil diperbarui secara manual.');
    }
}
