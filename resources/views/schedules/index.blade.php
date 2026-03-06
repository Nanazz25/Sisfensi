@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header d-flex justify-content-between align-items-center">
                    <div>
                        <h2>Daftar Kelas</h2>
                        <small>Pilih kelas untuk melihat detail jadwal pelajaran</small>
                    </div>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('schedules.create') }}" class="btn btn-success">
                            <i class="fa fa-plus"></i> Tambah Jadwal
                        </a>
                    @endif
                </div>
                <div class="body">
                    <!-- Filter -->
                    <form action="{{ route('schedules.index') }}" method="GET" class="row mb-4">
                        <div class="col-md-4 mb-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari Nama Kelas..."
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select name="jurusan_id" class="form-control">
                                <option value="">Semua Jurusan</option>
                                @foreach($jurusans as $j)
                                    <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>
                                        {{ $j->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <select name="tahun_ajar_id" class="form-control">
                                <option value="">Semua Tahun Ajar</option>
                                @foreach($tahunAjars as $t)
                                    <option value="{{ $t->id }}" {{ request('tahun_ajar_id') == $t->id ? 'selected' : '' }}>
                                        {{ $t->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </form>

                    @forelse($rombels as $tahunAjar => $items)
                        <h4 class="mt-4 mb-3" style="border-left: 5px solid #007bff; padding-left: 15px;">{{ $tahunAjar }}</h4>
                        <div class="row">
                            @foreach($items as $rombel)
                                <div class="col-lg-3 col-md-4 col-6 mb-4">
                                    <a href="{{ route('schedules.show', $rombel->id) }}" class="card-link text-decoration-none">
                                        <div class="card text-center mb-0 h-100 shadow-none border">
                                            <div class="body">
                                                <div class="mb-3">
                                                    <i class="fa fa-university fa-3x text-muted"></i>
                                                </div>
                                                <h4 class="mb-1 font-weight-bold text-dark">{{ $rombel->nama_rombel }}</h4>
                                                <p class="text-muted small mb-0">{{ $rombel->jurusan->nama_jurusan ?? '-' }}</p>
                                                <hr class="my-2">
                                                <span class="small text-muted">Wali Kelas:</span><br>
                                                <strong class="small text-dark">{{ $rombel->waliKelas->user->name ?? '-' }}</strong>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fa fa-search fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Data kelas tidak ditemukan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-link {
            transition: all 0.2s;
        }

        .card-link:hover .card {
            border-color: #007bff !important;
            background: #f8f9fa;
        }

        .card-link:hover h4 {
            color: #007bff !important;
        }
    </style>
@endsection