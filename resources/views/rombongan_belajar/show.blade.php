@extends('layouts.app')

@section('title', 'Detail Rombongan Belajar')

@section('content')
    <div class="row">

        {{-- =======================
        DETAIL ROMBEL
        ======================== --}}
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="header pb-0">
                    <h2 class="mb-0">Detail Rombel</h2>
                </div>

                <div class="body pt-2">
                    <table class="table table-sm mb-2">
                        <tr>
                            <th width="100">Rombel</th>
                            <td>{{ $rombel->nama_rombel }}</td>
                        </tr>
                        <tr>
                            <th>Tahun Ajar</th>
                            <td>
                                {{ $rombel->tahunAjar->nama }}
                                <span class="badge badge-info">
                                    {{ ucfirst($rombel->tahunAjar->semester) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Wali</th>
                            <td>{{ $rombel->waliKelas->user->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Siswa</th>
                            <td>
                                <span class="badge badge-success">
                                    {{ $anggota->count() }} orang
                                </span>
                            </td>
                        </tr>
                    </table>

                    <a href="{{ route('rombongan-belajar.index') }}" class="btn btn-secondary btn-sm btn-block">
                        Kembali
                    </a>
                </div>
            </div>

            {{-- Statistik Presensi Card --}}
            <div class="card">
                <div class="header pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Statistik Kehadiran</h2>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                            {{ $period === 'today' ? 'Hari Ini' : ($period === 'week' ? '1 Minggu' : '1 Bulan') }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="?period=today">Hari Ini</a>
                            <a class="dropdown-item" href="?period=week">1 Minggu</a>
                            <a class="dropdown-item" href="?period=month">1 Bulan</a>
                        </div>
                    </div>
                </div>
                <div class="body text-center">
                    <div style="height: 200px; position: relative;">
                        <canvas id="attendanceChart"></canvas>
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <h3 class="mb-0 font-weight-bold text-primary">{{ $percentage }}%</h3>
                            <small class="text-muted">Hadir</small>
                        </div>

                    </div>
                    <div class="mt-3 text-left">
                        <div class="d-flex justify-content-between mb-1">
                            <small><i class="fa fa-circle text-success mr-1"></i> Hadir</small>
                            <small class="font-weight-bold">{{ $stats['hadir'] }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small><i class="fa fa-circle text-warning mr-1"></i> Terlambat</small>
                            <small class="font-weight-bold">{{ $stats['terlambat'] }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small><i class="fa fa-circle text-info mr-1"></i> Izin/Sakit</small>
                            <small class="font-weight-bold">{{ $stats['izin_sakit'] }}</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small><i class="fa fa-circle text-danger mr-1"></i> Alpha</small>
                            <small class="font-weight-bold">{{ $stats['alpha'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">

            {{-- FORM TAMBAH (Hanya Admin) --}}
            @if(auth()->user()->role === 'admin')
                <div class="card mb-3">
                    <div class="header pb-0">
                        <h2 class="mb-0">Tambah Anggota</h2>
                    </div>

                    <div class="body pt-2">
                        @error('peserta_didik_id')
                            <div class="alert alert-danger py-1 mb-2">
                                {{ $message }}
                            </div>
                        @enderror

                        <form action="{{ route('rombels.anggota.store', $rombel->id) }}" method="POST" class="form-inline">
                            @csrf

                            <select name="peserta_didik_id" class="form-control mr-2" required>
                                <option value="">-- pilih siswa --</option>
                                @foreach ($siswaAvailable as $siswa)
                                    <option value="{{ $siswa->id }}">
                                        {{ $siswa->user->name }} — {{ $siswa->nis }}
                                    </option>
                                @endforeach
                            </select>

                            <button class="btn btn-primary">
                                <i class="fa fa-plus"></i> Tambah
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="header pb-0">
                    <h2 class="mb-0">Daftar Anggota</h2>
                </div>

                <div class="body pt-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="40">#</th>
                                    <th>Nama</th>
                                    <th width="120">NIS</th>
                                    {{-- Aksi muncul jika Admin ATAU Guru yang jadi Walas --}}
                                    @if(auth()->user()->role === 'admin' || (auth()->user()->role === 'guru' && $isWalas))
                                        <th width="90" class="text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($anggota as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $item->pesertaDidik->user->name }}
                                        </td>
                                        <td>
                                            {{ $item->pesertaDidik->no_induk }}
                                        </td>

                                        @if(auth()->user()->role === 'admin' || (auth()->user()->role === 'guru' && $isWalas))
                                            <td class="text-center">
                                                {{-- Button Detail untuk Admin & Walas --}}
                                                <a href="{{ route('peserta-didik.show', $item->pesertaDidik->id) }}"
                                                    class="btn btn-info btn-sm" title="Detail">
                                                    <i class="fa fa-eye"></i>
                                                </a>

                                                {{-- Button Delete HANYA untuk Admin --}}
                                                @if(auth()->user()->role === 'admin')
                                                                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                                                data-name="{{ $item->pesertaDidik->user->name }}" data-action="{{ route('rombels.anggota.destroy', [
                                                        'rombel' => $rombel->id,
                                                        'anggotaRombel' => $item->id
                                                    ]) }}" data-toggle="modal" data-target="#deleteModal"
                                                                                title="Hapus">
                                                                                <i class="fa fa-trash"></i>
                                                                            </button>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        @php $cols = (auth()->user()->role === 'admin' || (auth()->user()->role === 'guru' && $isWalas)) ? 4 : 3; @endphp
                                        <td colspan="{{ $cols }}" class="text-center text-muted">
                                            Belum ada anggota
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('afterAppScripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Terlambat', 'Izin/Sakit', 'Alpha'],
                datasets: [{
                    data: [
                                {{ $stats['hadir'] }},
                                {{ $stats['terlambat'] }},
                                {{ $stats['izin_sakit'] }},
                        {{ $stats['alpha'] }}
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#17a2b8',
                        '#dc3545'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            }
        });
    </script>
@endsection

<x-modal-delete title="Hapus Anggota" message="Yakin ingin menghapus anggota berikut dari rombel?" />