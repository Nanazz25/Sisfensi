@extends('layouts.app')
@section('title', 'Detail Peserta Didik')

@section('content')

    <div class="row">
        <!-- FOTO -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="body text-center">

                    @if($pesertaDidik->foto_wajah)
                        <img src="{{ route('peserta-didik.photo', $pesertaDidik->id) }}"
                            onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($pesertaDidik->nama_lengkap ?? $pesertaDidik->user->name) }}&background=00bcd4&color=fff&bold=true&size=512';"
                            class="shadow mb-3"
                            style="max-width: 200px; width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 12px; display: block; margin: 0 auto;">

                    @else
                        <img src="{{ optional($pesertaDidik->user)->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($pesertaDidik->nama_lengkap) . '&background=00bcd4&color=fff&bold=true&size=512' }}"
                            class="shadow mb-3"
                            style="max-width: 200px; width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 12px; display: block; margin: 0 auto;">
                    @endif

                    <span class="badge badge-info mb-2 mt-2">
                        {{ $pesertaDidik->user->name }}
                    </span>

                </div>
            </div>

            <!-- PENILAIAN KARAKTER -->
            <div class="card">
                <div class="header">
                    <h2>Penilaian Karakter Siswa</h2>
                </div>
                <div class="body text-center">
                @if(isset($assessment_score) && count($assessment_score) > 0)
                    <div style="max-width: 500px; margin: 0 auto;">
                        <canvas id="radarChart"></canvas>
                    </div>
                    <div class="mt-2 text-center">
                        <a href="{{ route('assessment.show', $pesertaDidik->user_id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 btn-block">
                            <i class="fa fa-line-chart mr-1"></i> Detail Riwayat Karakter
                        </a>
                    </div>
                @else
                    <div class="py-4 text-center">
                        <i class="fa fa-star-o fa-3x text-muted mb-2 opacity-50"></i>
                        <p class="text-muted mb-0">Belum ada data penilaian.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- DETAIL -->
    <div class="col-md-8 mb-4">
            <div class="card">
                <div class="header">
                    <h2>Informasi Peserta Didik</h2>
                </div>

                <div class="body">
                    <div class="table-responsive">
                        <table class="table table-striped" style="table-layout: fixed; width: 100%;">
                        <tr>
                            <th style="width: 35%; min-width: 120px;">Nama Lengkap</th>
                            <td style="white-space: normal; word-wrap: break-word; word-break: break-all;">{{ $pesertaDidik->user->name }}</td>
                        </tr>
                        <tr>
                            <th>No Induk</th>
                            <td style="white-space: normal; word-wrap: break-word; word-break: break-all;">{{ $pesertaDidik->no_induk }}</td>
                        </tr>
                        <tr>
                            <th>NISN</th>
                            <td style="white-space: normal; word-wrap: break-word; word-break: break-all;">{{ $pesertaDidik->nisn }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td style="white-space: normal; word-wrap: break-word; word-break: break-all;">{{ $pesertaDidik->user->email }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Kelamin</th>
                            <td>{{ $pesertaDidik->jenis_kelamin }}</td>
                        </tr>
                        <tr>
                            <th>Tempat Lahir</th>
                            <td style="white-space: normal; word-wrap: break-word; word-break: break-all;">{{ $pesertaDidik->tempat_lahir }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Lahir</th>
                            <td>{{ \Carbon\Carbon::parse($pesertaDidik->tanggal_lahir)->translatedFormat('j F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Face Embedding</th>
                            <td>
                                @if($pesertaDidik->face_embedding)
                                    <span class="badge badge-success">
                                        Tersimpan
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        Belum ada
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td>{{ $pesertaDidik->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                    </div>

                    <div class="mt-3">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>

                        <a href="{{ route('peserta-didik.edit', $pesertaDidik->id) }}" class="btn btn-warning">
                            <i class="fa fa-edit"></i> Edit
                        </a>

                        @if(!$pesertaDidik->face_embedding && (auth()->user()->role === 'admin' || auth()->id() === $pesertaDidik->user_id))
                            <a href="{{ route('face.enroll') }}" class="btn btn-primary">
                                <i class="fa fa-camera"></i> Registrasi Wajah
                            </a>
                        @endif
                    </div>

                </div>
            </div>
            

        </div>
    </div>

    <!-- KALENDER ABSENSI FULL WIDTH -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="header">
                    <h2>Kalender Kehadiran & Streak Absensi</h2>
                </div>
                <div class="body">
                    <div id="attendance-calendar"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('afterAppStyles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
<style>
    .fc { font-family: 'Nunito', sans-serif; }
    .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700 !important; color: #333; }
    .fc-button-primary { background-color: #00bcd4 !important; border-color: #00bcd4 !important; border-radius: 8px !important; padding: 0.4rem 1rem !important; font-weight: bold !important; text-transform: capitalize !important; box-shadow: 0 2px 4px rgba(0, 188, 212, 0.2); }
    .fc-button-primary:hover { background-color: #00a0b5 !important; border-color: #00a0b5 !important; }
    .fc-daygrid-day-number { font-weight: bold; color: #555; padding: 8px !important; text-decoration: none; }
    .fc-theme-standard td, .fc-theme-standard th { border-color: #eef2f5 !important; }
    .fc-col-header-cell-cushion { padding: 10px !important; font-weight: 700; color: #666; text-transform: uppercase; font-size: 0.85rem; text-decoration: none; }
    .fc-day-today { background-color: rgba(0, 188, 212, 0.05) !important; }
    .fc-event { cursor: pointer; border-radius: 6px !important; padding: 2px 5px !important; font-weight: 600 !important; border: none !important; margin-bottom: 2px !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .fc-daygrid-event-harness { margin: 0 4px !important; }
    
    /* Ensure event titles never stretch the calendar width */
    .fc-event-title, .fc-event-main {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        display: block !important;
    }

    /* Responsive Styling for Mobile */
    @media (max-width: 768px) {
        .fc-header-toolbar { flex-direction: column !important; gap: 10px; }
        .fc-toolbar-title { font-size: 1.1rem !important; }
        .fc-button-primary { padding: 0.3rem 0.6rem !important; font-size: 0.85rem !important; }
        .fc-col-header-cell-cushion { padding: 4px 0 !important; font-size: 0.65rem !important; text-align: center; }
        .fc-daygrid-day-number { padding: 4px !important; font-size: 0.8rem !important; }
        .fc-event { font-size: 0.55rem !important; padding: 1px 2px !important; text-align: center; }
        .fc .fc-toolbar.fc-header-toolbar { margin-bottom: 0.5em !important; }
        .fc-daygrid-event-harness { margin: 0 1px !important; }
    }
</style>
@endsection

@section('afterAppScripts')
@if(isset($assessment_score) && count($assessment_score) > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.radarChartData = {
            labels: {!! json_encode($assessment_score->pluck('name')) !!},
            values: {!! json_encode($assessment_score->pluck('score')) !!}
        };
    </script>
    @vite('resources/js/assessment/show.js')
@endif
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('attendance-calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'title',
                right: 'prev,next today'
            },
            height: 'auto',
            contentHeight: 'auto',
            handleWindowResize: true,
            events: '{{ route('attendance.calendar', $pesertaDidik->id) }}',
            eventClick: function(info) {
                // simple tooltip or toast when clicking event
                toastr.info("Status: " + info.event.title);
            }
        });
        calendar.render();

        // Force recalculation of calendar size after window resize/DevTools open
        // Resolves the issue where calendar gets squished on instant resize before refresh
        window.addEventListener('resize', function() {
            setTimeout(function() {
                calendar.updateSize();
            }, 250);
        });
    });
</script>
@endsection