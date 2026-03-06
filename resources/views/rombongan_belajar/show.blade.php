@extends('layouts.app')

@section('title', 'Detail Rombongan Belajar')

@section('content')
    <div class="row">
        <div class="col-md-4">
            {{-- Info Rombel --}}
            <div class="card shadow-none border">
                <div class="header">
                    <h2>Informasi Rombel</h2>
                </div>
                <div class="body">
                    <div class="text-center mb-4">
                        <h3 class="mb-0 font-weight-bold text-primary">{{ $rombel->nama_rombel }}</h3>
                        <p class="text-muted">{{ $rombel->jurusan->nama_jurusan ?? '-' }}</p>
                    </div>
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Tahun Ajar</td>
                            <th class="text-right">{{ $rombel->tahunAjar->nama }}</th>
                        </tr>
                        <tr>
                            <td class="text-muted">Semester</td>
                            <th class="text-right">{{ ucfirst($rombel->tahunAjar->semester) }}</th>
                        </tr>
                        <tr>
                            <td class="text-muted">Wali Kelas</td>
                            <th class="text-right">{{ $rombel->waliKelas->user->name ?? '-' }}</th>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Siswa</td>
                            <th class="text-right">{{ $anggota->count() }} Orang</th>
                        </tr>
                    </table>
                    <div class="mt-3">
                        <a href="{{ route('rombongan-belajar.index') }}" class="btn btn-secondary btn-block">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>

            {{-- Statistik Presensi --}}
            <div class="card shadow-none border">
                <div class="header d-flex justify-content-between align-items-center">
                    <h2>Statistik</h2>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                            data-toggle="dropdown">
                            {{ $period === 'today' ? 'Hari Ini' : ($period === 'week' ? 'Minggu' : 'Bulan') }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="?period=today">Hari Ini</a>
                            <a class="dropdown-item" href="?period=week">1 Minggu</a>
                            <a class="dropdown-item" href="?period=month">1 Bulan</a>
                        </div>
                    </div>
                </div>
                <div class="body">
                    <div style="height: 180px; position: relative;">
                        <canvas id="attendanceChart"></canvas>
                        <div
                            style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none;">
                            <h3 class="mb-0 font-weight-bold text-primary">{{ $percentage }}%</h3>
                            <small class="text-muted">Hadir</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="row text-center list-unstyled small">
                            <div class="col-6 mb-2">
                                <span class="text-success font-weight-bold">{{ $stats['hadir'] }}</span>
                                <div class="text-muted">Hadir</div>
                            </div>
                            <div class="col-6 mb-2">
                                <span class="text-warning font-weight-bold">{{ $stats['terlambat'] }}</span>
                                <div class="text-muted">Telat</div>
                            </div>
                            <div class="col-6">
                                <span class="text-info font-weight-bold">{{ $stats['izin_sakit'] }}</span>
                                <div class="text-muted">Izin/Skt</div>
                            </div>
                            <div class="col-6">
                                <span class="text-danger font-weight-bold">{{ $stats['alpha'] }}</span>
                                <div class="text-muted">Alpha</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Form Tambah (Hanya Admin) --}}
            @if(auth()->user()->role === 'admin')
                <div class="card shadow-none border mb-3">
                    <div class="header">
                        <h2>Tambah Anggota</h2>
                    </div>
                    <div class="body">
                        <form action="{{ route('rombels.anggota.store', $rombel->id) }}" method="POST">
                            @csrf
                            <div class="row align-items-center">
                                <div class="col">
                                    <select name="peserta_didik_id" class="form-control" required>
                                        <option value="">-- Pilih Siswa --</option>
                                        @foreach ($siswaAvailable as $siswa)
                                            <option value="{{ $siswa->id }}">
                                                {{ $siswa->user->name }} — ({{ $siswa->no_induk }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-success">
                                        <i class="fa fa-plus"></i> Tambah
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Tabs --}}
            <div class="card shadow-none border">
                <div class="header p-0 border-bottom">
                    <ul class="nav nav-tabs nav-tabs-minimal border-0">
                        <li class="nav-item">
                            <a class="nav-link active px-4 py-3" id="members-tab" data-toggle="tab" href="#members">
                                <i class="fa fa-users mr-1"></i> Anggota
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-4 py-3" id="schedule-tab" data-toggle="tab" href="#schedule">
                                <i class="fa fa-calendar mr-1"></i> Jadwal
                            </a>
                        </li>
                    </ul>
                </div>
                <style>
                    .nav-tabs-minimal .nav-link { 
                        color: #a0a0a0; 
                        border: none;
                        border-bottom: 2px solid transparent;
                        border-radius: 0;
                        transition: all 0.2s ease;
                        font-size: 13px;
                        font-weight: 600;
                    }
                    .nav-tabs-minimal .nav-link.active { 
                        color: #333 !important; 
                        background: transparent !important;
                        border-bottom: 2px solid #333;
                    }
                    .nav-tabs-minimal .nav-link:hover:not(.active) {
                        color: #666;
                        border-bottom: 2px solid #e9ecef;
                    }
                </style>
                <div class="body">
                    <div class="tab-content">
                        {{-- Tab Anggota --}}
                        <div class="tab-pane fade show active" id="members">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="40">#</th>
                                            <th>Nama Siswa</th>
                                            <th width="150">NIS</th>
                                            @if(auth()->user()->role === 'admin' || (auth()->user()->role === 'guru' && $isWalas))
                                                <th width="120" class="text-center">Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($anggota as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="font-weight-bold">{{ $item->pesertaDidik->user->name }}</td>
                                                <td>{{ $item->pesertaDidik->no_induk }}</td>
                                                @if(auth()->user()->role === 'admin' || (auth()->user()->role === 'guru' && $isWalas))
                                                    <td class="text-center">
                                                        <a href="{{ route('peserta-didik.show', $item->peserta_didik_id) }}"
                                                            class="btn btn-sm btn-info" title="Detail">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        @if(auth()->user()->role === 'admin')
                                                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                                data-name="{{ $item->pesertaDidik->user->name }}"
                                                                data-action="{{ route('rombels.anggota.destroy', ['rombel' => $rombel->id, 'anggotaRombel' => $item->id]) }}"
                                                                data-toggle="modal" data-target="#deleteModal" title="Hapus">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4">Belum ada anggota</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab Jadwal --}}
                        <div class="tab-pane fade" id="schedule">
                            <div class="row">
                                @php $hasSchedule = false; @endphp
                                @foreach(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $hari)
                                    @if(isset($schedules[$hari]))
                                        @php $hasSchedule = true; @endphp
                                        <div class="col-md-6 mb-3">
                                            <div class="card shadow-none border">
                                                <div class="header bg-light py-2 px-3 border-bottom">
                                                    <h5 class="mb-0 font-weight-bold text-dark text-capitalize">{{ $hari }}</h5>
                                                </div>
                                                <div class="list-group list-group-flush small">
                                                    @foreach($schedules[$hari] as $sch)
                                                        <div class="list-group-item py-2 px-3">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <div class="font-weight-bold text-dark">{{ $sch->subject->nama_mapel }}</div>
                                                                    <div class="text-muted" style="font-size: 11px;">{{ $sch->teacher->user->name ?? '-' }}</div>
                                                                </div>
                                                                <div class="text-right">
                                                                    <span class="badge badge-light border text-dark font-weight-normal px-2 py-1">
                                                                        {{ substr($sch->jam_mulai, 0, 5) }} - {{ substr($sch->jam_selesai, 0, 5) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                @if(!$hasSchedule)
                                    <div class="col-12 text-center py-5">
                                        <i class="fa fa-calendar-times-o fa-3x text-muted mb-3"></i>
                                        <p class="mb-0">Jadwal belum tersedia untuk kelas ini.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
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
                    backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
@endsection

<x-modal-delete title="Hapus Anggota" message="Yakin ingin mengeluarkan anggota berikut?" />