@extends('layouts.app')

@section('title', 'Pendaftaran Biometrik')

@section('afterAppStyles')
    @vite('resources/css/biometric.css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <style>
        .select2-container--default .select2-selection--single {
            height: 45px;
            padding: 8px;
            border: 1px solid #ced4da;
        }
    </style>
@endsection

@section('content')
    <div class="scanner-layout">
        <!-- Kolom Kiri: Kamera -->
        <div class="camera-section" id="cameraWrapper">
            <video id="video" autoplay muted></video>
            <canvas id="canvas"></canvas>

            <!-- Permission Overlay (Same as Scanner) -->
            <div class="permission-overlay" id="permissionOverlay">
                <div class="permission-card shadow-lg">
                    <div class="permission-icon mb-3">
                        <i class="fa fa-lock text-warning"></i>
                    </div>
                    <h4 class="font-weight-bold mb-2 text-dark">Akses Diperlukan</h4>
                    <p class="text-secondary small mb-4 px-3">
                        Aplikasi butuh izin <b>Kamera</b> untuk proses registrasi biometrik wajah.
                    </p>
                    <button class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm"
                        onclick="requestPermissions(event)">
                        IZINKAN SEKARANG
                    </button>
                </div>
            </div>

            <!-- Success Overlay -->
            <div class="success-overlay" id="successOverlay">
                <i class="fa fa-check-circle"></i>
                <h1 id="successName" class="font-weight-bold">TERDAFTAR</h1>
                <h4 id="successSub" class="mb-4">Wajah Berhasil Disimpan</h4>
                <div class="d-flex justify-content-center mt-2">
                    <button class="btn btn-secondary btn-lg px-5 shadow-sm mx-2" onclick="resetEnroll()">
                        DAFTAR LAGI
                    </button>
                    <button class="btn btn-outline-light btn-lg px-5 mx-2"
                        onclick="location.href='{{ route('dashboard.index') }}'">
                        DASHBOARD
                    </button>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="status-badge-inline shadow">
                <div id="statusDot" class="dot"></div>
                <span id="statusText">System Ready</span>
            </div>

            <!-- Accuracy Badge (Top Right Mobile) -->
            <div class="accuracy-indicator-badge" id="accuracyIndicator">
                <i class="fa fa-crosshairs mr-1"></i> <span id="accuracyVal">0%</span>
            </div>
        </div>

        <!-- Kolom Kanan: Kontrol & Info -->
        <div class="info-section">
            {{-- Mobile Only: Student Selector Toggle --}}
            <div class="mobile-student-panel">
                <button class="student-toggle-btn" onclick="toggleMobileSelector(event)">
                    <span><i class="fa fa-user mr-2"></i> <span id="mobileStudentName">Cari & Pilih Siswa</span></span>
                    <i class="fa fa-chevron-right small"></i>
                </button>
            </div>

            {{-- Desktop Only: Student Selector Card --}}
            <div class="status-card shadow-sm mb-3 desktop-only" id="desktopStudentCard">
                <h6 class="mb-3 text-uppercase font-weight-bold text-primary"
                    style="font-size: 0.75rem; letter-spacing: 1px;">
                    <i class="fa fa-user-circle mr-2"></i>Pilih Target Siswa
                </h6>
                <div class="form-group mb-0">
                    <select id="studentSelect_desktop" class="form-control select2">
                        <option value="">-- Cari Nama Siswa --</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}"
                                data-registered="{{ $student->face_embedding ? 'true' : 'false' }}">
                                {{ strtoupper($student->user->name) }} ({{ $student->nisn }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="status-card shadow-sm mb-3">
                <h6 class="mb-4 text-uppercase font-weight-bold text-primary"
                    style="font-size: 0.75rem; letter-spacing: 1px;">
                    <i class="fa fa-info-circle mr-2"></i>Panduan Registrasi
                </h6>

                <div class="step-item">
                    <div class="step-number">1</div>
                    <div>
                        <h6 class="mb-1 font-weight-bold" style="font-size: 0.85rem;">Cahaya Cukup</h6>
                        <small class="text-muted">Pastikan wajah mendapat pencahayaan yang jelas.</small>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">2</div>
                    <div>
                        <h6 class="mb-1 font-weight-bold" style="font-size: 0.85rem;">Lepas Aksesoris</h6>
                        <small class="text-muted">Lepas kacamata hitam atau masker jika sedang dipakai.</small>
                    </div>
                </div>

                <div class="step-item border-0 mb-0 pb-0">
                    <div class="step-number">3</div>
                    <div>
                        <h6 class="mb-1 font-weight-bold" style="font-size: 0.85rem;">Tatap Kamera</h6>
                        <small class="text-muted">Arahkan pandangan lurus ke arah kamera.</small>
                    </div>
                </div>
            </div>

            <div class="status-card shadow-sm mt-auto mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small font-weight-bold text-uppercase">Kualitas Deteksi:</span>
                    <span id="accuracyBadge" class="badge badge-light">0%</span>
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div id="accuracyBar" class="progress-bar bg-primary" style="width: 0%"></div>
                </div>
            </div>

            <div>
                <button id="enrollBtn" class="btn btn-primary btn-enroll-large shadow-lg pulse-button" disabled>
                    <i class="fa fa-user-plus mr-2"></i> DAFTARKAN WAJAH
                </button>
                <div class="text-center mt-3">
                    <small class="text-muted font-italic"><i class="fa fa-lock mr-1"></i>Data biometrik dienkripsi
                        aman</small>
                </div>
            </div>
        </div>

        {{-- Mobile Selector Modal (Outside for centering) --}}
        <div id="mobileBackdrop" class="mobile-backdrop mobile-only" onclick="toggleMobileSelector(event)"></div>
        <div class="status-card shadow-sm mb-3 student-selector-box mobile-only" id="studentSearchCard">
            <div class="mobile-selector-header mobile-only">
                <h6 class="mb-0 font-weight-bold text-dark">PILIH SISWA</h6>
                <button class="btn btn-close-modal" onclick="toggleMobileSelector(event)">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="selector-body-mobile">
                <select id="studentSelect_mobile" class="form-control select2">
                    <option value="">-- Cari Nama Siswa --</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" data-registered="{{ $student->face_embedding ? 'true' : 'false' }}">
                            {{ strtoupper($student->user->name) }} ({{ $student->nisn }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mt-3 text-center mobile-only">
                <small class="text-muted" style="font-size: 0.75rem;">Mencari berdasarkan Nama atau NISN</small>
            </div>
        </div>
    </div>

    {{-- Native Modal for Re-Registration Warning --}}
    <div class="modal fade" id="confirmReRegisterModal" tabindex="-1" role="dialog" aria-hidden="true"
        data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-dark"><i
                            class="fa fa-exclamation-triangle text-warning mr-2"></i>Siswa Sudah Terdaftar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="cancelReRegistration()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="text-secondary mb-0">Wajah siswa ini sudah ada di sistem. Apakah Anda ingin mendaftarkan
                        ulang? <br><b class="text-danger">(Foto lama akan diganti)</b></p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-light px-4 font-weight-bold" style="border-radius: 12px;"
                        data-dismiss="modal" onclick="cancelReRegistration()">BATAL</button>
                    <button type="button" class="btn btn-primary px-4 font-weight-bold shadow-sm"
                        style="border-radius: 12px;" onclick="confirmReRegistration()">YA, DAFTAR ULANG</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('afterAppScripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let isResetting = false;

        function confirmReRegistration() {
            $('#confirmReRegisterModal').modal('hide');
        }

        function cancelReRegistration() {
            isResetting = true;
            $('#studentSelect_desktop').val(null).trigger('change.select2');
            $('#studentSelect_mobile').val(null).trigger('change.select2');
            $('#mobileStudentName').text('Cari & Pilih Siswa');
            $('#confirmReRegisterModal').modal('hide');
            setTimeout(() => { isResetting = false; }, 100);
        }

        function toggleMobileSelector(e) {
            // Prevent event bubbling to avoid accidental triggers
            if (e) e.stopPropagation();

            const card = $('.student-selector-box');
            const backdrop = $('#mobileBackdrop');
            const isShowing = card.hasClass('show');

            if (!isShowing) {
                // LOCK BODY SCROLL
                $('body').css({
                    'overflow': 'hidden',
                    'height': '100vh',
                    'touch-action': 'none'
                });

                backdrop.addClass('show');
                card.addClass('show');

                setTimeout(() => {
                    const $mobileSelect = $('#studentSelect_mobile');
                    $mobileSelect.select2('open');

                    // Force focus on mobile search field
                    setTimeout(() => {
                        const searchField = document.querySelector('.select2-search__field');
                        if (searchField) searchField.focus();
                    }, 50);
                }, 350);
            } else {
                // UNLOCK BODY SCROLL
                $('body').css({
                    'overflow': '',
                    'height': '',
                    'touch-action': ''
                });

                card.removeClass('show');
                backdrop.removeClass('show');
                $('#studentSelect_mobile').select2('close');
            }
        }

        $(document).ready(function () {
            // Init Desktop Select2
            $('#studentSelect_desktop').select2({
                placeholder: "-- Cari Nama Siswa --",
                allowClear: false,
                width: '100%'
            });

            // Init Mobile Select2
            const $mobileSelect = $('#studentSelect_mobile');
            $mobileSelect.select2({
                placeholder: "Ketik Nama atau NISN...",
                allowClear: false,
                width: '100%',
                dropdownParent: $('.selector-body-mobile')
            });

            // FIX: If Select2 closes, the modal MUST close too
            $mobileSelect.on('select2:close', function () {
                setTimeout(() => {
                    const card = $('.student-selector-box');
                    if (card.hasClass('show')) {
                        toggleMobileSelector();
                    }
                }, 50);
            });

            // SINKRONISASI: Jika desktop berubah
            $('#studentSelect_desktop').on('change', function () {
                if (isResetting) return;

                const $selected = $(this).find('option:selected');
                const val = $(this).val();
                const isRegistered = $selected.data('registered') === true || $selected.data('registered') === "true";

                if ($mobileSelect.val() !== val) {
                    $mobileSelect.val(val).trigger('change.select2');
                }

                if (val && isRegistered) {
                    $('#confirmReRegisterModal').modal('show');
                }
            });

            // SINKRONISASI: Jika mobile berubah
            $mobileSelect.on('change', function () {
                if (isResetting) return;

                const $selected = $(this).find('option:selected');
                const val = $(this).val();
                const name = $selected.text();
                const isRegistered = $selected.data('registered') === true || $selected.data('registered') === "true";

                if ($('#studentSelect_desktop').val() !== val) {
                    $('#studentSelect_desktop').val(val).trigger('change.select2');
                }

                if (val && isRegistered) {
                    $('#confirmReRegisterModal').modal('show');
                }

                if (name && !name.includes('--')) {
                    const cleanName = name.split('(')[0].trim().replace('[ TERDAFTAR ]', '').trim();
                    $('#mobileStudentName').text(cleanName);
                } else {
                    $('#mobileStudentName').text('Cari & Pilih Siswa');
                }
            });
        });

        window.enrollEndpoint = "{{ route('face.enroll.post') }}";
        window.redirectUrl = "{{ route('face.enroll') }}";
        window.csrfToken = "{{ csrf_token() }}";
    </script>
    @vite('resources/js/biometric/enroll.js')
@endsection