@extends('layouts.app')

@section('title', 'Kelola Presensi Manual')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Kelola Presensi Manual</h2>
                    <small>Gunakan halaman ini untuk merubah status kehadiran secara manual jika terjadi kesalahan input
                        atau error sistem.</small>
                </div>
                <div class="body">
                    <form action="{{ route('attendance.manual') }}" method="GET">
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group mb-lg-0 mb-3">
                                    <label class="font-weight-bold">Pilih Kelas</label>
                                    <select name="rombel_id" class="form-control select2" required>
                                        <option value="">-- Semua Kelas --</option>
                                        @foreach($rombels as $rombel)
                                            <option value="{{ $rombel->id }}" {{ request('rombel_id') == $rombel->id ? 'selected' : '' }}>
                                                {{ $rombel->nama_rombel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group mb-lg-0 mb-3">
                                    <label class="font-weight-bold">Pilih Tanggal</label>
                                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group mb-0">
                                    <label class="d-none d-md-block">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block py-2">
                                        <i class="fa fa-search mr-1"></i> Cari Data Presensi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if(request('rombel_id'))
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom spacing5">
                                <thead>
                                    <tr>
                                        <th>Siswa</th>
                                        <th>Status Saat Ini</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $student)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avtar-pic rounded-circle bg-light mr-2" style="width: 35px; height: 35px; line-height: 35px; text-align: center;">
                                                        <span class="font-weight-bold text-primary">{{ substr($student->pesertaDidik->user->name, 0, 1) }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="d-block font-weight-bold">{{ $student->pesertaDidik->user->name }}</span>
                                                        <small class="text-muted">{{ $student->pesertaDidik->no_induk }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $record = $student->attendances->first();
                                                @endphp

                                                @if($record)
                                                    @switch($record->status)
                                                        @case('hadir') <span class="badge badge-success">HADIR</span> @break
                                                        @case('terlambat') <span class="badge badge-warning">TERLAMBAT</span> @break
                                                        @case('izin') <span class="badge badge-info text-white">IZIN</span> @break
                                                        @case('sakit') <span class="badge badge-info text-white">SAKIT</span> @break
                                                        @default <span class="badge badge-danger">ALPHA</span>
                                                    @endswitch
                                                    <div class="mt-1"><small class="text-muted"><i class="fa fa-clock-o mr-1"></i>{{ date('H:i', strtotime($record->waktu_absen)) }}</small></div>
                                                @else
                                                    <span class="badge badge-default">BELUM ABSEN</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <form action="{{ route('attendance.manual-adjust') }}" method="POST" class="d-flex justify-content-end align-items-center flex-wrap">
                                                    @csrf
                                                    <input type="hidden" name="anggota_rombel_id" value="{{ $student->id }}">
                                                    <input type="hidden" name="tanggal" value="{{ $date }}">

                                                    <div class="input-group input-group-sm mb-1 mb-md-0" style="width: 130px;">
                                                        <select name="status" class="form-control custom-select" required>
                                                            <option value="">Status...</option>
                                                            <option value="pending">Pending</option>
                                                            <option value="hadir">Hadir</option>
                                                            <option value="terlambat">Terlambat</option>
                                                            <option value="izin">Izin</option>
                                                            <option value="sakit">Sakit</option>
                                                            <option value="alpha">Alpha</option>
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button type="submit" class="btn btn-primary btn-sm" title="Simpan Perubahan">
                                                                <i class="fa fa-save"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Kelas ini tidak memiliki anggota.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection