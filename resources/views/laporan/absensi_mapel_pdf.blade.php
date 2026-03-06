<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        th {
            background: #eee;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <h3 align="center">LAPORAN ABSENSI MATA PELAJARAN</h3>
    <p>
        Kelas: {{ $rombel->nama_rombel }} <br>
        Periode: {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} s/d
        {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Nama Siswa</th>
                <th>Mata Pelajaran</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $row->tanggal->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $row->waktu_absen->format('H:i') }}</td>
                    <td>{{ $row->anggotaRombel->pesertaDidik->user->name }}</td>
                    <td>{{ $row->schedule->subject->nama_pelajaran ?? '-' }}</td>
                    <td class="text-center">{{ strtoupper($row->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>