@extends('layouts.app')

@section('title', 'Pendaftaran Biometrik')

@section('afterAppStyles')
    @vite('resources/css/biometric.css')
@endsection

@section('content')
    <div class="scanner-layout">
        <div class="camera-section" id="cameraWrapper">
            <video id="video" autoplay muted></video>
            <canvas id="canvas"></canvas>

            <div style="position: absolute; top: 20px; left: 20px; z-index: 10;">
                <div class="status-badge-inline shadow">
                    <div id="statusDot" class="dot"></div>
                    <span id="statusText">System Initializing</span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="status-card shadow-sm">
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
                        <small class="text-muted">Arahkan pandangan lurus ke area kotak hijau.</small>
                    </div>
                </div>
            </div>

            <div class="status-card shadow-sm mt-auto">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small font-weight-bold text-uppercase">Tingkat Akurasi:</span>
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
                    <small class="text-muted font-italic"><i class="fa fa-lock mr-1"></i>Data biometrik Anda
                        dienkripsi</small>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('afterAppScripts')
    <script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        window.enrollEndpoint = "{{ route('face.enroll.post') }}";
        window.redirectUrl = "{{ route('dashboard.index') }}";
        window.csrfToken = "{{ csrf_token() }}";
    </script>
    @vite('resources/js/biometric/enroll.js')
@endsection