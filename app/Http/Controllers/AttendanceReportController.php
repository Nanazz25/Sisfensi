<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\RombonganBelajar;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Schedule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiKelasExport;
use App\Exports\AbsensiMapelExport;

class AttendanceReportController extends Controller
{
    private function calculateDates(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;

        if ($request->filled('month')) {
            $year = $request->year ?? Carbon::now()->year;
            $start = Carbon::createFromDate($year, $request->month, 1)->startOfMonth()->toDateString();
            $end = Carbon::createFromDate($year, $request->month, 1)->endOfMonth()->toDateString();
        } elseif ($request->filled('year') && !$request->filled('month')) {
            $start = Carbon::createFromDate($request->year, 1, 1)->startOfYear()->toDateString();
            $end = Carbon::createFromDate($request->year, 12, 31)->endOfYear()->toDateString();
        }

        // Default if everything is empty
        $start = $start ?? Carbon::now()->startOfMonth()->toDateString();
        $end = $end ?? Carbon::now()->toDateString();

        return ['start' => $start, 'end' => $end];
    }

    public function perKelas(Request $request)
    {
        $user = auth()->user();
        if ($user->role === 'siswa') {
            abort(403);
        }

        $teacher = $user->role === 'guru' ? $user->teacher : null;
        $rombelsQuery = RombonganBelajar::with(['tahunAjar', 'waliKelas']);
        $teachers = $user->role === 'admin' ? Teacher::all() : [];
        $tahunAjars = \App\Models\TahunAjar::orderBy('created_at', 'desc')->get();

        if ($user->role === 'guru') {
            $rombelsQuery->where('wali_kelas_id', $teacher->id ?? 0);
            if (!$request->filled('rombel_id')) {
                $myRombel = RombonganBelajar::where('wali_kelas_id', $teacher->id ?? 0)->first();
                if ($myRombel) {
                    $request->merge(['rombel_id' => $myRombel->id]);
                }
            } else {
                $requestedRombel = RombonganBelajar::find($request->rombel_id);
                if ($requestedRombel && $requestedRombel->wali_kelas_id !== ($teacher->id ?? 0)) {
                    abort(403, 'Anda bukan wali kelas di kelas ini.');
                }
            }
        }

        if ($user->role === 'admin' && $request->filled('teacher_id')) {
            $rombelsQuery->where('wali_kelas_id', $request->teacher_id);
        }

        if ($request->filled('tahun_ajar_id')) {
            $rombelsQuery->where('tahun_ajar_id', $request->tahun_ajar_id);
        }

        $rombels = $rombelsQuery->get();
        $dates = $this->calculateDates($request);
        $start = $dates['start'];
        $end = $dates['end'];

        $data = [];
        $rombel = null;

        if ($request->filled('rombel_id')) {
            $rombel = RombonganBelajar::with(['anggotaRombel.pesertaDidik.user', 'waliKelas'])->findOrFail($request->rombel_id);

            foreach ($rombel->anggotaRombel as $anggota) {
                $attendance = Attendance::where('anggota_rombel_id', $anggota->id)
                    ->whereBetween('tanggal', [$start, $end])
                    ->get();

                $data[] = [
                    'nis' => $anggota->pesertaDidik->no_induk,
                    'nama' => $anggota->pesertaDidik->user->name,
                    'hadir' => $attendance->where('jenis_absensi', 'masuk')->whereIn('status', ['hadir', 'terlambat'])->count(),
                    'izin' => $attendance->where('status', 'izin')->count(),
                    'sakit' => $attendance->where('status', 'sakit')->count(),
                    'alpha' => $attendance->where('status', 'alpha')->count(),
                ];
            }
        }

        return view('laporan.absensi_kelas', compact('rombels', 'rombel', 'data', 'start', 'end', 'teachers', 'tahunAjars'));
    }

    public function perMapel(Request $request)
    {
        $user = auth()->user();
        if ($user->role === 'siswa') {
            abort(403);
        }

        $teacher = $user->role === 'guru' ? $user->teacher : null;
        $rombelsQuery = RombonganBelajar::with('tahunAjar');
        $subjectsQuery = Subject::query();
        $teachers = $user->role === 'admin' ? Teacher::all() : [];
        $tahunAjars = \App\Models\TahunAjar::orderBy('created_at', 'desc')->get();

        if ($user->role === 'guru') {
            $taughtSchedules = Schedule::where('teacher_id', $teacher->id ?? 0)->get();
            $taughtRombelIds = $taughtSchedules->pluck('rombongan_belajar_id')->unique();
            $taughtSubjectIds = $taughtSchedules->pluck('subject_id')->unique();

            $rombelsQuery->whereIn('id', $taughtRombelIds);
            $subjectsQuery->whereIn('id', $taughtSubjectIds);

            if ($request->filled('rombel_id') && !$taughtRombelIds->contains($request->rombel_id)) {
                abort(403, 'Anda tidak mengajar di kelas ini.');
            }
            if ($request->filled('subject_id') && !$taughtSubjectIds->contains($request->subject_id)) {
                abort(403, 'Anda tidak mengajar mata pelajaran ini.');
            }
        }

        if ($user->role === 'admin' && $request->filled('teacher_id')) {
            $rombelsQuery->where('wali_kelas_id', $request->teacher_id);
        }

        if ($request->filled('tahun_ajar_id')) {
            $rombelsQuery->where('tahun_ajar_id', $request->tahun_ajar_id);
        }

        $rombels = $rombelsQuery->get();
        $subjects = $subjectsQuery->get();

        $dates = $this->calculateDates($request);
        $start = $dates['start'];
        $end = $dates['end'];
        $rombel = null;
        $data = [];

        $attendanceQuery = Attendance::with(['anggotaRombel.pesertaDidik.user', 'schedule.subject', 'schedule.teacher', 'schedule.rombonganBelajar'])
            ->where('jenis_absensi', 'pelajaran')
            ->whereBetween('tanggal', [$start, $end]);

        if ($request->filled('rombel_id')) {
            $rombel = RombonganBelajar::findOrFail($request->rombel_id);
            $attendanceQuery->whereHas('anggotaRombel', function ($q) use ($rombel) {
                $q->where('rombongan_belajar_id', $rombel->id);
            });
        }

        if ($request->filled('subject_id')) {
            $attendanceQuery->whereHas('schedule', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        if ($user->role === 'admin' && $request->filled('teacher_id')) {
            $attendanceQuery->whereHas('schedule', function ($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id);
            });
        } elseif ($user->role === 'guru') {
            $attendanceQuery->whereHas('schedule', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id ?? 0);
            });
        }

        $data = [];
        if ($request->filled('rombel_id')) {
            $rawLogs = $attendanceQuery->orderBy('tanggal', 'desc')
                ->orderBy('waktu_absen', 'desc')
                ->get();

            $data = $rawLogs->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->tanggal)->toDateString() . '_' . $item->schedule_id;
            })->map(function ($logs) {
                $first = $logs->first();
                $totalSiswa = \App\Models\AnggotaRombel::where('rombongan_belajar_id', $first->schedule->rombongan_belajar_id)->count();
                $hadirCount = $logs->whereIn('status', ['hadir', 'terlambat'])->count();

                return (object) [
                    'tanggal' => $first->tanggal,
                    'schedule_id' => $first->schedule_id,
                    'subject_name' => $first->schedule->subject->nama_mapel ?? '-',
                    'teacher_name' => $first->schedule->teacher->nama_lengkap ?? '-',
                    'hadir_summary' => $hadirCount . ' / ' . $totalSiswa,
                ];
            });
        }

        return view('laporan.absensi_mapel', compact('rombels', 'rombel', 'data', 'start', 'end', 'subjects', 'teachers', 'tahunAjars'));
    }

    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $rombel = RombonganBelajar::with('anggotaRombel.pesertaDidik.user')->findOrFail($request->rombel_id);

        if ($user->role === 'guru') {
            $teacher = $user->teacher;
            if ($rombel->wali_kelas_id !== ($teacher->id ?? 0)) {
                abort(403, 'Anda bukan wali kelas di kelas ini.');
            }
        }

        $dates = $this->calculateDates($request);
        $start = $dates['start'];
        $end = $dates['end'];

        $data = [];
        foreach ($rombel->anggotaRombel as $anggota) {
            $attendance = Attendance::where('anggota_rombel_id', $anggota->id)
                ->whereBetween('tanggal', [$start, $end])
                ->get();

            $data[] = [
                'nama' => $anggota->pesertaDidik->user->name,
                'hadir' => $attendance->where('jenis_absensi', 'masuk')->whereIn('status', ['hadir', 'terlambat'])->count(),
                'izin' => $attendance->where('status', 'izin')->count(),
                'sakit' => $attendance->where('status', 'sakit')->count(),
                'alpha' => $attendance->where('status', 'alpha')->count(),
            ];
        }

        $pdf = Pdf::loadView('laporan.absensi_kelas_pdf', compact('rombel', 'data', 'start', 'end'))->setPaper('A4', 'portrait');
        return $pdf->download('laporan-absensi-' . $rombel->nama_rombel . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        $rombel = RombonganBelajar::findOrFail($request->rombel_id);

        if ($user->role === 'guru') {
            $teacher = $user->teacher;
            if ($rombel->wali_kelas_id !== ($teacher->id ?? 0)) {
                abort(403, 'Anda bukan wali kelas di kelas ini.');
            }
        }

        $dates = $this->calculateDates($request);
        return Excel::download(
            new AbsensiKelasExport($request->rombel_id, $dates['start'], $dates['end']),
            'laporan-absensi-' . $rombel->nama_rombel . '.xlsx'
        );
    }

    public function exportMapelPdf(Request $request)
    {
        if (!$request->filled('rombel_id')) {
            return back()->with('error', 'Pilih kelas terlebih dahulu');
        }

        $user = auth()->user();
        $teacher = $user->role === 'guru' ? $user->teacher : null;
        $rombel = RombonganBelajar::with('anggotaRombel.pesertaDidik.user')->findOrFail($request->rombel_id);

        if ($user->role === 'guru') {
            $taughtInRombel = Schedule::where('teacher_id', $teacher->id ?? 0)
                ->where('rombongan_belajar_id', $rombel->id)
                ->exists();
            if (!$taughtInRombel) {
                abort(403, 'Anda tidak mengajar di kelas ini.');
            }
        }

        $dates = $this->calculateDates($request);
        $start = $dates['start'];
        $end = $dates['end'];

        $attendanceQuery = Attendance::with(['anggotaRombel.pesertaDidik.user', 'schedule.subject', 'schedule.teacher'])
            ->whereHas('anggotaRombel', function ($q) use ($rombel) {
                $q->where('rombongan_belajar_id', $rombel->id);
            })
            ->where('jenis_absensi', 'pelajaran')
            ->whereBetween('tanggal', [$start, $end]);

        if ($request->filled('subject_id')) {
            $attendanceQuery->whereHas('schedule', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        if ($user->role === 'admin' && $request->filled('teacher_id')) {
            $attendanceQuery->whereHas('schedule', function ($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id);
            });
        } elseif ($user->role === 'guru') {
            $attendanceQuery->whereHas('schedule', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id ?? 0);
            });
        }

        $logs = $attendanceQuery->orderBy('tanggal', 'desc')->get();
        $grouped = $logs->groupBy(function ($item) {
            return \Carbon\Carbon::parse($item->tanggal)->toDateString() . '_' . $item->schedule_id;
        });

        $allStudents = $rombel->anggotaRombel->sortBy(function ($a) {
            return $a->pesertaDidik->user->name;
        });

        $finalData = [];
        foreach ($grouped as $key => $sessionLogs) {
            $first = $sessionLogs->first();
            foreach ($allStudents as $student) {
                $log = $sessionLogs->firstWhere('anggota_rombel_id', $student->id);
                $finalData[] = (object) [
                    'tanggal' => $first->tanggal,
                    'waktu_absen' => $log ? $log->waktu_absen : null,
                    'nama_siswa' => $student->pesertaDidik->user->name,
                    'nama_mapel' => $first->schedule->subject->nama_mapel ?? '-',
                    'nama_guru' => $first->schedule->teacher->nama_lengkap ?? '-',
                    'status' => $log ? $log->status : 'tidak hadir'
                ];
            }
        }

        $pdf = Pdf::loadView('laporan.absensi_mapel_pdf', [
            'rombel' => $rombel,
            'data' => $finalData,
            'start' => $start,
            'end' => $end
        ])->setPaper('A4', 'landscape'); // Better for subject reports

        return $pdf->download('laporan-absensi-mapel-' . $rombel->nama_rombel . '.pdf');
    }

    public function exportMapelExcel(Request $request)
    {
        if (!$request->filled('rombel_id')) {
            return back()->with('error', 'Pilih kelas terlebih dahulu');
        }

        $user = auth()->user();
        $teacher = $user->role === 'guru' ? $user->teacher : null;
        $rombel = RombonganBelajar::findOrFail($request->rombel_id);

        if ($user->role === 'guru') {
            $taughtInRombel = Schedule::where('teacher_id', $teacher->id ?? 0)
                ->where('rombongan_belajar_id', $rombel->id)
                ->exists();
            if (!$taughtInRombel) {
                abort(403, 'Anda tidak mengajar di kelas ini.');
            }
        }

        $dates = $this->calculateDates($request);
        return Excel::download(
            new AbsensiMapelExport(
                $request->rombel_id,
                $dates['start'],
                $dates['end'],
                $request->subject_id,
                $user->role === 'admin' ? $request->teacher_id : ($teacher->id ?? null)
            ),
            'laporan-absensi-mapel-' . $rombel->nama_rombel . '.xlsx'
        );
    }

    public function getDetailMapel(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'tanggal' => 'required|date',
        ]);

        $schedule = Schedule::with(['rombonganBelajar', 'subject'])->findOrFail($request->schedule_id);
        $user = auth()->user();

        if ($user->role === 'guru') {
            if ($schedule->teacher_id !== ($user->teacher->id ?? 0)) {
                abort(403, 'Anda tidak memiliki akses ke detail mata pelajaran ini.');
            }
        }

        $date = $request->tanggal;
        $students = \App\Models\AnggotaRombel::where('rombongan_belajar_id', $schedule->rombongan_belajar_id)
            ->with(['pesertaDidik.user'])->get()->sortBy(function ($anggota) {
                return $anggota->pesertaDidik->user->name ?? '';
            })->values();

        $attendances = Attendance::where('schedule_id', $schedule->id)->where('tanggal', $date)->get()->keyBy('anggota_rombel_id');

        $result = $students->map(function ($student) use ($attendances) {
            $att = $attendances->get($student->id);
            $status = $att ? $att->status : 'tidak hadir';
            $waktu = $att && $att->waktu_absen ? $att->waktu_absen->format('H:i') : '-';
            return [
                'name' => $student->pesertaDidik->user->name ?? '-',
                'status' => $status,
                'waktu' => $waktu,
                'pills' => $this->getStatusPill($status)
            ];
        });

        return response()->json([
            'subject' => $schedule->subject->nama_mapel,
            'class' => $schedule->rombonganBelajar->nama_rombel,
            'date' => Carbon::parse($date)->translatedFormat('l, d F Y'),
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
                return '<span class="badge badge-secondary">Tidak Hadir</span>';
        }
    }
}
