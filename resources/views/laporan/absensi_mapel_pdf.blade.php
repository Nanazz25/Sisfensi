<!DOCTYPE html>
<html>

<head>
    <title>Laporan Absensi Mata Pelajaran - {{ $rombel->nama_rombel }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .info {
            margin-bottom: 15px;
        }

        .info table {
            border: none;
            width: 100%;
        }

        .info td {
            border: none;
            padding: 2px 0;
            text-align: left;
            vertical-align: top;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #ddd;
            padding: 6px 4px;
        }

        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #444;
            text-transform: uppercase;
            font-size: 8px;
            text-align: center;
        }

        .text-center {
            text-align: center !important;
        }

        .text-left {
            text-align: left !important;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 8px;
            color: #777;
        }

        .status-badge {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin: 0; font-size: 16px;">LAPORAN ABSENSI MATA PELAJARAN</h2>
        <h4 style="margin: 5px 0; color: #666; font-size: 12px;">{{ $rombel->tahunAjar->nama ?? '' }}</h4>
    </div>

    <div class="info">
        <table style="width: 100%;">
            <tr>
                <td style="width: 15%;"><strong>Kelas</strong></td>
                <td style="width: 35%;">: {{ $rombel->nama_rombel }}</td>
                <td style="width: 15%;"><strong>Periode</strong></td>
                <td style="width: 35%;">: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} s/d
                    {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 10%;">Waktu</th>
                <th class="text-left" style="width: 28%;">Nama Siswa</th>
                <th class="text-left" style="width: 25%;">Mata Pelajaran / Guru</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                <tr style="{{ $row->status === 'tidak hadir' ? 'background-color: #fafafa; color: #999;' : '' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $row->tanggal->translatedFormat('l, d/m/Y') }}</td>
                    <td class="text-center">{{ $row->waktu_absen ? $row->waktu_absen->format('H:i') : '-' }}</td>
                    <td class="text-left" style="font-weight: 500;">{{ $row->nama_siswa }}</td>
                    <td class="text-left">
                        <strong>{{ $row->nama_mapel }}</strong><br>
                        <small style="color: inherit;">{{ $row->nama_guru }}</small>
                    </td>
                    <td class="text-center status-badge">
                        {{ $row->status }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
    </div>
</body>

</html>