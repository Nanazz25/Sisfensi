<!DOCTYPE html>
<html>

<head>
    <title>Laporan Absensi Harian - {{ $rombel->nama_rombel }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
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
            text-align: center;
        }

        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #444;
            text-transform: uppercase;
            font-size: 9px;
        }

        .text-left {
            text-align: left !important;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
            color: #777;
        }

        .badge-danger {
            color: #d9534f;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin: 0; font-size: 18px;">LAPORAN ABSENSI HARIAN KELAS</h2>
        <h4 style="margin: 5px 0; color: #666; font-size: 14px;">{{ $rombel->tahunAjar->nama ?? '' }}</h4>
    </div>

    <div class="info">
        <table style="width: 100%;">
            <tr>
                <td style="width: 15%;"><strong>Kelas</strong></td>
                <td style="width: 35%;">: {{ $rombel->nama_rombel }}</td>
                <td style="width: 15%;"><strong>Wali Kelas</strong></td>
                <td style="width: 35%;">: {{ $rombel->waliKelas->nama_lengkap ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Periode</strong></td>
                <td colspan="3">: {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} s/d
                    {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 40%;">Nama Siswa</th>
                <th style="width: 15%;">Hadir</th>
                <th style="width: 15%;">Izin</th>
                <th style="width: 15%;">Sakit</th>
                <th style="width: 15%;">Alpha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="text-left" style="font-weight: 500;">{{ $row['nama'] }}</td>
                    <td>{{ $row['hadir'] }}</td>
                    <td>{{ $row['izin'] }}</td>
                    <td>{{ $row['sakit'] }}</td>
                    <td class="{{ $row['alpha'] > 0 ? 'badge-danger' : '' }}">{{ $row['alpha'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i') }}
    </div>
</body>

</html>