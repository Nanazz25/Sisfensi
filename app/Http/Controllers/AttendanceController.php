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
        $type = $type ?? request('type', 'masuk');
        $user = auth()->user();
        $schedules = collect();
        $mapelAttended = [];

        // Mode Mapel: Ambil jadwal hari ini
        if ($type === 'mapel' && $user) {
            $now = Carbon::now();
            $today = $now->toDateString();

            $dayMap = [
                'monday' => 'senin', 'tuesday' => 'selasa', 'wednesday' => 'rabu',
                'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu', 'sunday' => 'minggu'
            ];
            $hariIndo = $dayMap[strtolower($now->englishDayOfWeek)] ?? strtolower($now->englishDayOfWeek);
            
            // Cari rombel (prioritas siswa, fallback ke rombel pertama buat admin testing)
            $anggotaRombel = null;
            if ($user->role === 'siswa' && $user->pesertaDidik) {
                $anggotaRombel = AnggotaRombel::where('peserta_didik_id', $user->pesertaDidik->id)
                    ->whereHas('rombonganBelajar.tahunAjar', function($q) {
                        $q->where('is_active', true);
                    })->first();
            } else if ($user->role === 'admin') {
                $anggotaRombel = AnggotaRombel::whereHas('rombonganBelajar.tahunAjar', function($q) {
                        $q->where('is_active', true);
                    })->first();
            }

            if ($anggotaRombel) {
                $schedules = \App\Models\Schedule::with('subject', 'teacher.user')
                    ->where('rombongan_belajar_id', $anggotaRombel->rombongan_belajar_id)
                    ->where('hari', $hariIndo)
                    ->orderBy('jam_mulai', 'asc')
                    ->get();
                
                $mapelAttended = Attendance::where('anggota_rombel_id', $anggotaRombel->id)
                    ->where('tanggal', $today)
                    ->where('jenis_absensi', 'pelajaran')
                    ->pluck('schedule_id')
                    ->toArray();
            }
        }

        return view('attendance.scanner', [
            'type' => $type,
            'title' => 'Sistem Presensi Biometrik',
            'schedules' => $schedules,
            'mapelAttended' => $mapelAttended,
            'hasMasukToday' => $user->role === 'siswa' && $user->active_rombel ? Attendance::where('anggota_rombel_id', $user->active_rombel->id)->where('tanggal', Carbon::now()->toDateString())->where('jenis_absensi', 'masuk')->whereIn('status', ['hadir', 'terlambat'])->exists() : true
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
                'message' => 'Hari ini adalah hari libur sekolah (akhir pekan).'
            ], 403);
        }

        // --- PROTEKSI TABEL HARI LIBUR ---
        $holiday = \App\Models\Holiday::where('date', $today)->first();
        if ($holiday) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hari ini adalah hari libur: ' . $holiday->description
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
        // Ambil anggota rombel yang aktif di tahun ajar berjalan
        $anggotaRombel = AnggotaRombel::where('peserta_didik_id', $siswa->id)
            ->whereHas('rombonganBelajar.tahunAjar', function($q) {
                $q->where('is_active', true);
            })
            ->first();

        // Fallback jika belum diatur tahun ajarnya tapi siswa sudah ada (backward compatibility)
        if (!$anggotaRombel) {
            $anggotaRombel = AnggotaRombel::where('peserta_didik_id', $siswa->id)
                ->latest('id')
                ->first();
        }

        if (!$anggotaRombel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak terdaftar di kelas.'
            ], 400);
        }

        try {
            $latestLedgerId = \App\Models\PointLedger::where('user_id', $siswa->user_id ?? ($siswa->user->id ?? 0))->max('id') ?? 0;

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

            // Fetch new point ledgers created in process through observer
            $newLedgers = \App\Models\PointLedger::where('user_id', $siswa->user_id ?? ($siswa->user->id ?? 0))
                ->where('id', '>', $latestLedgerId)
                ->get();
            
            $pointDelta = $newLedgers->sum('amount');
            $pointInfo = null;

            if ($pointDelta !== 0) {
                $rules = [];
                foreach ($newLedgers as $l) {
                    $desc = $l->description;
                    if (($pos = strpos($desc, ' pada ')) !== false) {
                        $desc = substr($desc, 0, $pos);
                    }
                    $rules[] = str_replace('[Koreksi] ', '', $desc);
                }
                $ruleString = implode(', ', $rules);
                if ($result['is_exempted'] ?? false) {
                    $ruleString = "💎 Voucher Digunakan, " . $ruleString;
                }
                $operator = $pointDelta > 0 ? '+' : '';
                $pointInfo = [
                    'amount' => $pointDelta,
                    'text' => "{$operator}{$pointDelta} Poin ({$ruleString})"
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Absensi berhasil!',
                'nama' => $siswa->nama_lengkap,
                'waktu' => $now->format('H:i:s'),
                'is_late' => $result['is_late'],
                'is_exempted' => $result['is_exempted'] ?? false,
                'attendance_status' => $result['status'],
                'late_info' => $result['late_info'],
                'point_info' => $pointInfo
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
        $rombels = RombonganBelajar::whereHas('tahunAjar', function($q) {
            $q->where('is_active', true);
        })->orderBy('nama_rombel')->get();

        $date = $request->date ?? date('Y-m-d');
        $type = $request->type ?? 'masuk';
        $scheduleId = $request->schedule_id;
        $students = [];
        $schedules = [];

        if ($request->filled('rombel_id')) {
            // Jika mode Mapel, ambil jadwal untuk kelas tersebut di hari itu
            if ($type === 'pelajaran') {
                $dayMap = [
                    'monday' => 'senin', 'tuesday' => 'selasa', 'wednesday' => 'rabu',
                    'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu', 'sunday' => 'minggu'
                ];
                $dayName = $dayMap[strtolower(Carbon::parse($date)->englishDayOfWeek)] ?? 'senin';
                
                $schedules = \App\Models\Schedule::with('subject')
                    ->where('rombongan_belajar_id', $request->rombel_id)
                    ->where('hari', $dayName)
                    ->get();
            }

            $studentQuery = AnggotaRombel::with(['pesertaDidik.user'])
                ->where('rombongan_belajar_id', $request->rombel_id);

            // Filter Search (Nama/NIS)
            if ($request->filled('q')) {
                $q = $request->q;
                $studentQuery->whereHas('pesertaDidik', function($query) use ($q) {
                    $query->where('no_induk', 'LIKE', "%{$q}%")
                          ->orWhere('nama_lengkap', 'LIKE', "%{$q}%")
                          ->orWhereHas('user', function($uq) use ($q) {
                              $uq->where('name', 'LIKE', "%{$q}%");
                          });
                });
            }

            $students = $studentQuery->get();
            
            // Pasangkan status absen untuk masing-masing siswa
            foreach ($students as $key => $student) {
                $query = Attendance::whereHas('anggotaRombel', function($q) use ($student) {
                        $q->where('peserta_didik_id', $student->peserta_didik_id);
                    })
                    ->where('tanggal', $date)
                    ->where('jenis_absensi', $type);
                
                if ($type === 'pelajaran' && $scheduleId) {
                    $query->where('schedule_id', $scheduleId);
                }

                $student->current_attendance = $query->first();

                // Filter berdasarkan Status Kehadiran (Post-processing)
                if ($request->filled('status')) {
                    $status = $request->status;
                    $currentStatus = $student->current_attendance ? $student->current_attendance->status : 'belum_absen';
                    
                    if ($status !== $currentStatus) {
                        unset($students[$key]);
                    }
                }
            }
        }

        return view('attendance.manual_adjust', compact('rombels', 'students', 'date', 'schedules'));
    }

    public function manualAdjust(Request $request)
    {
        $request->validate([
            'anggota_rombel_ids' => 'required|array',
            'anggota_rombel_ids.*' => 'exists:anggota_rombel,id',
            'peserta_didik_ids' => 'required|array',
            'peserta_didik_ids.*' => 'exists:peserta_didik,id',
            'rombel_id' => 'required|exists:rombongan_belajar,id',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha,pending',
            'tanggal' => 'required|date'
        ]);

        // --- CEK HARI SEKOLAH (Proteksi Libur) ---
        $schoolDaysStr = \App\Models\SchoolSetting::where('key', 'hari_sekolah')->first()->value ?? 'senin,selasa,rabu,kamis,jumat';
        $schoolDays = explode(',', strtolower($schoolDaysStr));
        $targetDate = Carbon::parse($request->tanggal);
        $dayName = strtolower($targetDate->englishDayOfWeek);
        $map = [
            'monday' => 'senin', 'tuesday' => 'selasa', 'wednesday' => 'rabu',
            'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu', 'sunday' => 'minggu'
        ];
        $hariIndo = $map[$dayName] ?? $dayName;

        if (!in_array($hariIndo, $schoolDays)) {
            return back()->with('error', "Gagal! Data absensi tidak dapat diubah karena tanggal " . $targetDate->translatedFormat('l, d F Y') . " adalah hari libur sekolah (Akhir Pekan).");
        }

        // --- PROTEKSI TABEL HARI LIBUR ---
        $holiday = \App\Models\Holiday::where('date', $targetDate->toDateString())->first();
        if ($holiday) {
            return back()->with('error', "Gagal! Tanggal " . $targetDate->translatedFormat('d F Y') . " adalah hari libur: " . $holiday->description);
        }

        // PROTEKSI KEAMANAN: Pastikan rombel yang akan diubah adalah rombel aktif!
        $rombelTarget = RombonganBelajar::with('tahunAjar')->findOrFail($request->rombel_id);
        if (!$rombelTarget->tahunAjar || !$rombelTarget->tahunAjar->is_active) {
            return back()->with('error', 'Gagal! Kelas ini berada di tahun ajaran non-aktif (Arsip). Data sejarah tidak boleh diubah secara manual.');
        }
        
        $successCount = 0;
        
        \DB::transaction(function () use ($request, &$successCount) {
            $type = $request->type ?? 'masuk';
            $scheduleId = $request->schedule_id;

            foreach ($request->anggota_rombel_ids as $index => $anggotaRombelId) {
                $targetSiswaId = $request->peserta_didik_ids[$index];
                
                // 1. PEMBERSIHAN KHUSUS: Hapus rekaman tipe tertentu pada hari tersebut
                $allMemberships = AnggotaRombel::where('peserta_didik_id', $targetSiswaId)->pluck('id');

                $query = Attendance::whereIn('anggota_rombel_id', $allMemberships)
                    ->where('tanggal', $request->tanggal)
                    ->where('jenis_absensi', $type);
                
                if ($type === 'pelajaran' && $scheduleId) {
                    $query->where('schedule_id', $scheduleId);
                }

                $existingEntries = $query->get();

                foreach ($existingEntries as $entry) {
                    $entry->delete(); // Picu Observer 'deleted' untuk reversal poin
                }

                // 2. Jika status baru BUKAN pending, buat rekaman baru
                if ($request->status !== 'pending') {
                    $waktuAbsen = '00:00:00';
                    if ($type === 'masuk') {
                        $waktuAbsen = $request->status === 'hadir' ? '07:00:00' : ($request->status === 'terlambat' ? '07:45:00' : '00:00:00');
                    } else if ($type === 'pelajaran' && $scheduleId) {
                        // Ambil jam mulai mapel untuk waktu absen manual mapel
                        $sch = \App\Models\Schedule::find($scheduleId);
                        $waktuAbsen = $sch ? $sch->jam_mulai : '00:00:00';
                    }

                    Attendance::create([
                        'anggota_rombel_id' => $anggotaRombelId,
                        'tanggal' => $request->tanggal,
                        'waktu_absen' => $waktuAbsen,
                        'status' => $request->status,
                        'jenis_absensi' => $type,
                        'schedule_id' => ($type === 'pelajaran') ? $scheduleId : null,
                        'metode' => 'manual'
                    ]);
                }
                
                $successCount++;
            }
        });

        return back()->with('success', "Berhasil memperbarui status untuk {$successCount} siswa. Sinkronisasi poin telah dilakukan.");
    }

    public function getCalendarData(Request $request, $peserta_didik_id = null)
    {
        $user = auth()->user();
        
        // 1. Menentukan ID Peserta Didik
        if (!$peserta_didik_id) {
            if ($user->role === 'siswa') {
                $peserta_didik_id = $user->pesertaDidik->id ?? null;
            } else {
                return response()->json([]);
            }
        }
        
        if (!$peserta_didik_id) return response()->json([]);

        $pesertaDidik = \App\Models\PesertaDidik::find($peserta_didik_id);
        if (!$pesertaDidik) return response()->json([]);

        // 2. Otorisasi (Pengecekan Hak Akses)
        if ($user->role === 'siswa' && $pesertaDidik->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        if ($user->role === 'guru') {
            $teacher = $user->teacher;
            if ($teacher) {
                $rombelFromSchedules = \App\Models\Schedule::where('teacher_id', $teacher->id)->pluck('rombongan_belajar_id');
                $rombelFromWali = \App\Models\RombonganBelajar::where('wali_kelas_id', $teacher->id)->pluck('id');
                $rombelIds = $rombelFromSchedules->concat($rombelFromWali)->unique();
                
                $isInClass = \App\Models\AnggotaRombel::where('peserta_didik_id', $peserta_didik_id)
                    ->whereIn('rombongan_belajar_id', $rombelIds)->exists();
                    
                if (!$isInClass) {
                    return response()->json(['error' => 'Unauthorized. Siswa bukan dari kelas Anda.'], 403);
                }
            } else {
                return response()->json(['error' => 'Tidak punya akses'], 403);
            }
        }

        // 3. Mengambil Data Kehadiran
        $events = [];
        $anggotaRombelIds = \App\Models\AnggotaRombel::where('peserta_didik_id', $peserta_didik_id)->pluck('id');
        
        $start = $request->start ? Carbon::parse($request->start) : Carbon::now()->startOfMonth();
        $end = $request->end ? Carbon::parse($request->end) : Carbon::now()->endOfMonth();

        // Rekaman absensi (hanya "masuk" untuk kemudahan tampikan pada kalender)
        $attendances = Attendance::whereIn('anggota_rombel_id', $anggotaRombelIds)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('jenis_absensi', 'masuk')
            ->get();

        foreach ($attendances as $att) {
            $color = '#6c757d'; // warna bawaan
            switch ($att->status) {
                case 'hadir': $color = '#28a745'; break;
                case 'terlambat': $color = '#ffc107'; break;
                case 'izin': $color = '#17a2b8'; break;
                case 'sakit': $color = '#17a2b8'; break;
                case 'alpha': $color = '#dc3545'; break;
            }

            $events[] = [
                'id' => 'att_' . $att->id,
                'title' => ucfirst($att->status),
                'start' => $att->tanggal,
                'color' => $color,
                'allDay' => true,
            ];
        }

        // 4. Menghasilkan Jadwal "Libur" untuk akhir pekan atau hari non-sekolah
        $schoolDaysStr = \App\Models\SchoolSetting::where('key', 'hari_sekolah')->first()->value ?? 'senin,selasa,rabu,kamis,jumat';
        $schoolDays = explode(',', $schoolDaysStr);
        
        $enToId = [
            'monday' => 'senin', 'tuesday' => 'selasa', 'wednesday' => 'rabu',
            'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu', 'sunday' => 'minggu'
        ];

        $currentDate = $start->copy();
        while ($currentDate <= $end) {
            $dayNameEn = strtolower($currentDate->englishDayOfWeek);
            $dayNameId = $enToId[$dayNameEn] ?? $dayNameEn;

            if (!in_array($dayNameId, $schoolDays)) {
                $events[] = [
                    'id' => 'holiday_' . $currentDate->format('Ymd'),
                    'title' => 'Libur',
                    'start' => $currentDate->toDateString(),
                    'backgroundColor' => '#ffe5e5',
                    'borderColor' => '#ffe5e5',
                    'textColor' => '#dc3545',
                    'allDay' => true
                ];
            }
            $currentDate->addDay();
        }

        // --- Tambahkan Hari Libur dari Tabel ---
        $allHolidays = \App\Models\Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();
        foreach ($allHolidays as $h) {
            $events[] = [
                'id' => 'holiday_db_' . $h->id,
                'title' => $h->description,
                'start' => $h->date->toDateString(),
                'backgroundColor' => '#ffe5e5',
                'borderColor' => '#ffe5e5',
                'textColor' => '#dc3545',
                'allDay' => true
            ];
        }

        return response()->json($events);
    }
}
