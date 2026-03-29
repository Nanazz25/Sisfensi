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
                            <a class="nav-link px-4 py-3" id="presence-tab" data-toggle="tab" href="#presence">
                                <i class="fa fa-check-square-o mr-1"></i> Presensi Hari Ini
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-4 py-3" id="schedule-tab" data-toggle="tab" href="#schedule">
                                <i class="fa fa-calendar mr-1"></i> Jadwal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-4 py-3" id="character-tab" data-toggle="tab" href="#character">
                                <i class="fa fa-bar-chart mr-1"></i> Karakter
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
                        color: #007bff !important;
                        background: transparent !important;
                        border-bottom: 2px solid #007bff;
                    }

                    .nav-tabs-minimal .nav-link:hover:not(.active) {
                        color: #666;
                        border-bottom: 2px solid #e9ecef;
                    }

                    .presence-list-item {
                        padding: 8px 12px;
                        border-radius: 8px;
                        margin-bottom: 5px;
                        background: #fdfdfd;
                        border: 1px solid #f0f0f0;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
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

                        {{-- Tab Presensi --}}
                        <div class="tab-pane fade" id="presence">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold mb-3 text-success"><i class="fa fa-check-circle mr-1"></i>
                                        Hadir & Telat</h6>
                                    @forelse(array_merge($presenceLists['hadir'], $presenceLists['terlambat']) as $item)
                                        <div class="presence-list-item">
                                            <span>{{ $item->pesertaDidik->user->name }}</span>
                                            @php
                                                // Find specific status
                                                $isLate = collect($presenceLists['terlambat'])->contains('id', $item->id);
                                            @endphp
                                            <span class="badge {{ $isLate ? 'badge-warning' : 'badge-success' }}">
                                                {{ $isLate ? 'Terlambat' : 'Hadir' }}
                                            </span>
                                        </div>
                                    @empty
                                        <p class="text-muted small italic">Belum ada yang hadir.</p>
                                    @endforelse

                                    <h6 class="font-weight-bold mb-3 mt-4 text-info"><i class="fa fa-info-circle mr-1"></i>
                                        Izin & Sakit</h6>
                                    @forelse($presenceLists['izin_sakit'] as $item)
                                        <div class="presence-list-item">
                                            <span>{{ $item->pesertaDidik->user->name }}</span>
                                            <span class="badge badge-info">Izin/Sakit</span>
                                        </div>
                                    @empty
                                        <p class="text-muted small italic">Tidak ada yang izin.</p>
                                    @endforelse
                                </div>
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold mb-3 text-danger"><i class="fa fa-times-circle mr-1"></i>
                                        Alpha</h6>
                                    @forelse($presenceLists['alpha'] as $item)
                                        <div class="presence-list-item border-danger" style="background: #fffafa;">
                                            <span>{{ $item->pesertaDidik->user->name }}</span>
                                            <span class="badge badge-danger">Alpha</span>
                                        </div>
                                    @empty
                                        <p class="text-muted small italic">Tidak ada keterangan alpha.</p>
                                    @endforelse

                                    <h6 class="font-weight-bold mb-3 mt-4 text-muted"><i class="fa fa-clock-o mr-1"></i>
                                        Belum Absen</h6>
                                    @forelse($presenceLists['belum'] as $item)
                                        <div class="presence-list-item bg-light">
                                            <span class="text-muted">{{ $item->pesertaDidik->user->name }}</span>
                                            <span class="badge badge-secondary">Belum Absen</span>
                                        </div>
                                    @empty
                                        <p class="text-muted small italic">Semua sudah terdata.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- Tab Jadwal --}}
                        <div class="tab-pane fade" id="schedule">
                            <div class="row">
                                @php $hasSchedule = false; @endphp
                                @foreach(['senin', 'selasa', 'rabu', 'kamis', 'jumat'] as $hari)
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
                                                                    <div class="font-weight-bold text-dark">
                                                                        {{ $sch->subject->nama_mapel }}</div>
                                                                    <div class="text-muted" style="font-size: 11px;">
                                                                        {{ $sch->teacher->user->name ?? '-' }}</div>
                                                                </div>
                                                                <div class="text-right">
                                                                    <span
                                                                        class="badge badge-light border text-dark font-weight-normal px-2 py-1">
                                                                        {{ substr($sch->jam_mulai, 0, 5) }} -
                                                                        {{ substr($sch->jam_selesai, 0, 5) }}
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

                        {{-- Tab Karakter --}}
                        <div class="tab-pane fade" id="character">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Rata-rata Karakter Kelas</h6>
                                            <small class="text-muted">Analisis performa karakter berdasarkan seluruh anggota rombel</small>
                                        </div>
                                    </div>

                                    @if($averageAssessment->count() > 0)
                                        <div class="row">
                                            <div class="col-lg-7">
                                                <div style="height: 300px;">
                                                    <canvas id="classCharChart"></canvas>
                                                </div>
                                            </div>
                                            <div class="col-lg-5">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-custom spacing5">
                                                        <thead>
                                                            <tr>
                                                                <th>Kategori</th>
                                                                <th class="text-center">Skor</th>
                                                                <th width="100">Progres</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($averageAssessment as $avg)
                                                            <tr>
                                                                <td><span class="font-weight-bold">{{ $avg['name'] }}</span></td>
                                                                <td class="text-center"><strong>{{ $avg['score'] }}</strong>/10</td>
                                                                <td>
                                                                    <div class="progress progress-xs mb-0">
                                                                        <div class="progress-bar {{ $avg['score'] >= 7 ? 'bg-success' : ($avg['score'] >= 5 ? 'bg-warning' : 'bg-danger') }}" 
                                                                             role="progressbar" 
                                                                             style="width: {{ $avg['score'] * 10 }}%"></div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if(auth()->user()->role === 'siswa')
                                        <div class="mt-4 p-3 bg-light rounded border border-info">
                                            <div class="d-flex align-items-center">
                                                <div class="mr-3">
                                                    <i class="fa fa-lightbulb-o fa-2x text-info"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 font-weight-bold text-info">Analisis Pribadi Anda</h6>
                                                    <p class="mb-0 small text-muted">Grafik di atas menunjukkan rata-rata kelas. Kamu bisa melihat detil perbandingan nilai pribadimu dengan rata-rata kelas di halaman <a href="{{ route('assessment.show', auth()->id()) }}" class="font-weight-bold text-info">Detail Penilaian Pribadi</a>.</p>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fa fa-bar-chart fa-4x text-light mb-3"></i>
                                            <h5 class="text-muted">Data penilaian belum tersedia</h5>
                                            <p class="text-muted">Belum ada penilaian yang dilakukan untuk siswa di rombel ini.</p>
                                        </div>
                                    @endif
                                </div>
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

        // Chart Karakter Kelas (Radar Style)
        @if($averageAssessment->count() > 0)
        const charCtx = document.getElementById('classCharChart').getContext('2d');
        new Chart(charCtx, {
            type: 'radar',
            data: {
                labels: {!! json_encode($averageAssessment->pluck('name')) !!},
                datasets: [{
                    label: 'Rata-rata Kelas',
                    data: {!! json_encode($averageAssessment->pluck('score')) !!},
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    pointBackgroundColor: 'rgba(52, 152, 219, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { display: true },
                        suggestedMin: 0,
                        suggestedMax: 10,
                        ticks: { stepSize: 2, display: false }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Skor: ' + context.raw + ' / 10';
                            }
                        }
                    }
                }
            }
        });
        @endif
    </script>
@endsection

<x-modal-delete title="Hapus Anggota" message="Yakin ingin mengeluarkan anggota berikut?" />