<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #eee;
        }
    </style>
</head>

<body>

    <h3 align="center">Laporan Absensi Kelas</h3>
    <p>
        Kelas: {{ $rombel->nama_rombel }} <br>
        Periode: {{ $start }} s/d {{ $end }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Alpha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['hadir'] }}</td>
                    <td>{{ $row['izin'] }}</td>
                    <td>{{ $row['alpha'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>