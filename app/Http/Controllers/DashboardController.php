<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\PesertaDidik;
use App\Models\AnggotaRombel;
use Carbon\Carbon;

use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\AttendancePermission;
use App\Models\RombonganBelajar;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $hariIndo = $this->translateHari(strtolower(Carbon::now()->englishDayOfWeek));
        $period = $request->period ?? 'week';

        $schoolDaysStr = \App\Models\SchoolSetting::where('key', 'hari_sekolah')->first()->value ?? 'senin,selasa,rabu,kamis,jumat';
        $schoolDays = explode(',', $schoolDaysStr);
        $isSchoolDay = in_array($hariIndo, $schoolDays);
        $data = [
            'schedules' => [],
            'stats' => [],
            'recent' => [],
            'walas_data' => null,
            'period' => $period,
            'is_school_day' => $isSchoolDay
        ];

        if ($user->role === 'admin') {
            $data['stats'] = [
                'total_siswa' => PesertaDidik::count(),
                'total_guru' => Teacher::count(),
                'total_rombel' => RombonganBelajar::count(),
                'pending_permissions' => AttendancePermission::where('status', 'pending')->count(),
                'absensi_hari_ini' => [
                    'hadir' => Attendance::where('tanggal', $today)->where('status', 'hadir')->where('jenis_absensi', 'masuk')->count(),
                    'terlambat' => Attendance::where('tanggal', $today)->where('status', 'terlambat')->where('jenis_absensi', 'masuk')->count(),
                    'izin_sakit' => Attendance::where('tanggal', $today)->whereIn('status', ['izin', 'sakit'])->count(),
                    'alpha' => Attendance::where('tanggal', $today)->where('status', 'alpha')->count(),
                ],
            ];

            // Filter data for charts
            $data['rombels'] = RombonganBelajar::orderBy('nama_rombel')->get();
            $data['jurusans'] = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
            $data['tahunAjars'] = \App\Models\TahunAjar::orderBy('created_at', 'desc')->get();

            // Top Classes ranking
            $startDate = ($period === 'month') ? Carbon::now()->startOfMonth()->toDateString() : Carbon::now()->startOfWeek()->toDateString();

            $data['top_classes'] = RombonganBelajar::withCount(['anggotaRombel as total_anggota'])
                ->get()
                ->map(function ($rombel) use ($startDate, $today) {
                    $anggotaIds = $rombel->anggotaRombel->pluck('id');
                    $hadir = Attendance::whereIn('anggota_rombel_id', $anggotaIds)
                        ->whereBetween('tanggal', [$startDate, $today])
                        ->whereIn('status', ['hadir', 'terlambat'])
                        ->where('jenis_absensi', 'masuk')
                        ->count();

                    $diffDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($today)) + 1;
                    $total_possible = $rombel->total_anggota * $diffDays;
                    $percentage = $total_possible > 0 ? round(($hadir / $total_possible) * 100, 1) : 0;

                    return [
                        'id' => $rombel->id,
                        'nama' => $rombel->nama_rombel,
                        'percentage' => $percentage
                    ];
                })->sortByDesc('percentage')->take(5);

        } elseif ($user->role === 'guru') {
            $teacher = $user->teacher;
            if ($teacher && $isSchoolDay) {
                // Personal Schedule with Attendance Counts
                $data['schedules'] = Schedule::with(['subject', 'rombonganBelajar'])
                    ->withCount([
                        'rombonganBelajar as total_siswa' => function ($q) {
                            $q->select(\DB::raw('count(*)'));
                        }
                    ]) // This is tricky because it's a relationship of a relationship.
                    ->where('teacher_id', $teacher->id)
                    ->where('hari', $hariIndo)
                    ->orderBy('jam_mulai', 'asc')
                    ->get()
                    ->map(function ($schedule) use ($today) {
                        $totalSiswa = AnggotaRombel::where('rombongan_belajar_id', $schedule->rombongan_belajar_id)->count();
                        $hadirCount = Attendance::where('schedule_id', $schedule->id)
                            ->where('tanggal', $today)
                            ->count();

                        $schedule->attendance_stats = [
                            'hadir' => $hadirCount,
                            'total' => $totalSiswa,
                            'belum' => max(0, $totalSiswa - $hadirCount)
                        ];
                        return $schedule;
                    });
            }

            if ($teacher) {
                // Walas Data
                $rombel = RombonganBelajar::where('wali_kelas_id', $teacher->id)->first();
                if ($rombel) {
                    $anggotaIds = AnggotaRombel::where('rombongan_belajar_id', $rombel->id)->pluck('id');
                    $hadirCount = Attendance::whereIn('anggota_rombel_id', $anggotaIds)
                        ->where('tanggal', $today)
                        ->where('jenis_absensi', 'masuk')
                        ->count();

                    $data['walas_data'] = [
                        'id' => $rombel->id,
                        'nama_rombel' => $rombel->nama_rombel,
                        'hadir' => $hadirCount,
                        'belum_absen' => max(0, count($anggotaIds) - $hadirCount),
                        'pending_izin' => AttendancePermission::whereIn('anggota_rombel_id', $anggotaIds)->where('status', 'pending')->count(),
                    ];
                }
            }
        } elseif ($user->role === 'siswa') {
            $peserta = $user->pesertaDidik;

            if ($peserta) {
                $anggotaRombel = $peserta->anggotaRombel()->latest('id')->first();

                if ($anggotaRombel && $isSchoolDay) {
                    $data['schedules'] = Schedule::with(['subject', 'teacher.user'])
                        ->where('rombongan_belajar_id', $anggotaRombel->rombongan_belajar_id)
                        ->where('hari', $hariIndo)
                        ->orderBy('jam_mulai', 'asc')
                        ->get();
                }

                // Global today's attendance summary for student
                if ($anggotaRombel) {
                    $data['attendance_summary'] = [
                        'masuk' => Attendance::where('anggota_rombel_id', $anggotaRombel->id)->where('tanggal', $today)->where('jenis_absensi', 'masuk')->first(),
                        'pulang' => Attendance::where('anggota_rombel_id', $anggotaRombel->id)->where('tanggal', $today)->where('jenis_absensi', 'pulang')->first(),
                        'permissions' => AttendancePermission::where('anggota_rombel_id', $anggotaRombel->id)->where('status', 'pending')->count(),
                    ];
                }
            }
        }

        return view('dashboard.index', $data);
    }

    public function getAttendanceDetail($scheduleId)
    {
        $today = Carbon::today()->toDateString();
        $schedule = Schedule::findOrFail($scheduleId);

        $students = AnggotaRombel::with(['pesertaDidik.user'])
            ->where('rombongan_belajar_id', $schedule->rombongan_belajar_id)
            ->get();

        $attendance = Attendance::where('schedule_id', $scheduleId)
            ->where('tanggal', $today)
            ->pluck('status', 'anggota_rombel_id');

        $result = $students->map(function ($student) use ($attendance) {
            return [
                'name' => $student->pesertaDidik->user->name,
                'status' => $attendance[$student->id] ?? 'belum absen',
                'pills' => $this->getStatusPill($attendance[$student->id] ?? 'belum absen')
            ];
        });

        return response()->json([
            'subject' => $schedule->subject->nama_mapel,
            'class' => $schedule->rombonganBelajar->nama_rombel,
            'data' => $result
        ]);
    }

    public function getAttendanceChartData(Request $request)
    {
        $period = $request->period ?? 'week'; // day, week, month, year
        $filterType = $request->filter_type ?? 'all'; // all, rombel, angkatan, jurusan
        $filterValue = $request->filter_value;

        // Base query
        $query = Attendance::where('jenis_absensi', 'masuk');

        // Apply Entity Filters
        if ($filterType === 'rombel' && $filterValue) {
            $query->whereHas('anggotaRombel', function ($q) use ($filterValue) {
                $q->where('rombongan_belajar_id', $filterValue);
            });
        } elseif ($filterType === 'angkatan' && $filterValue) {
            $romanMap = ['10' => 'X', '11' => 'XI', '12' => 'XII'];
            $roman = $romanMap[$filterValue] ?? $filterValue;

            $query->whereHas('anggotaRombel.rombonganBelajar', function ($q) use ($roman) {
                $q->where('nama_rombel', 'like', $roman . ' %')
                    ->orWhere('nama_rombel', 'like', $roman . '-%')
                    ->orWhere('nama_rombel', $roman);
            });
        } elseif ($filterType === 'jurusan' && $filterValue) {
            $query->whereHas('anggotaRombel.rombonganBelajar', function ($q) use ($filterValue) {
                $q->where('jurusan_id', $filterValue);
            });
        }

        $labels = [];
        $datasets = [
            'hadir' => [],
            'terlambat' => [],
            'izin_sakit' => [],
            'alpha' => []
        ];

        if ($period === 'day') {
            $today = Carbon::today()->toDateString();
            $labels = ['Hadir', 'Terlambat', 'Izin/Sakit', 'Alpha'];
            $dataset = [
                $query->clone()->where('tanggal', $today)->where('status', 'hadir')->count(),
                $query->clone()->where('tanggal', $today)->where('status', 'terlambat')->count(),
                $query->clone()->where('tanggal', $today)->whereIn('status', ['izin', 'sakit'])->count(),
                $query->clone()->where('tanggal', $today)->where('status', 'alpha')->count(),
            ];
            return response()->json([
                'type' => 'doughnut',
                'labels' => $labels,
                'datasets' => [['data' => $dataset, 'backgroundColor' => ['#28a745', '#ffc107', '#17a2b8', '#dc3545']]]
            ]);
        }

        $count = ($period === 'week') ? 7 : (($period === 'month') ? 30 : 12);

        for ($i = $count - 1; $i >= 0; $i--) {
            if ($period === 'week' || $period === 'month') {
                $date = Carbon::today()->subDays($i);
                $dateStr = $date->toDateString();
                $labels[] = $date->translatedFormat('d M');

                $datasets['hadir'][] = $query->clone()->where('tanggal', $dateStr)->where('status', 'hadir')->count();
                $datasets['terlambat'][] = $query->clone()->where('tanggal', $dateStr)->where('status', 'terlambat')->count();
                $datasets['izin_sakit'][] = $query->clone()->where('tanggal', $dateStr)->whereIn('status', ['izin', 'sakit'])->count();
                $datasets['alpha'][] = $query->clone()->where('tanggal', $dateStr)->where('status', 'alpha')->count();
            } else {
                // Year
                $month = Carbon::today()->subMonths($i);
                $labels[] = $month->translatedFormat('F');
                $start = $month->startOfMonth()->toDateString();
                $end = $month->endOfMonth()->toDateString();

                $datasets['hadir'][] = $query->clone()->whereBetween('tanggal', [$start, $end])->where('status', 'hadir')->count();
                $datasets['terlambat'][] = $query->clone()->whereBetween('tanggal', [$start, $end])->where('status', 'terlambat')->count();
                $datasets['izin_sakit'][] = $query->clone()->whereBetween('tanggal', [$start, $end])->whereIn('status', ['izin', 'sakit'])->count();
                $datasets['alpha'][] = $query->clone()->whereBetween('tanggal', [$start, $end])->where('status', 'alpha')->count();
            }
        }

        return response()->json([
            'type' => 'line',
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Hadir', 'data' => $datasets['hadir'], 'borderColor' => '#28a745', 'backgroundColor' => 'rgba(40, 167, 69, 0.1)', 'fill' => true],
                ['label' => 'Terlambat', 'data' => $datasets['terlambat'], 'borderColor' => '#ffc107', 'backgroundColor' => 'rgba(255, 193, 7, 0.1)', 'fill' => true],
                ['label' => 'Izin/Sakit', 'data' => $datasets['izin_sakit'], 'borderColor' => '#17a2b8', 'backgroundColor' => 'rgba(23, 162, 184, 0.1)', 'fill' => true],
                ['label' => 'Alpha', 'data' => $datasets['alpha'], 'borderColor' => '#dc3545', 'backgroundColor' => 'rgba(220, 53, 69, 0.1)', 'fill' => true],
            ]
        ]);
    }

    private function getStatusPill($status)
    {
        switch ($status) {
            case 'hadir':
                return '<span class="badge badge-success">Hadir</span>';
            case 'terlambat':
                return '<span class="badge badge-warning">Terlambat</span>';
            case 'izin':
                return '<span class="badge badge-info">Izin</span>';
            case 'sakit':
                return '<span class="badge badge-info">Sakit</span>';
            case 'alpha':
                return '<span class="badge badge-danger">Alpha</span>';
            default:
                return '<span class="badge badge-secondary">Belum Absen</span>';
        }
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
