@extends('layouts.app')

@section('title', 'Presensi Biometrik')

@section('afterAppStyles')
    @vite('resources/css/attendance.scanner.css')
@endsection

@section('content')
    <div class="scanner-layout">
        <!-- Kolom Kiri: Kamera -->
        <div class="camera-section" id="cameraWrapper">
            <video id="video" autoplay muted></video>
            <canvas id="canvas"></canvas>

            <div class="permission-overlay" id="permissionOverlay">
                <div class="permission-card shadow-lg">
                    <div class="permission-icon mb-3">
                        <i class="fa fa-lock text-warning"></i>
                    </div>
                    <h4 class="font-weight-bold mb-2 text-dark">Akses Diperlukan</h4>
                    <p class="text-secondary small mb-4 px-3">
                        Aplikasi butuh izin <b>Kamera</b> & <b>Lokasi (GPS)</b> untuk proses presensi biometrik.
                    </p>
                    <button class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm"
                        onclick="requestPermissions(event)">
                        IZINKAN SEKARANG
                    </button>
                </div>
            </div>

            <div class="success-overlay" id="successOverlay">
                <i class="fa fa-check-circle"></i>
                <h1 id="successName" class="font-weight-bold">BERHASIL</h1>
                <h4 id="successTime" class="mb-4">Absensi Tercatat</h4>
                <div id="successExtra" class="w-100"></div>
                <div class="d-flex justify-content-center mt-2">
                    <button class="btn btn-secondary btn-lg px-5 shadow-sm mx-2" onclick="resetScanner()">
                        ABSEN LAGI
                    </button>
                    <button class="btn btn-outline-light btn-lg px-5 mx-2"
                        onclick="location.href='{{ route('dashboard.index') }}'">
                        DASHBOARD
                    </button>
                </div>
            </div>

            <div style="position: absolute; top: 20px; left: 20px; z-index: 10;">
                <div class="status-badge-inline shadow">
                    <div id="statusDot" class="dot"></div>
                    <span id="statusText">System Ready</span>
                </div>
            </div>

            {{-- Mode Indicator Badge (Top Right) --}}
            <div class="mode-indicator-badge {{ $type }}" id="modeIndicator">
                @if($type == 'masuk')
                    📍 Masuk
                @elseif($type == 'mapel')
                    📚 Mapel
                @else
                    🏠 Pulang
                @endif
            </div>

        </div>

        {{-- Floating Schedule Button (Mobile Only) --}}
        @if($type == 'mapel')
             <button class="floating-schedule-btn d-lg-none" id="floatingScheduleBtn" onclick="toggleScheduleModal()" type="button">
                 <i class="fa fa-calendar-o mr-1"></i> Jadwal
             </button>
        @endif

        <!-- Kolom Kanan: Kontrol & Info -->
        <div class="info-section">
            <div class="card-info shadow-sm">
                <h6 class="mb-3 text-uppercase font-weight-bold text-primary"
                    style="font-size: 0.75rem; letter-spacing: 1px;">
                    <i class="fa fa-map-marker mr-2"></i>Status Keamanan
                </h6>
                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <span class="text-muted">ID Perangkat:</span>
                    <b class="text-dark">FACE-SCAN-{{ substr(md5(auth()->id()), 0, 4) }}</b>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted">GPS Signal:</span>
                    <span id="gpsStatus"><i class="fa fa-circle-o-notch fa-spin text-warning"></i></span>
                </div>
            <div id="coordsDebug" class="text-right small font-weight-bold text-secondary">Mencari Koordinat...</div>
            </div>

            {{-- Info Daftar Mapel Hari Ini (Jika Mode Mapel) - HIDDEN ON MOBILE (Show on Desktop) --}}
            @if($type == 'mapel')
                <div class="card-info shadow-sm bg-white border-0 d-none d-lg-block">
                    <h6 class="mb-3 text-uppercase font-weight-bold text-primary" style="font-size: 0.75rem; letter-spacing: 1px;">
                        <i class="fa fa-calendar mr-2"></i>Jadwal Hari Ini
                    </h6>
                    
                    <div class="schedule-mini-list" style="max-height: 350px; overflow-y: auto;">
                        @forelse($schedules as $sch)
                            @php
                                $now = \Carbon\Carbon::now()->toTimeString();
                                $isCurrent = ($now >= $sch->jam_mulai && $now <= $sch->jam_selesai);
                                $hasAttended = in_array($sch->id, $mapelAttended);
                            @endphp
                            
                            <div class="schedule-item mb-2 p-2 rounded {{ $isCurrent ? 'bg-light-cyan border-left-cyan' : 'bg-light' }}" 
                                 style="{{ $isCurrent ? 'border-left: 4px solid #00cfd5;' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div style="flex: 1; min-width: 0;">
                                        <div class="font-weight-bold text-dark text-truncate" style="font-size: 0.85rem;">
                                            {{ $sch->subject->nama_mapel }}
                                        </div>
                                        <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">
                                            {{ $sch->teacher->user->name }}
                                        </small>
                                    </div>
                                    <div class="text-right ml-2">
                                        <div class="badge {{ $isCurrent ? 'badge-info' : 'badge-light border' }} mb-1" style="font-size: 0.65rem;">
                                            {{ substr($sch->jam_mulai, 0, 5) }}
                                        </div>
                                        @if($hasAttended)
                                            <div class="text-success small"><i class="fa fa-check-circle"></i></div>
                                        @elseif($isCurrent)
                                            <div class="pulse-cyan-dot"></div>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="small" style="font-size: 0.65rem; opacity: 0.7;">
                                        {{ substr($sch->jam_mulai, 0, 5) }} - {{ substr($sch->jam_selesai, 0, 5) }}
                                    </span>
                                    @if($hasAttended)
                                        <span class="badge badge-success" style="font-size: 0.6rem;">SUDAH ABSEN</span>
                                    @elseif($isCurrent)
                                        <span class="badge badge-info pulse-cyan" style="font-size: 0.6rem;">LAGI JALAN</span>
                                    @else
                                        <span class="badge badge-secondary" style="font-size: 0.6rem;">BELUM</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="fa fa-calendar-times-o fa-2x text-muted mb-2 opacity-50"></i>
                                <p class="mb-0 small text-muted font-weight-bold">Tidak ada jadwal hari ini</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <style>
                    .bg-light-cyan { background-color: rgba(0, 207, 213, 0.08) !important; }
                    .border-left-cyan { border-left: 4px solid #00cfd5 !important; }
                    .pulse-cyan-dot {
                        width: 8px;
                        height: 8px;
                        background: #00cfd5;
                        border-radius: 50%;
                        display: inline-block;
                        box-shadow: 0 0 0 rgba(0, 207, 213, 0.4);
                        animation: pulse-dot 2s infinite;
                    }
                    @keyframes pulse-dot {
                        0% { box-shadow: 0 0 0 0 rgba(0, 207, 213, 0.7); }
                        70% { box-shadow: 0 0 0 10px rgba(0, 207, 213, 0); }
                        100% { box-shadow: 0 0 0 0 rgba(0, 207, 213, 0); }
                    }
                </style>
            @endif

            <div class="card-info shadow-sm">
                <h6 class="mb-3 text-uppercase font-weight-bold text-primary"
                    style="font-size: 0.75rem; letter-spacing: 1px;">
                    <i class="fa fa-list-ul mr-2"></i>Kategori Absensi
                </h6>
                <div class="type-selector">
                    <button class="type-btn {{ $type == 'masuk' ? 'active' : '' }}" onclick="checkAttendanceFlow('masuk')">
                        <i class="fa fa-sign-in"></i> <span>Presensi Masuk</span>
                    </button>
                    <button class="type-btn {{ $type == 'mapel' ? 'active' : '' }}" onclick="checkAttendanceFlow('mapel')">
                        <i class="fa fa-book"></i> <span>Presensi Mapel</span>
                    </button>
                    <button class="type-btn {{ $type == 'pulang' ? 'active' : '' }}" onclick="checkAttendanceFlow('pulang')">
                        <i class="fa fa-sign-out"></i> <span>Presensi Pulang</span>
                    </button>
                </div>
            </div>

            {{-- Type Selector Toggle (Mobile Only) --}}
            <button class="type-selector-toggle" id="typeSelectorToggle" onclick="toggleTypeSelector()">
                <span>
                    <i class="fa 
                                            @if($type == 'masuk') fa-sign-in
                                            @elseif($type == 'mapel') fa-book
                                            @else fa-sign-out
                                            @endif
                                        "></i>
                    @if($type == 'masuk') Presensi Masuk
                    @elseif($type == 'mapel') Presensi Mapel
                    @else Presensi Pulang
                    @endif
                </span>
                <i class="fa fa-chevron-down"></i>
            </button>

            {{-- Type Selector Dropdown (Mobile Only) --}}
            <div class="type-selector-dropdown" id="typeSelectorDropdown">
                <div class="type-selector-dropdown-item {{ $type == 'masuk' ? 'active' : '' }}"
                    onclick="checkAttendanceFlow('masuk')">
                    <i class="fa fa-sign-in"></i>
                    <span>Presensi Masuk</span>
                </div>
                <div class="type-selector-dropdown-item {{ $type == 'mapel' ? 'active' : '' }}"
                    onclick="checkAttendanceFlow('mapel')">
                    <i class="fa fa-book"></i>
                    <span>Presensi Mapel</span>
                </div>
                <div class="type-selector-dropdown-item {{ $type == 'pulang' ? 'active' : '' }}"
                    onclick="checkAttendanceFlow('pulang')">
                    <i class="fa fa-sign-out"></i>
                    <span>Presensi Pulang</span>
                </div>
            </div>

            <script>
                function checkAttendanceFlow(targetType) {
                    const hasMasuk = @json($hasMasukToday);
                    
                    if (targetType !== 'masuk' && !hasMasuk) {
                        toastr.error('Harap melakukan Presensi Masuk terlebih dahulu!');
                        return;
                    }

                    location.href = "{{ route('attendance.scanner') }}?type=" + targetType;
                }
            </script>

            <div class="mt-auto">
                <button id="btnAbsen" class="btn btn-primary btn-absen-large shadow-lg pulse-button" disabled>
                    <i class="fa fa-camera mr-2"></i>
                    @if($type == 'masuk') KONFIRMASI HADIR
                    @elseif($type == 'mapel') KONFIRMASI MAPEL
                    @else KONFIRMASI PULANG
                    @endif
                </button>
                <div class="text-center mt-3">
                    <small class="text-muted font-italic">Posisikan wajah di tengah area kamera</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Schedule Modal (Mobile Only) --}}
    @if($type == 'mapel')
        <div class="schedule-modal-overlay" id="scheduleModal">
            <div class="schedule-modal-card">
                <div class="schedule-modal-header">
                    <h5 class="mb-0 font-weight-bold"><i class="fa fa-calendar mr-2"></i>Jadwal Hari Ini</h5>
                    <button class="close-modal-btn" onclick="toggleScheduleModal()">&times;</button>
                </div>
                <div class="schedule-modal-body">
                    <div class="schedule-mini-list p-3">
                        @forelse($schedules as $sch)
                            @php
                                $now = \Carbon\Carbon::now()->toTimeString();
                                $isCurrent = ($now >= $sch->jam_mulai && $now <= $sch->jam_selesai);
                                $hasAttended = in_array($sch->id, $mapelAttended);
                            @endphp
                            
                            <div class="schedule-item mb-3 p-3 rounded {{ $isCurrent ? 'bg-light-cyan border-left-cyan' : 'bg-light' }}" 
                                 style="{{ $isCurrent ? 'border-left: 4px solid #00cfd5;' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div style="flex: 1; min-width: 0;">
                                        <div class="font-weight-bold text-dark h6 mb-1" style="font-size: 0.95rem;">
                                            {{ $sch->subject->nama_mapel }}
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fa fa-user-circle mr-1"></i> {{ $sch->teacher->user->name }}
                                        </div>
                                    </div>
                                    <div class="text-right ml-2">
                                        <div class="badge {{ $isCurrent ? 'badge-info' : 'badge-light border' }} mb-2">
                                            {{ substr($sch->jam_mulai, 0, 5) }}
                                        </div>
                                        @if($hasAttended)
                                            <div class="text-success"><i class="fa fa-check-circle fa-lg"></i></div>
                                        @elseif($isCurrent)
                                            <div class="pulse-cyan-dot"></div>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                    <span class="small font-weight-bold" style="opacity: 0.8;">
                                        <i class="fa fa-clock-o mr-1"></i> {{ substr($sch->jam_mulai, 0, 5) }} - {{ substr($sch->jam_selesai, 0, 5) }}
                                    </span>
                                    @if($hasAttended)
                                        <span class="badge badge-success px-2 py-1">SUDAH ABSEN</span>
                                    @elseif($isCurrent)
                                        <span class="badge badge-info pulse-cyan px-2 py-1">SEDANG BERLANGSUNG</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1">BELUM</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fa fa-calendar-times-o fa-3x text-muted mb-3 opacity-30"></i>
                                <p class="mb-0 text-muted font-weight-bold">Tidak ada jadwal hari ini</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('afterAppScripts')
    <script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <script>
        window.attendanceConfig = {
            verifyUrl: "{{ route('attendance.verify') }}",
            csrfToken: "{{ csrf_token() }}",
            dashboardUrl: "{{ route('dashboard.index') }}",
            type: "{{ $type }}"
        };

        // Type Selector Toggle (Bottom Panel)
        function toggleTypeSelector() {
            const dropdown = document.getElementById('typeSelectorDropdown');
            const toggle = document.getElementById('typeSelectorToggle');
            dropdown.classList.toggle('active');
            toggle.classList.toggle('active');
        }

        // Schedule Modal Toggle (Global)
        window.toggleScheduleModal = function() {
            const modal = document.getElementById('scheduleModal');
            console.log("Modal toggled!");
            if (modal) {
                modal.classList.toggle('show');
                document.body.classList.toggle('modal-open');
            }
        }

        // Close modal when clicking on overlay
        const scheduleModal = document.getElementById('scheduleModal');
        if (scheduleModal) {
            scheduleModal.addEventListener('click', function(event) {
                if (event.target === scheduleModal) {
                    toggleScheduleModal();
                }
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            const toggle = document.getElementById('typeSelectorToggle');
            const dropdown = document.getElementById('typeSelectorDropdown');

            if (toggle && dropdown && !toggle.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
                toggle.classList.remove('active');
            }
        });

        // Set type from bottom dropdown
        function setTypeFromDropdown(type) {
            window.location.href = `{{ route('attendance.scanner') }}?type=${type}`;
        }
    </script>

    @vite('resources/js/biometric/attendance.scanner.js')
@endsection