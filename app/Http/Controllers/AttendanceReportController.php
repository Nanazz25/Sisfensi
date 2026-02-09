<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\RombonganBelajar;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiKelasExport;

class AttendanceReportController extends Controller
{
    public function perKelas(Request $request)
    {
        $rombels = RombonganBelajar::with('tahunAjar')->get();

        $data = [];
        $rombel = null;

        $start = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $end = $request->end_date ?? Carbon::now()->toDateString();

        if ($request->filled('rombel_id')) {
            $rombel = RombonganBelajar::with(
                'anggotaRombel.pesertaDidik.user'
            )->findOrFail($request->rombel_id);

            foreach ($rombel->anggotaRombel as $anggota) {
                $attendance = Attendance::where('anggota_rombel_id', $anggota->id)
                    ->whereBetween('tanggal', [$start, $end])
                    ->get();

                $data[] = [
                    'nama' => $anggota->pesertaDidik->user->name,
                    'hadir' => $attendance->where('status', 'hadir')->count(),
                    'izin' => $attendance->where('status', 'izin')->count(),
                    'alpha' => $attendance->where('status', 'alpha')->count(),
                ];
            }
        }

        return view('laporan.absensi_kelas', compact(
            'rombels',
            'rombel',
            'data',
            'start',
            'end'
        ));
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
                'hadir' => $attendance->where('status', 'hadir')->count(),
                'izin' => $attendance->where('status', 'izin')->count(),
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
}
