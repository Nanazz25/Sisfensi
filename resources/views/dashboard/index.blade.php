@extends('layouts.app')

@section('title', 'Dashboard')

@section('afterAppStyles')
    {{-- Custom styles already loaded globally via layout --}}
    @if(auth()->user()->role === 'siswa')
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
    @endif
@endsection

@section('content')
    <div class="row clearfix row-deck">
        <div class="col-lg-12">
            <div class="card welcome-card bg-gradient-primary text-white border-0 shadow-sm mb-4">
                <div class="body d-flex align-items-center py-4 justify-content-between">
                    <div class="d-flex align-items-center">
                        @php
                            $hour = now()->format('H');
                            if (isset($holiday) && $holiday) {
                                $greeting = 'Selamat Berlibur';
                                $icon = 'mingcute:celebrate-line';
                                $sub = 'Hari ini adalah ' . $holiday->description . '. Nikmati waktu istirahatmu!';
                            } elseif ($hour >= 5 && $hour < 11) {
                                $greeting = 'Selamat Pagi';
                                $icon = 'mingcute:sun-fog-line';
                                $sub = 'Semangat pagi untuk memulai hari yang produktif!';
                            } elseif ($hour >= 11 && $hour < 15) {
                                $greeting = 'Selamat Siang';
                                $icon = 'mingcute:sun-line';
                                $sub = 'Tetap semangat pantau perkembangan siswa!';
                            } elseif ($hour >= 15 && $hour < 18) {
                                $greeting = 'Selamat Sore';
                                $icon = 'mingcute:sunset-line';
                                $sub = 'Tuntaskan tugas hari ini sebelum beristirahat!';
                            } else {
                                $greeting = 'Selamat Malam';
                                $icon = 'mingcute:moon-stars-line';
                                $sub = 'Terima kasih atas dedikasi luar biasamu hari ini!';
                            }
                        @endphp
                        <div class="icon-box bg-white-20 rounded-circle text-center mr-3"
                            style="width: 60px; height: 60px; line-height: 60px; display: flex; align-items: center; justify-content: center;">
                            <iconify-icon icon="{{ $icon }}" style="font-size: 32px;"></iconify-icon>
                        </div>
                        <div>
                            <h4 class="mb-1 font-weight-bold">{{ $greeting }}, {{ Auth::user()->name }}!</h4>
                            <p class="mb-0 opacity-75" style="font-size: 13px;">{{ $sub }}</p>
                            <p class="mb-0 opacity-50 small mt-1">
                                <i class="fa fa-calendar-o mr-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                            </p>
                        </div>
                    </div>
                    @php 
                        $pendingCount = 0;
                        if(auth()->user()->role === 'admin') {
                            $pendingCount = $stats['pending_permissions'] ?? 0;
                        } elseif(auth()->user()->role === 'guru') {
                            $pendingCount = $walas_data['pending_izin'] ?? 0;
                        }
                    @endphp

                    @if($pendingCount > 0)
                        <a href="{{ route('attendance-permissions.index') }}" class="btn btn-warning shadow-sm pulse py-2 px-3 rounded-pill font-weight-bold text-white">
                            <i class="fa fa-warning mr-1"></i> <span class="d-none d-md-inline">{{ $pendingCount }} Menunggu Verifikasi</span>
                            <span class="d-md-none">{{ $pendingCount }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
            @include('dashboard.partials.admin')
        @elseif(auth()->user()->role === 'guru')
            @include('dashboard.partials.guru')
        @elseif(auth()->user()->role === 'siswa')
            @include('dashboard.partials.siswa')
        @elseif(auth()->user()->role === 'helpdesk')
            @include('dashboard.partials.helpdesk')
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
                        html = '<tr><td colspan="2" class="text-center py-4 text-muted"><iconify-icon icon="mingcute:user-remove-line" class="d-block mb-2 mx-auto" style="font-size: 28px; opacity: 0.5;"></iconify-icon> Tidak ada data siswa.</td></tr>';
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

    @if(auth()->user()->role === 'siswa')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>
    
    @if(isset($assessment_score) && count($assessment_score) > 0)
        <!-- Chart JS for dashboard student status -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            window.radarChartData = {
                labels: {!! json_encode($assessment_score->pluck('name')) !!},
                values: {!! json_encode($assessment_score->pluck('score')) !!}
            };
        </script>
        @vite('resources/js/assessment/show.js')
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('student-attendance-calendar');
            if (calendarEl) {
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
                    events: '{{ route('attendance.calendar') }}',
                    eventClick: function(info) {
                        const title = info.event.title || '';
                        const id = info.event.id || '';
                        
                        if (id.includes('holiday') || title.toLowerCase() === 'libur') {
                            toastr.info("Keterangan: " + title);
                        } else {
                            toastr.info("Status Kehadiran: " + title);
                        }
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
            }
        });
    </script>
    @endif
@endsection