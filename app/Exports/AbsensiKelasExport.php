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

class AbsensiKelasExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
{
    protected $rombel_id, $start, $end;

    public function __construct($rombel_id, $start, $end)
    {
        $this->rombel_id = $rombel_id;
        $this->start = $start;
        $this->end = $end;
    }

    public function startCell(): string
    {
        return 'A6';
    }
    public function collection()
    {
        $rombel = RombonganBelajar::with('anggotaRombel.pesertaDidik.user')
            ->findOrFail($this->rombel_id);

        $rows = [];

        foreach ($rombel->anggotaRombel as $anggota) {
            $attendance = Attendance::where('anggota_rombel_id', $anggota->id)
                ->whereBetween('tanggal', [$this->start, $this->end])
                ->get();

            $rows[] = [
                $anggota->pesertaDidik->user->name,
                $attendance->where('status', 'hadir')->count(),
                $attendance->where('status', 'izin')->count(),
                $attendance->where('status', 'alpha')->count(),
            ];
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        return ['Nama', 'Hadir', 'Izin', 'Alpha'];
    }

    /**
     * Styling dasar
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Lebar kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 12,
            'C' => 12,
            'D' => 12,
        ];
    }

    /**
     * Styling lanjutan (border, background, freeze header)
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                /**
                 * JUDUL
                 */
                $sheet->mergeCells('A1:D1');
                $sheet->setCellValue('A1', 'LAPORAN ABSENSI KELAS');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                /**
                 * INFO KELAS & PERIODE
                 */
                $rombel = RombonganBelajar::find($this->rombel_id);

                $sheet->setCellValue('A3', 'Kelas');
                $sheet->setCellValue('B3', ': ' . ($rombel->nama_rombel ?? '-'));

                $sheet->setCellValue('A4', 'Periode');
                $sheet->setCellValue(
                    'B4',
                    ': ' .
                    \Carbon\Carbon::parse($this->start)->translatedFormat('d F Y') .
                    ' s/d ' .
                    \Carbon\Carbon::parse($this->end)->translatedFormat('d F Y')
                );

                $sheet->getStyle('A3:A4')->getFont()->setBold(true);

                /**
                 * FREEZE HEADER TABEL
                 */
                $sheet->freezePane('A7');

                /**
                 * HEADER TABEL (A6:D6)
                 */
                $sheet->getStyle('A6:D6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E5E7EB'],
                    ],
                ]);

                /**
                 * ZEBRA ROW
                 */
                for ($row = 7; $row <= $highestRow; $row++) {
                    if ($row % 2 == 1) {
                        $sheet->getStyle("A{$row}:D{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('F9FAFB');
                    }
                }

                /**
                 * BORDER DALAM
                 */
                $sheet->getStyle("A6:D{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(
                        new \PhpOffice\PhpSpreadsheet\Style\Color('E5E7EB')
                    );

                /**
                 * BORDER LUAR
                 */
                $sheet->getStyle("A6:D{$highestRow}")
                    ->getBorders()
                    ->getOutline()
                    ->setBorderStyle(Border::BORDER_MEDIUM)
                    ->setColor(
                        new \PhpOffice\PhpSpreadsheet\Style\Color('9CA3AF')
                    );

                /**
                 * ALIGNMENT
                 */
                $sheet->getStyle("B7:D{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A7:A{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}
