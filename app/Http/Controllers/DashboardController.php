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

        $data = [
            'schedules' => [],
            'stats' => [],
            'recent' => [],
            'walas_data' => null,
            'period' => $period
        ];

        if ($user->role === 'admin') {
            $data['stats'] = [
                'total_siswa' => PesertaDidik::count(),
                'total_guru' => Teacher::count(),
                'total_rombel' => RombonganBelajar::count(),
                'absensi_hari_ini' => [
                    'hadir' => Attendance::where('tanggal', $today)->where('status', 'hadir')->where('jenis_absensi', 'masuk')->count(),
                    'terlambat' => Attendance::where('tanggal', $today)->where('status', 'terlambat')->where('jenis_absensi', 'masuk')->count(),
                    'izin_sakit' => Attendance::where('tanggal', $today)->whereIn('status', ['izin', 'sakit'])->count(),
                    'alpha' => Attendance::where('tanggal', $today)->where('status', 'alpha')->count(),
                ],
            ];

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
            if ($teacher) {
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

                if ($anggotaRombel) {
                    $data['schedules'] = Schedule::with(['subject', 'teacher.user'])
                        ->where('rombongan_belajar_id', $anggotaRombel->rombongan_belajar_id)
                        ->where('hari', $hariIndo)
                        ->orderBy('jam_mulai', 'asc')
                        ->get();
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
