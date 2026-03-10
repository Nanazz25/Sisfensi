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
            <div class="status-icon bg-info text-white text-center mr-3">
                <i class="fa fa-heartbeat"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Kesehatan</small>
                <h6 class="mb-0 font-weight-bold text-dark">Normal</h6>
            </div>
        </div>
    </div>
</div>

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
                                        @if($isCurrent)
                                            <span class="badge badge-soft-success">Berlangsung</span>
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
                    <span><i
                            class="fa fa-file-text-o mr-2 {{ ($alreadyAttended || $hasPending) ? 'text-muted' : 'text-warning' }}"></i>
                        Pengajuan Izin / Manual</span>
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