{{-- STUDENT ATTENDANCE STATUS --}}
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-success text-white text-center mr-3">
                <i class="fa fa-sign-in"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Masuk</small>
                <h6 class="mb-0 font-weight-bold text-dark">
                    {{ $attendance_summary['masuk'] ? $attendance_summary['masuk']->waktu_absen->format('H:i') : 'Belum' }}
                </h6>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-danger text-white text-center mr-3">
                <i class="fa fa-sign-out"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Pulang</small>
                <h6 class="mb-0 font-weight-bold text-dark">
                    {{ $attendance_summary['pulang'] ? $attendance_summary['pulang']->waktu_absen->format('H:i') : 'Belum' }}
                </h6>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-warning text-white text-center mr-3">
                <i class="fa fa-file-text-o"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Izin Pending</small>
                <h6 class="mb-0 font-weight-bold text-dark">{{ $attendance_summary['permissions'] }} Pengajuan</h6>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-info text-white text-center mr-3 shadow-sm">
                <i class="fa fa-star"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Rata-rata Karakter</small>
                <h6 class="mb-0 font-weight-bold text-dark">
                    @php
                        $avgChar = $assessment_score ? round($assessment_score->avg('score'), 1) : 0;
                    @endphp
                    {{ $avgChar > 0 ? $avgChar . '/10' : 'Belum Ada' }}
                </h6>
            </div>
        </div>
    </div>
</div>

    <div class="col-lg-8 col-md-12">
        <div class="card shadow-sm border-0">
            <div class="header">
                <h2 class="font-weight-bold">Status Karakter Anda</h2>
            </div>
            <div class="body text-center">
                @if($assessment_score && count($assessment_score) > 0)
                    <div style="max-width: 500px; margin: 0 auto;">
                        <canvas id="radarChart"></canvas>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="{{ route('assessment.show', auth()->id()) }}" class="btn btn-sm btn-outline-primary rounded-pill px-4">
                            <i class="fa fa-line-chart mr-1"></i> Detail Riwayat Karakter
                        </a>
                    </div>
                @else
                    <div class="py-4 text-center">
                        <div class="icon-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 65px; height: 65px; border-radius: 50%;">
                            <i class="fa fa-star-o fa-2x text-muted opacity-50"></i>
                        </div>
                        <h6 class="font-weight-bold text-dark mb-1">Murni & Bersih</h6>
                        <p class="text-muted mb-0" style="font-size: 13px;">Belum ada data penilaian karakter untuk Anda saat ini.</p>
                    </div>
                @endif
            </div>
        </div>

    <div class="card shadow-sm border-0">
        <div class="header">
            <h2 class="font-weight-bold">Jadwal Pelajaran Hari Ini</h2>
        </div>
        <div class="body">
            @if(!$is_school_day)
                <div class="bg-light border-0 mb-0 py-4 text-center" style="border-radius: 12px;">
                    <div class="icon-circle bg-white shadow-sm d-inline-flex align-items-center justify-content-center mb-3" style="width: 65px; height: 65px; border-radius: 50%;">
                        <i class="fa fa-coffee fa-2x text-warning opacity-75"></i>
                    </div>
                    <h6 class="mb-1 font-weight-bold text-dark">Hari Libur / Non-Sekolah</h6>
                    <p class="mb-0 text-muted" style="font-size: 13px;">Nikmati waktu istirahatmu! Tidak ada jadwal pelajaran hari ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-custom spacing5 mb-0">
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
                                        @if(in_array($schedule->id, $attendance_summary['mapel_attended']))
                                            <span class="badge badge-success"><i class="fa fa-check mr-1"></i> Sudah Absen</span>
                                        @elseif($isCurrent)
                                            <span class="badge badge-soft-success pulse-cyan" style="border-radius: 12px; padding: 5px 12px;"><i class="fa fa-clock-o mr-1"></i> Sedang Berlangsung</span>
                                        @elseif($now < $schedule->jam_mulai)
                                            <span class="badge badge-soft-warning">Akan Datang</span>
                                        @else
                                            <span class="badge badge-soft-secondary text-muted">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada jadwal pelajaran untuk hari ini.
                                    </td>
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
                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $attendance_summary['masuk'] ? 'bg-light text-muted' : '' }}"
                    style="{{ $attendance_summary['masuk'] ? 'pointer-events: none;' : '' }}">
                    <span><i
                            class="fa fa-sign-in mr-2 {{ $attendance_summary['masuk'] ? 'text-muted' : 'text-primary' }}"></i>
                        Absensi Masuk</span>
                    @if($attendance_summary['masuk'])
                        <span class="badge badge-success">Selesai</span>
                    @else
                        <i class="fa fa-chevron-right font-12 text-muted"></i>
                    @endif
                </a>
                <a href="{{ route('attendance.scanner', ['type' => 'mapel']) }}"
                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="fa fa-book mr-2 text-info"></i> Absensi Mapel</span>
                    <i class="fa fa-chevron-right font-12 text-muted"></i>
                </a>
                <a href="{{ route('attendance.scanner', ['type' => 'pulang']) }}"
                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $attendance_summary['pulang'] ? 'bg-light text-muted' : '' }}"
                    style="{{ $attendance_summary['pulang'] ? 'pointer-events: none;' : '' }}">
                    <span><i
                            class="fa fa-sign-out mr-2 {{ $attendance_summary['pulang'] ? 'text-muted' : 'text-danger' }}"></i>
                        Absensi Pulang</span>
                    @if($attendance_summary['pulang'])
                        <span class="badge badge-success">Selesai</span>
                    @else
                        <i class="fa fa-chevron-right font-12 text-muted"></i>
                    @endif
                </a>

                @php
                    $alreadyAttended = $attendance_summary['masuk'] ? true : false;
                    $hasPending = $attendance_summary['permissions'] > 0;
                @endphp

                <a href="{{ route('attendance-permissions.create') }}"
                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center mt-2 {{ ($alreadyAttended || $hasPending) ? 'bg-light text-muted' : '' }}"
                    style="{{ ($alreadyAttended || $hasPending) ? 'pointer-events: none;' : '' }}">
                    <span><i class="fa fa-file-text-o mr-2 {{ ($alreadyAttended || $hasPending) ? 'text-muted' : 'text-warning' }}"></i> Pengajuan Izin / Manual</span>
                    @if($alreadyAttended)
                        <span class="badge badge-soft-secondary">Sudah Absen</span>
                    @elseif($hasPending)
                        <span class="badge badge-warning">Pending</span>
                    @else
                        <i class="fa fa-chevron-right font-12 text-muted"></i>
                    @endif
                </a>
            </div>

            @if($alreadyAttended)
                <div class="alert alert-info border-0 mt-3 mb-0 py-2 small">
                    <i class="fa fa-info-circle mr-1"></i> Anda sudah melakukan absensi hari ini.
                </div>
            @elseif($hasPending)
                <div class="alert alert-warning border-0 mt-3 mb-0 py-2 small">
                    <i class="fa fa-clock-o mr-1"></i> Anda memiliki pengajuan izin yang sedang diproses.
                </div>
            @endif

            <div class="mt-4 text-center">
                <div id="miniGpsStatus" class="p-2 rounded bg-light border">
                    <i class="fa fa-location-arrow mr-1"></i> Mendeteksi Lokasi...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KALENDER SIKLUS KEHADIRAN SISWA -->
<div class="col-lg-12 col-md-12">
    <div class="card shadow-sm border-0">
        <div class="header">
            <h2 class="font-weight-bold">Kalender Kehadiran & Streak Absensi</h2>
        </div>
        <div class="body">
            <div id="student-attendance-calendar"></div>
        </div>
    </div>
</div>