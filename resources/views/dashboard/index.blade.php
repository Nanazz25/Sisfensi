@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="row clearfix row-deck">
        <div class="col-lg-12">
            <div class="card">
                <div class="body">
                    <h5 class="mb-0">Selamat Datang, <strong>{{ Auth::user()->name }}</strong>!</h5>
                    <p class="text-muted">Hari ini adalah {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'siswa')
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="header">
                        <h2>Jadwal Pelajaran Hari Ini</h2>
                    </div>
                    <div class="body">
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
                                            <td>{{ $schedule->teacher->nama_lengkap }}</td>
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
@endsection

@section('afterAppScripts')
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