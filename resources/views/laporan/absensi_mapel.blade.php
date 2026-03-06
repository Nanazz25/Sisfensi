@extends('layouts.app')

@section('title', 'Laporan Absensi Mata Pelajaran')

@section('content')
    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Laporan Absensi Mata Pelajaran</h2>
            @if($rombel)
                <div class="mb-3">
                    <a target="_blank" href="{{ route('laporan.absensi.mapel.pdf', request()->query()) }}"
                        class="btn btn-danger">
                        <i class="fa fa-file-pdf-o"></i> Export PDF
                    </a>

                    <a href="{{ route('laporan.absensi.mapel.excel', request()->query()) }}" class="btn btn-success">
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
                    <input type="date" name="start_date" class="form-control" value="{{ $start }}">
                </div>

                <div class="col-md-3">
                    <label>Sampai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                </div>

                <div class="col-md-2 align-self-end">
                    <button class="btn btn-primary btn-block">
                        Tampilkan
                    </button>
                </div>
            </form>

            @if($rombel)
                <h5>Kelas: <b>{{ $rombel->nama_rombel }}</b></h5>

                <table class="table table-bordered mt-3 js-basic-example dataTable">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Nama Siswa</th>
                            <th>Mata Pelajaran</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td>{{ $row->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $row->waktu_absen->format('H:i') }}</td>
                                <td>{{ $row->anggotaRombel->pesertaDidik->user->name }}</td>
                                <td>{{ $row->schedule->subject->nama_mapel ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $row->status == 'hadir' ? 'success' : 'warning' }}">
                                        {{ strtoupper($row->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Data tidak ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection