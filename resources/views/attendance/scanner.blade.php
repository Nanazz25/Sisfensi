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

            <div class="card-info shadow-sm">
                <h6 class="mb-3 text-uppercase font-weight-bold text-primary"
                    style="font-size: 0.75rem; letter-spacing: 1px;">
                    <i class="fa fa-list-ul mr-2"></i>Kategori Absensi
                </h6>
                <div class="type-selector">
                    <button class="type-btn {{ $type == 'masuk' ? 'active' : '' }}" onclick="setType('masuk', this)">
                        <i class="fa fa-sign-in"></i> <span>Presensi Masuk</span>
                    </button>
                    <button class="type-btn {{ $type == 'mapel' ? 'active' : '' }}" onclick="setType('mapel', this)">
                        <i class="fa fa-book"></i> <span>Presensi Mapel</span>
                    </button>
                    <button class="type-btn {{ $type == 'pulang' ? 'active' : '' }}" onclick="setType('pulang', this)">
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
                    onclick="setTypeFromDropdown('masuk')">
                    <i class="fa fa-sign-in"></i>
                    <span>Presensi Masuk</span>
                </div>
                <div class="type-selector-dropdown-item {{ $type == 'mapel' ? 'active' : '' }}"
                    onclick="setTypeFromDropdown('mapel')">
                    <i class="fa fa-book"></i>
                    <span>Presensi Mapel</span>
                </div>
                <div class="type-selector-dropdown-item {{ $type == 'pulang' ? 'active' : '' }}"
                    onclick="setTypeFromDropdown('pulang')">
                    <i class="fa fa-sign-out"></i>
                    <span>Presensi Pulang</span>
                </div>
            </div>

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