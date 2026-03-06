<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\RombonganBelajar;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class AbsensiMapelExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
{
    protected $rombel_id, $start, $end, $subject_id, $teacher_id;

    public function __construct($rombel_id, $start, $end, $subject_id = null, $teacher_id = null)
    {
        $this->rombel_id = $rombel_id;
        $this->start = $start;
        $this->end = $end;
        $this->subject_id = $subject_id;
        $this->teacher_id = $teacher_id;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function collection()
    {
        $rombel = RombonganBelajar::with('anggotaRombel.pesertaDidik.user')->findOrFail($this->rombel_id);

        $query = Attendance::with(['anggotaRombel.pesertaDidik.user', 'schedule.subject', 'schedule.teacher'])
            ->whereHas('anggotaRombel', function ($q) use ($rombel) {
                $q->where('rombongan_belajar_id', $rombel->id);
            })
            ->where('jenis_absensi', 'pelajaran')
            ->whereBetween('tanggal', [$this->start, $this->end]);

        if ($this->subject_id) {
            $query->whereHas('schedule', function ($q) {
                $q->where('subject_id', $this->subject_id);
            });
        }

        if ($this->teacher_id) {
            $query->whereHas('schedule', function ($q) {
                $q->where('teacher_id', $this->teacher_id);
            });
        }

        $logs = $query->orderBy('tanggal', 'desc')->get();
        $grouped = $logs->groupBy(function ($item) {
            return $item->tanggal->toDateString() . '_' . $item->schedule_id;
        });

        $allStudents = $rombel->anggotaRombel->sortBy(function ($a) {
            return $a->pesertaDidik->user->name;
        });

        $finalData = collect();
        $index = 1;

        foreach ($grouped as $sessionLogs) {
            $first = $sessionLogs->first();
            foreach ($allStudents as $student) {
                $log = $sessionLogs->firstWhere('anggota_rombel_id', $student->id);
                $finalData->push([
                    $index++,
                    $student->pesertaDidik->user->name,
                    $first->schedule->subject->nama_mapel ?? '-',
                    $first->tanggal->translatedFormat('l, d/m/Y'),
                    $log ? $log->waktu_absen->format('H:i') : '-',
                    strtoupper($log ? $log->status : 'tidak hadir'),
                ]);
            }
        }

        return $finalData;
    }

    public function headings(): array
    {
        return ['No', 'Nama Siswa', 'Mata Pelajaran', 'Tanggal', 'Waktu', 'Status'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 35,
            'C' => 25,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'LAPORAN ABSENSI MATA PELAJARAN');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $rombel = RombonganBelajar::find($this->rombel_id);
                $sheet->setCellValue('A3', 'Kelas');
                $sheet->setCellValue('B3', ': ' . ($rombel->nama_rombel ?? '-'));
                $sheet->setCellValue('A4', 'Periode');
                $sheet->setCellValue('B4', ': ' . \Carbon\Carbon::parse($this->start)->translatedFormat('d F Y') . ' s/d ' . \Carbon\Carbon::parse($this->end)->translatedFormat('d F Y'));
                $sheet->getStyle('A3:A4')->getFont()->setBold(true);

                $sheet->getStyle('A6:F6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E5E7EB'],
                    ],
                ]);

                $sheet->getStyle("A6:F{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A7:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D7:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
