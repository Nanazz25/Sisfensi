<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\RombonganBelajar;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiKelasExport;
use App\Exports\AbsensiMapelExport;

class AttendanceReportController extends Controller
{
    public function perKelas(Request $request)
    {
        $user = auth()->user();
        if ($user->role === 'siswa') {
            abort(403);
        }

        $rombels = RombonganBelajar::with('tahunAjar');

        // Guru Priority
        if ($user->role === 'guru') {
            $teacher = $user->teacher;
            $rombels = $rombels->orderByRaw("CASE WHEN wali_kelas_id = ? THEN 0 ELSE 1 END", [$teacher->id ?? 0]);
        }

        $rombels = $rombels->get();
        $data = [];
        $rombel = null;

        $start = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $end = $request->end_date ?? Carbon::now()->toDateString();

        if ($request->filled('rombel_id')) {
            $rombel = RombonganBelajar::with('anggotaRombel.pesertaDidik.user')->findOrFail($request->rombel_id);

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
        }

        return view('laporan.absensi_kelas', compact('rombels', 'rombel', 'data', 'start', 'end'));
    }

    public function perMapel(Request $request)
    {
        $user = auth()->user();
        if ($user->role === 'siswa') {
            abort(403);
        }

        $rombels = RombonganBelajar::with('tahunAjar');

        if ($user->role === 'guru') {
            $teacher = $user->teacher;
            $rombels = $rombels->orderByRaw("CASE WHEN wali_kelas_id = ? THEN 0 ELSE 1 END", [$teacher->id ?? 0]);
        }

        $rombels = $rombels->get();
        $rombel = null;
        $data = [];
        $start = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $end = $request->end_date ?? Carbon::now()->toDateString();

        if ($request->filled('rombel_id')) {
            $rombel = RombonganBelajar::findOrFail($request->rombel_id);

            $data = Attendance::with(['anggotaRombel.pesertaDidik.user', 'schedule.subject'])
                ->whereHas('anggotaRombel', function ($q) use ($rombel) {
                    $q->where('rombongan_belajar_id', $rombel->id);
                })
                ->where('jenis_absensi', 'pelajaran')
                ->whereBetween('tanggal', [$start, $end])
                ->orderBy('tanggal', 'desc')
                ->orderBy('waktu_absen', 'desc')
                ->get();
        }

        return view('laporan.absensi_mapel', compact('rombels', 'rombel', 'data', 'start', 'end'));
    }

    public function exportPdf(Request $request)
    {
        $rombel = RombonganBelajar::with(
            'anggotaRombel.pesertaDidik.user'
        )->findOrFail($request->rombel_id);

        $start = $request->start_date;
        $end = $request->end_date;

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

        $pdf = Pdf::loadView('laporan.absensi_kelas_pdf', compact(
            'rombel',
            'data',
            'start',
            'end'
        ))->setPaper('A4', 'portrait');

        return $pdf->download(
            'laporan-absensi-' . $rombel->nama_rombel . '.pdf'
        );
    }

    public function exportExcel(Request $request)
    {
        $rombel = RombonganBelajar::with(
            'anggotaRombel.pesertaDidik.user'
        )->findOrFail($request->rombel_id);

        return Excel::download(
            new AbsensiKelasExport(
                $request->rombel_id,
                $request->start_date,
                $request->end_date
            ),
            'laporan-absensi-' . $rombel->nama_rombel . '.xlsx'
        );
    }

    public function exportMapelPdf(Request $request)
    {
        if (!$request->filled('rombel_id')) {
            return back()->with('error', 'Pilih kelas terlebih dahulu');
        }

        $rombel = RombonganBelajar::findOrFail($request->rombel_id);
        $start = $request->start_date;
        $end = $request->end_date;

        $data = Attendance::with(['anggotaRombel.pesertaDidik.user', 'schedule.subject'])
            ->whereHas('anggotaRombel', function ($q) use ($rombel) {
                $q->where('rombongan_belajar_id', $rombel->id);
            })
            ->where('jenis_absensi', 'pelajaran')
            ->whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal', 'desc')
            ->get();

        $pdf = Pdf::loadView('laporan.absensi_mapel_pdf', compact(
            'rombel',
            'data',
            'start',
            'end'
        ))->setPaper('A4', 'portrait');

        return $pdf->download('laporan-absensi-mapel-' . $rombel->nama_rombel . '.pdf');
    }

    public function exportMapelExcel(Request $request)
    {
        if (!$request->filled('rombel_id')) {
            return back()->with('error', 'Pilih kelas terlebih dahulu');
        }

        $rombel = RombonganBelajar::findOrFail($request->rombel_id);
        return Excel::download(
            new AbsensiMapelExport($request->rombel_id, $request->start_date, $request->end_date),
            'laporan-absensi-mapel-' . $rombel->nama_rombel . '.xlsx'
        );
    }
}
