{{-- SUMMARY SECTION MOVED TO TOP FOR SPACE --}}
@if($walas_data)
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
            <div class="body p-0">
                <div class="row align-items-center no-gutters">
                    <div class="col-md-3 p-4 text-center text-md-left" style="background: #4e73df;">
                        <h5 class="font-weight-bold mb-1 text-white">Bimbingan Kelas</h5>
                        <p class="mb-0 text-white-50 small"><i class="fa fa-university mr-1"></i> {{ $walas_data['nama_rombel'] }}</p>
                    </div>
                    <div class="col-md-9 p-3 bg-white">
                        <div class="row text-center align-items-center">
                            <div class="col-4 border-right">
                                <h2 class="mb-0 font-weight-bold text-success">{{ $walas_data['hadir'] }}</h2>
                                <small class="text-muted font-weight-bold text-uppercase" style="letter-spacing: 1px; font-size: 10px;">HADIR</small>
                            </div>
                            <div class="col-4 border-right">
                                <h2 class="mb-0 font-weight-bold text-warning">{{ $walas_data['belum_absen'] }}</h2>
                                <small class="text-muted font-weight-bold text-uppercase" style="letter-spacing: 1px; font-size: 10px;">BELUM ABSEN</small>
                            </div>
                            <div class="col-4">
                                @if($walas_data['pending_izin'] > 0)
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="badge badge-danger px-3 py-1 mb-2 shadow-xs">{{ $walas_data['pending_izin'] }} Pengajuan</span>
                                        <a href="{{ route('attendance-permissions.index') }}" class="btn btn-sm btn-primary rounded-pill px-4 font-weight-bold shadow-sm">
                                            Verifikasi
                                        </a>
                                    </div>
                                @else
                                    <div class="text-success small">
                                        <i class="fa fa-check-circle-o fa-2x mb-1 d-block"></i>
                                        <span class="font-weight-bold">Semua Aman</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="col-lg-8 col-md-12">
    <div class="card shadow-sm border-0">
        <div class="header">
            <h2 class="font-weight-bold">Jadwal Mengajar Hari Ini</h2>
        </div>
        <div class="body">
            @if(!$is_school_day)
                <div class="alert alert-info border-0 shadow-xs mb-0 py-4 text-center bg-light">
                    <i class="fa fa-calendar-times-o fa-3x mb-3 d-block opacity-20"></i>
                    <h5 class="mb-1 font-weight-bold">Hari Libur Sekolah</h5>
                    <p class="mb-0 text-muted">Tidak ada jadwal mengajar hari ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-custom spacing5 mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Mata Pelajaran</th>
                                <th>Kelas</th>
                                <th width="150">Presensi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $schedule)
                                @php
                                    $now = \Carbon\Carbon::now()->toTimeString();
                                    $isCurrent = ($now >= $schedule->jam_mulai && $now <= $schedule->jam_selesai);
                                    $stats = $schedule->attendance_stats;
                                    $percent = $stats['total'] > 0 ? round(($stats['hadir'] / $stats['total']) * 100) : 0;
                                @endphp
                                <tr class="{{ $isCurrent ? 'bg-light-cyan' : '' }}">
                                    <td>{{ substr($schedule->jam_mulai, 0, 5) }} - {{ substr($schedule->jam_selesai, 0, 5) }}</td>
                                    <td><strong>{{ $schedule->subject->nama_mapel }}</strong></td>
                                    <td>{{ $schedule->rombonganBelajar->nama_rombel }}</td>
                                    <td>
                                        <a href="javascript:void(0);" class="view-attendance-detail d-block"
                                            data-id="{{ $schedule->id }}" title="Klik untuk lihat detail">
                                            <div class="d-flex justify-content-between mb-1 text-dark">
                                                <small class="font-weight-bold">{{ $stats['hadir'] }}/{{ $stats['total'] }}</small>
                                                <small class="text-muted">{{ $percent }}%</small>
                                            </div>
                                            <div class="progress progress-xxs mb-0">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $percent }}%;"></div>
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        @if($isCurrent)
                                            <span class="badge badge-soft-success">Mengajar</span>
                                        @elseif($now < $schedule->jam_mulai)
                                            <span class="badge badge-soft-warning">Akan Datang</span>
                                        @else
                                            <span class="badge badge-soft-secondary text-muted">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada jadwal hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="col-lg-4 col-md-12">
    <div class="card shadow-sm border-0">
        <div class="header">
            <h2 class="font-weight-bold">Akses Cepat</h2>
        </div>
        <div class="body">
            <div class="list-group list-group-custom">
                <a href="{{ route('attendance-permissions.index') }}" class="list-group-item list-group-item-action d-flex align-items-center mb-2 border-0 bg-light rounded shadow-xs">
                    <div class="icon-in-bg bg-primary text-white rounded mr-3 text-center" style="width: 35px; height: 35px; line-height:35px;"><i class="fa fa-envelope-o"></i></div>
                    <span class="font-weight-bold">Pengajuan Izin</span>
                </a>
                <a href="{{ route('laporan.absensi.kelas') }}" class="list-group-item list-group-item-action d-flex align-items-center border-0 bg-light rounded shadow-xs">
                    <div class="icon-in-bg bg-info text-white rounded mr-3 text-center" style="width: 35px; height: 35px; line-height:35px;"><i class="fa fa-file-text-o"></i></div>
                    <span class="font-weight-bold">Laporan Kehadiran</span>
                </a>
            </div>
        </div>
        @if($walas_data)
            <div class="card-footer bg-white border-top-0 pt-0 mt-2">
                <a href="{{ route('rombongan-belajar.show', $walas_data['id']) }}" class="btn btn-outline-primary btn-block font-weight-bold">
                    <i class="fa fa-external-link mr-1"></i> Kelola Rombel {{ $walas_data['nama_rombel'] }}
                </a>
            </div>
        @endif
    </div>
</div>
