@extends('layouts.app')

@section('title', 'Dashboard')

@section('afterAppStyles')
    @vite('resources/css/dashboard.css')
@endsection

@section('content')
    <div class="row clearfix row-deck">
        <div class="col-lg-12">
            <div class="card welcome-card bg-gradient-primary text-white border-0 shadow-sm">
                <div class="body d-flex align-items-center py-4">
                    <div class="icon-box bg-white-20 rounded-circle text-center mr-3"
                        style="width: 60px; height: 60px; line-height: 60px;">
                        <i class="fa fa-smile-o font-30"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold text-info">Selamat Datang, {{ Auth::user()->name }}!</h4>
                        <p class="mb-0 opacity-75"><i class="fa fa-calendar-o mr-1"></i>
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
            {{-- SUMMARY CARDS --}}
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget shadow-sm">
                    <div class="body d-flex align-items-center">
                        <div class="icon bg-info text-white rounded mr-3 shadow-sm"
                            style="width: 50px; height: 50px; line-height: 50px; text-align: center;">
                            <i class="fa fa-users font-20"></i>
                        </div>
                        <div class="content">
                            <div class="text mb-0 text-uppercase font-weight-bold font-12 text-info">Total Siswa
                            </div>
                            <h4 class="number mb-0 font-weight-bold">{{ $stats['total_siswa'] }}</h4>
                            <small class="text-muted">Terdaftar Aktif</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget shadow-sm">
                    <div class="body d-flex align-items-center">
                        <div class="icon bg-primary text-white rounded mr-3 shadow-sm"
                            style="width: 50px; height: 50px; line-height: 50px; text-align: center;">
                            <i class="fa fa-black-tie font-20"></i>
                        </div>
                        <div class="content">
                            <div class="text mb-0 text-uppercase font-weight-bold font-12 text-primary">Total Guru
                            </div>
                            <h4 class="number mb-0 font-weight-bold">{{ $stats['total_guru'] }}</h4>
                            <small class="text-muted">Tenaga Pendidik</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget shadow-sm">
                    <div class="body d-flex align-items-center">
                        <div class="icon bg-warning text-white rounded mr-3 shadow-sm"
                            style="width: 50px; height: 50px; line-height: 50px; text-align: center;">
                            <i class="fa fa-university font-20"></i>
                        </div>
                        <div class="content">
                            <div class="text mb-0 text-uppercase font-weight-bold font-12 text-warning">Total Rombel
                            </div>
                            <h4 class="number mb-0 font-weight-bold">{{ $stats['total_rombel'] }}</h4>
                            <small class="text-muted">Kelas Terdaftar</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="body text-center p-3">
                        <a href="{{ route('attendance.manual') }}" class="btn btn-primary btn-block btn-sm mb-2">
                            <i class="fa fa-check-square-o mr-1"></i> Kelola Presensi
                        </a>
                        <a href="{{ route('school-settings.index') }}" class="btn btn-outline-secondary btn-block btn-sm mt-0">
                            <i class="fa fa-cog mr-1"></i> Pengaturan
                        </a>
                    </div>
                </div>
            </div>

            {{-- ATTENDANCE SUMMARY --}}
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="header d-flex justify-content-between align-items-center">
                        <h2>Statistik Kehadiran Hari Ini</h2>
                        <span class="badge badge-default">Update: {{ date('H:i') }}</span>
                    </div>
                    <div class="body">
                        <div class="stats-grid">
                            <div class="py-2">
                                <label class="mb-0 text-muted">Hadir</label>
                                <h4 class="font-30 font-weight-bold text-success">{{ $stats['absensi_hari_ini']['hadir'] }}</h4>
                            </div>
                            <div class="py-2">
                                <label class="mb-0 text-muted">Terlambat</label>
                                <h4 class="font-30 font-weight-bold text-warning">{{ $stats['absensi_hari_ini']['terlambat'] }}
                                </h4>
                            </div>
                            <div class="py-2">
                                <label class="mb-0 text-muted">Izin/Sakit</label>
                                <h4 class="font-30 font-weight-bold text-info">{{ $stats['absensi_hari_ini']['izin_sakit'] }}
                                </h4>
                            </div>
                            <div class="py-2">
                                <label class="mb-0 text-muted">Alpha</label>
                                <h4 class="font-30 font-weight-bold text-danger">{{ $stats['absensi_hari_ini']['alpha'] }}</h4>
                            </div>
                        </div>
                        <hr>
                        <div class="table-responsive mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Ranking Kehadiran Kelas ({{ $period == 'week' ? 'Minggu Ini' : 'Bulan Ini' }})
                                </h6>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('dashboard.index', ['period' => 'week']) }}"
                                        class="btn {{ $period == 'week' ? 'btn-primary' : 'btn-outline-secondary' }}">Mingguan</a>
                                    <a href="{{ route('dashboard.index', ['period' => 'month']) }}"
                                        class="btn {{ $period == 'month' ? 'btn-primary' : 'btn-outline-secondary' }}">Bulanan</a>
                                </div>
                            </div>
                            <table class="table table-hover table-custom spacing5">
                                <thead>
                                    <tr>
                                        <th>Kelas</th>
                                        <th width="200">Persentase</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($top_classes as $tc)
                                        <tr>
                                            <td><strong>{{ $tc['nama'] }}</strong></td>
                                            <td>
                                                <div class="progress progress-xs">
                                                    <div class="progress-bar bg-{{ $tc['percentage'] > 90 ? 'success' : ($tc['percentage'] > 70 ? 'info' : 'warning') }}"
                                                        role="progressbar" style="width: {{ $tc['percentage'] }}%;"></div>
                                                </div>
                                                <small class="text-muted">{{ $tc['percentage'] }}% Efektivitas</small>
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('rombongan-belajar.show', $tc['id']) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Detail Kelas">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="header">
                        <h2>Menu Laporan</h2>
                    </div>
                    <div class="body">
                        <div class="list-group">
                            <a href="{{ route('laporan.absensi.kelas') }}"
                                class="list-group-item list-group-item-action d-flex align-items-center border-0 py-3">
                                <div class="icon-in-bg bg-info text-white rounded mr-3"
                                    style="width: 35px; height: 35px; line-height:35px; text-align:center;"><i
                                        class="fa fa-file-text-o"></i></div>
                                <span>Laporan Per Kelas</span>
                            </a>
                            <a href="{{ route('laporan.absensi.mapel') }}"
                                class="list-group-item list-group-item-action d-flex align-items-center border-0 py-3">
                                <div class="icon-in-bg bg-primary text-white rounded mr-3"
                                    style="width: 35px; height: 35px; line-height:35px; text-align:center;"><i
                                        class="fa fa-book"></i></div>
                                <span>Laporan Per Mapel</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(auth()->user()->role === 'guru')
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="header">
                        <h2>Jadwal Mengajar Hari Ini</h2>
                    </div>
                    <div class="body">
                        @if(!$is_school_day)
                            <div class="alert alert-info border-0 shadow-xs mb-0 py-4 text-center">
                                <i class="fa fa-calendar-times-o fa-3x mb-3 d-block opacity-50"></i>
                                <h5 class="mb-1 font-weight-bold">Hari Libur Sekolah</h5>
                                <p class="mb-0 text-muted">Tidak ada jadwal mengajar yang dijadwalkan untuk hari ini.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Mapel</th>
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
                                                <td>{{ substr($schedule->jam_mulai, 0, 5) }} -
                                                    {{ substr($schedule->jam_selesai, 0, 5) }}
                                                </td>
                                                <td><strong>{{ $schedule->subject->nama_mapel }}</strong></td>
                                                <td>{{ $schedule->rombonganBelajar->nama_rombel }}</td>
                                                <td>
                                                    <a href="javascript:void(0);" class="view-attendance-detail d-block"
                                                        data-id="{{ $schedule->id }}" title="Klik untuk lihat detail">
                                                        <div class="d-flex justify-content-between mb-1 text-dark">
                                                            <small
                                                                class="font-weight-bold">{{ $stats['hadir'] }}/{{ $stats['total'] }}</small>
                                                            <small class="text-muted">{{ $percent }}%</small>
                                                        </div>
                                                        <div class="progress progress-xxs mb-0">
                                                            <div class="progress-bar bg-info" role="progressbar"
                                                                style="width: {{ $percent }}%;"></div>
                                                        </div>
                                                    </a>
                                                </td>
                                                <td>
                                                    @if($isCurrent)
                                                        <span class="badge badge-success">Sedang Mengajar</span>
                                                    @elseif($now < $schedule->jam_mulai)
                                                        <span class="badge badge-warning">Akan Datang</span>
                                                    @else
                                                        <span class="badge badge-secondary">Selesai</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Tidak ada jadwal mengajar hari ini.</td>
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
                @if($walas_data)
                    <div class="card shadow-sm border-0">
                        <div class="header d-flex justify-content-between align-items-center">
                            <h2>Bimbingan Kelas</h2>
                            <span class="badge badge-info">{{ $walas_data['nama_rombel'] }}</span>
                        </div>
                        <div class="body pt-0">
                            <div class="row text-center mb-4">
                                <div class="col-6">
                                    <div class="icon-in-bg bg-success text-white rounded-circle mx-auto mb-2"
                                        style="width: 40px; height: 40px; line-height: 40px;">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <h5 class="mb-0 font-weight-bold">{{ $walas_data['hadir'] }}</h5>
                                    <small class="text-muted">Hadir</small>
                                </div>
                                <div class="col-6">
                                    <div class="icon-in-bg bg-warning text-white rounded-circle mx-auto mb-2"
                                        style="width: 40px; height: 40px; line-height: 40px;">
                                        <i class="fa fa-user-secret"></i>
                                    </div>
                                    <h5 class="mb-0 font-weight-bold text-warning">{{ $walas_data['belum_absen'] }}</h5>
                                    <small class="text-muted">Belum</small>
                                </div>
                            </div>

                            @if($walas_data['pending_izin'] > 0)
                                <div class="alert bg-warning text-light d-flex align-items-center mb-0 mt-2 py-2">
                                    <i class="fa fa-info-circle mr-2"></i>
                                    <small><strong>{{ $walas_data['pending_izin'] }}</strong> izin perlu proses</small>
                                    <a href="{{ route('attendance-permissions.index') }}"
                                        class="btn btn-xs btn-primary ml-auto">Proses</a>
                                </div>
                            @else
                                <div class="text-center py-2 bg-light rounded text-success h-100">
                                    <small><i class="fa fa-check-circle mr-1"></i> Izin sudah beres semua</small>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-white border-top-0 pt-0 text-center">
                            <a href="{{ route('rombongan-belajar.show', $walas_data['id']) }}"
                                class="btn btn-sm btn-outline-primary btn-block">Kelola Kelas</a>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="header">
                        <h2>Akses Cepat</h2>
                    </div>
                    <div class="body">
                        <div class="list-group">
                            <a href="{{ route('attendance-permissions.index') }}"
                                class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fa fa-envelope-o mr-3 text-warning"></i> Pengajuan Izin
                            </a>
                            <a href="{{ route('laporan.absensi.kelas') }}"
                                class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fa fa-file-text-o mr-3 text-info"></i> Laporan Kehadiran
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(auth()->user()->role === 'siswa')
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="header">
                        <h2>Jadwal Pelajaran Hari Ini</h2>
                    </div>
                    <div class="body">
                        @if(!$is_school_day)
                            <div class="alert alert-warning border-0 shadow-xs mb-0 py-4 text-center">
                                <i class="fa fa-coffee fa-3x mb-3 d-block opacity-50"></i>
                                <h5 class="mb-1 font-weight-bold">Hari Libur / Non-Sekolah</h5>
                                <p class="mb-0 text-muted">Nikmati waktu istirahatmu! Tidak ada jadwal pelajaran hari ini.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Mata Pelajaran</th>
                                            <th>Guru</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($schedules as $schedule)
                                            @php
                                                $now = \Carbon\Carbon::now()->toTimeString();
                                                $isCurrent = ($now >= $schedule->jam_mulai && $now <= $schedule->jam_selesai);
                                            @endphp
                                            <tr class="{{ $isCurrent ? 'bg-light-cyan' : '' }}">
                                                <td>
                                                    <span class="badge badge-info">{{ substr($schedule->jam_mulai, 0, 5) }} -
                                                        {{ substr($schedule->jam_selesai, 0, 5) }}</span>
                                                </td>
                                                <td><strong>{{ $schedule->subject->nama_mapel }}</strong></td>
                                                <td>{{ $schedule->teacher->user->name }}</td>
                                                <td>
                                                    @if($isCurrent)
                                                        <span class="badge badge-success">Sedang Berlangsung</span>
                                                    @elseif($now < $schedule->jam_mulai)
                                                        <span class="badge badge-warning">Akan Datang</span>
                                                    @else
                                                        <span class="badge badge-secondary">Selesai</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Tidak ada jadwal pelajaran untuk hari
                                                    ini.</td>
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
                <div class="card">
                    <div class="header">
                        <h2>Akses Cepat Absensi</h2>
                    </div>
                    <div class="body">
                        <div class="list-group">
                            <a href="{{ route('attendance.scanner', ['type' => 'masuk']) }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-sign-in mr-2 text-primary"></i> Absensi Masuk</span>
                                <i class="fa fa-chevron-right font-12 text-muted"></i>
                            </a>
                            <a href="{{ route('attendance.scanner', ['type' => 'mapel']) }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-book mr-2 text-info"></i> Absensi Mapel</span>
                                <i class="fa fa-chevron-right font-12 text-muted"></i>
                            </a>
                            <a href="{{ route('attendance.scanner', ['type' => 'pulang']) }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-sign-out mr-2 text-danger"></i> Absensi Pulang</span>
                                <i class="fa fa-chevron-right font-12 text-muted"></i>
                            </a>
                            <a href="{{ route('attendance-permissions.index') }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center mt-2">
                                <span><i class="fa fa-file-text-o mr-2 text-warning"></i> Pengajuan Izin</span>
                                <i class="fa fa-chevron-right font-12 text-muted"></i>
                            </a>
                        </div>
                        <div class="mt-4 text-center">
                            <div id="miniGpsStatus" class="p-2 rounded bg-light border">
                                <i class="fa fa-location-arrow mr-1"></i> Mendeteksi Lokasi...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @if(auth()->user()->role === 'guru')
        <!-- Modal Detail Presensi -->
        <div class="modal fade" id="attendanceDetailModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Detail Presensi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modalMeta" class="mb-3">
                            <p class="mb-0">Mapel: <strong id="modalSubject">-</strong></p>
                            <p class="mb-0">Kelas: <strong id="modalClass">-</strong></p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom spacing5 mb-0">
                                <thead>
                                    <tr>
                                        <th>Nama Siswa</th>
                                        <th width="120">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceList">
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('afterAppScripts')
    <script>
        $(function () {
            $('.view-attendance-detail').on('click', function () {
                var scheduleId = $(this).data('id');
                var $modal = $('#attendanceDetailModal');

                $('#attendanceList').html('<tr><td colspan="2" class="text-center"><i class="fa fa-spinner fa-spin mr-1"></i> Memuat data...</td></tr>');
                $modal.modal('show');

                $.get('/dashboard/attendance-detail/' + scheduleId, function (res) {
                    $('#modalSubject').text(res.subject);
                    $('#modalClass').text(res.class);

                    var html = '';
                    if (res.data.length > 0) {
                        res.data.forEach(function (item) {
                            html += `<tr>
                                                <td><strong>${item.name}</strong></td>
                                                <td>${item.pills}</td>
                                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="2" class="text-center">Tidak ada data siswa.</td></tr>';
                    }
                    $('#attendanceList').html(html);
                });
            });
        });
    </script>
    <script>
        // Simple GPS check for dashboard
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const gpsEl = document.getElementById('miniGpsStatus');
                if (gpsEl) {
                    gpsEl.innerHTML = '<i class="fa fa-check-circle text-success mr-1"></i> Lokasi Terdeteksi';
                    gpsEl.classList.add('text-success');
                }
            });
        }
    </script>
@endsection