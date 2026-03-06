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
            text-align: center;
        }

        th {
            background: #eee;
        }

        .text-left {
            text-align: left;
        }
    </style>
</head>

<body>
    <h3 align="center">LAPORAN ABSENSI HARIAN KELAS</h3>
    <p>
        Kelas: {{ $rombel->nama_rombel }} <br>
        Periode: {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} s/d
        {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th class="text-left">Nama</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="text-left">{{ $row['nama'] }}</td>
                    <td>{{ $row['hadir'] }}</td>
                    <td>{{ $row['izin'] }}</td>
                    <td>{{ $row['sakit'] }}</td>
                    <td>{{ $row['alpha'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>