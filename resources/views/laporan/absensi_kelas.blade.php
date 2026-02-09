@extends('layouts.app')

@section('title', 'Laporan Absensi Per Kelas')

@section('content')
    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Laporan Absensi Per Kelas</h2>
            @if($rombel)
                <div class="mb-3">
                    <a target="_blank" href="{{ route('laporan.absensi.kelas.pdf', request()->query()) }}"
                        class="btn btn-danger">
                        <i class="fa fa-file-pdf-o"></i> Export PDF
                    </a>

                    <a href="{{ route('laporan.absensi.kelas.excel', request()->query()) }}" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                    </a>
                </div>
            @endif

        </div>

        <div class="body">
            <form method="GET" class="row mb-4">
                <div class="col-md-4">
                    <label>Kelas</label>
                    <select name="rombel_id" class="form-control" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($rombels as $r)
                            <option value="{{ $r->id }}" {{ request('rombel_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_rombel }} ({{ $r->tahunAjar->nama ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Dari</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>

                <div class="col-md-3">
                    <label>Sampai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>

                <div class="col-md-2 align-self-end">
                    <button class="btn btn-primary btn-block">
                        Tampilkan
                    </button>
                </div>
            </form>

            @if($rombel)
                <h5>Kelas: <b>{{ $rombel->nama_rombel }}</b></h5>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Hadir</th>
                            <th>Izin</th>
                            <th>Alpha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td>{{ $row['nama'] }}</td>
                                <td class="text-success">{{ $row['hadir'] }}</td>
                                <td class="text-warning">{{ $row['izin'] }}</td>
                                <td class="text-danger">{{ $row['alpha'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Data tidak ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection