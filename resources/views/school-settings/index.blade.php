@extends('layouts.app')

@section('title', 'Pengaturan Sistem Sekolah')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="header py-4 px-4 bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-primary-soft text-primary mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="fa fa-cogs fa-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-weight-bold mb-0">Pengaturan Sistem Sekolah</h4>
                            <p class="text-muted mb-0 small">Konfigurasi pusat untuk jam operasional, AI, dan pemeliharaan data.</p>
                        </div>
                    </div>
                </div>

                <div class="body p-4">
                    @if(session('success'))
                        <div class="alert alert-success border-0 shadow-xs mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-check-circle fa-2x mr-3"></i>
                                <div>
                                    <h6 class="mb-0 font-weight-bold">Berhasil!</h6>
                                    <span>{{ session('success') }}</span>
                                </div>
                            </div>

                            @if(session('alpha_data'))
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-light font-weight-bold px-3 shadow-xs" data-toggle="modal" data-target="#modalAlphaResult">
                                        <i class="fa fa-users mr-1 text-success"></i> Lihat Daftar {{ count(session('alpha_data')) }} Siswa
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    <form action="{{ route('school-settings.update') }}" method="POST" id="mainSettingsForm">
                        @csrf

                        <!-- SECTION 1: WAKTU OPERASIONAL -->
                        <div class="section-header mb-4 d-flex align-items-center">
                            <i class="fa fa-clock-o text-primary mr-2"></i>
                            <h6 class="font-weight-bold mb-0 text-uppercase" style="letter-spacing: 1px;">Konfigurasi Waktu & Jam Pelajaran</h6>
                        </div>
                        
                        <div class="row mb-5">
                            @foreach($settings->filter(fn($s) => str_contains($s->key, 'jam')) as $setting)
                                <div class="col-md-4 mb-3">
                                    <div class="setting-card p-3 rounded-lg border bg-white shadow-xs h-100 transition-all">
                                        <label class="small font-weight-bold text-dark text-uppercase mb-2 d-block">
                                            {{ str_replace('_', ' ', strtoupper($setting->key)) }}
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i class="fa fa-hourglass-start text-primary"></i></span>
                                            </div>
                                            <input type="time" name="{{ $setting->key }}" value="{{ $setting->value }}" class="form-control border-left-0 font-weight-bold" required>
                                        </div>
                                        <p class="text-muted x-small mt-2 mb-0" style="font-size: 0.75rem; line-height: 1.2;">
                                            <i class="fa fa-info-circle mr-1"></i> {{ $setting->description }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @php
                            $activeDays = explode(',', $settings->where('key', 'hari_sekolah')->first()->value ?? '');
                            $days = [
                                'senin' => 'Sen', 'selasa' => 'Sel', 'rabu' => 'Rab',
                                'kamis' => 'Kam', 'jumat' => 'Jum', 'sabtu' => 'Sab', 'minggu' => 'Min',
                            ];
                            $fullDays = [
                                'senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu',
                                'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu',
                            ];
                        @endphp
                        
                        <!-- SECTION 2: HARI KERJA -->
                        <div class="section-header mb-4 d-flex align-items-center mt-2">
                            <i class="fa fa-calendar-check-o text-success mr-2"></i>
                            <h6 class="font-weight-bold mb-0 text-uppercase" style="letter-spacing: 1px;">Hari Sekolah Operasional</h6>
                        </div>

                        <div class="days-selector-wrapper mb-5">
                            <div class="d-flex flex-wrap justify-content-between">
                                @foreach($days as $key => $short)
                                    <div class="day-item flex-grow-1 mx-1 mb-2" style="min-width: 80px;">
                                        <input type="checkbox" name="hari_sekolah[]" value="{{ $key }}" 
                                               id="day_{{ $key }}" class="d-none day-checkbox" 
                                               {{ in_array($key, $activeDays) ? 'checked' : '' }}>
                                        <label for="day_{{ $key }}" class="day-label d-flex flex-column align-items-center justify-content-center p-3 rounded-lg border bg-white shadow-xs cursor-pointer transition-all w-100 h-100">
                                            <span class="small text-muted text-uppercase mb-1" style="font-size: 0.65rem;">{{ $short }}</span>
                                            <span class="font-weight-bold">{{ $fullDays[$key] }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted mt-2 d-block"><i class="fa fa-info-circle mr-1"></i> Klik pada kotak hari untuk mengaktifkan/menonaktifkan jadwal operasional.</small>
                        </div>

                        <hr class="my-5">

                        <!-- SECTION 3: AI & SECURITY -->
                        <div class="row align-items-center mb-4">
                            <div class="col-lg-12">
                                <div class="section-header mb-4 d-flex align-items-center">
                                    <i class="fa fa-user-secret text-info mr-2"></i>
                                    <h6 class="font-weight-bold mb-0 text-uppercase" style="letter-spacing: 1px;">Konfigurasi Face Recognition & AI</h6>
                                </div>
                            </div>
                            
                            <div class="col-lg-12">
                                <div class="setting-item">
                                    @foreach($settings->filter(fn($s) => !str_contains($s->key, 'jam') && $s->key !== 'hari_sekolah') as $setting)
                                        <div class="d-flex flex-column flex-md-row align-items-md-center mb-4">
                                            <div class="mr-md-4 mb-2 mb-md-0" style="flex: 1;">
                                                <label class="font-weight-bold text-dark mb-0 d-block">{{ str_replace('_', ' ', strtoupper($setting->key)) }}</label>
                                                <small class="text-muted d-block">{{ $setting->description }}</small>
                                            </div>
                                            <div style="width: 140px; max-width: 100%;">
                                                <div class="input-group input-group-sm shadow-xs border rounded overflow-hidden">
                                                    <input type="number" name="{{ $setting->key }}" value="{{ $setting->value }}" class="form-control border-0 font-weight-bold" min="1" max="90" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-light border-0 font-weight-bold small text-muted">HARI</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-none d-md-block" style="flex: 1;"></div> <!-- Spacer -->
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- GLOBAL ACTION BAR -->
                        <div class="row mt-5">
                            <div class="col-12">
                                <div class="p-3 p-md-4 bg-light rounded-lg border d-flex flex-column flex-md-row align-items-center justify-content-between shadow-xs">
                                    <div class="d-flex align-items-center text-muted mb-3 mb-md-0">
                                        <div class="icon-box mr-3 bg-white rounded-circle shadow-xs d-flex align-items-center justify-content-center d-none d-sm-flex" style="width: 40px; height: 40px; flex-shrink: 0;">
                                            <i class="fa fa-info-circle text-primary"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 font-weight-bold text-dark small text-center text-md-left">Konfigurasi Sistem Global</p>
                                            <p class="mb-0 x-small text-center text-md-left" style="font-size: 0.75rem;">Perubahan ini berdampak pada seluruh operasional presensi sekolah.</p>
                                        </div>
                                    </div>
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm transition-all hover-lift btn-confirm"
                                            data-title="Simpan Pengaturan Sekolah"
                                            data-message="Apakah Anda yakin ingin memperbaharui konfigurasi sistem sekolah ini? Perubahan akan langsung berdampak pada seluruh operasional presensi."
                                            data-form-id="mainSettingsForm"
                                            data-btn-class="btn-success"
                                            data-btn-text="Ya, Simpan Sekarang"
                                            data-toggle="modal" data-target="#confirmModal">
                                            <i class="fa fa-save mr-2"></i> SIMPAN PERUBAHAN
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="maintenance-section mt-5">
                        <div class="section-header mb-4 d-flex align-items-center">
                            <i class="fa fa-wrench text-danger mr-2"></i>
                            <h6 class="font-weight-bold mb-0 text-uppercase text-danger" style="letter-spacing: 1px;">Maintenance & Utility Hub</h6>
                        </div>
                        
                        <div class="row">
                            <!-- Sync Alpha -->
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border shadow-xs hover-shadow transition-all">
                                    <div class="card-body p-4 text-center">
                                        <div class="mx-auto bg-warning-soft text-warning rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="fa fa-users fa-2x"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-1">Sinkronisasi Alpha</h6>
                                        <p class="text-muted small mb-4">Set otomatis status Alpha untuk seluruh siswa yang tidak absen kemarin.</p>
                                        
                                        @if($isYesterdaySchoolDay)
                                            <button type="button" class="btn btn-warning btn-block btn-round btn-confirm shadow-sm"
                                                data-title="Sinkronisasi Alpha"
                                                data-message="Yakin ingin menjalankan sinkronisasi absen Alpha untuk kemarin ({{ $yesterdayFormatted }})?"
                                                data-action="{{ route('school-settings.sync-yesterday-alpha') }}"
                                                data-btn-class="btn-warning" data-btn-text="Jalankan Sinkronisasi"
                                                data-confirm-icon="fa-refresh" data-toggle="modal" data-target="#confirmModal">
                                                <i class="fa fa-refresh mr-1"></i> Jalankan
                                            </button>
                                        @else
                                            <div class="alert alert-secondary p-2 mb-2 small rounded-pill">
                                                <i class="fa fa-ban mr-1"></i> Kemarin Libur
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Purge Files -->
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border shadow-xs hover-shadow transition-all">
                                    <div class="card-body p-4 text-center">
                                        <div class="mx-auto bg-info-soft text-info rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="fa fa-file-o fa-2x"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-1">Pembersihan Lampiran</h6>
                                        <p class="text-muted small mb-4">Terdeteksi <strong>{{ $oldFilesCount }} file</strong> bukti/lampiran yang usianya > 30 hari.</p>
                                        
                                        <button type="button" class="btn btn-info btn-block btn-round btn-confirm"
                                            {{ $oldFilesCount == 0 ? 'disabled' : '' }}
                                            data-title="Bersihkan Lampiran Lama"
                                            data-message="Hapus secara permanen {{ $oldFilesCount }} file bukti lama?"
                                            data-action="{{ route('school-settings.purge-attachments') }}"
                                            data-btn-class="btn-info" data-btn-text="Bersihkan Sekarang"
                                            data-confirm-icon="fa-eraser" data-toggle="modal" data-target="#confirmModal">
                                            <i class="fa fa-eraser mr-1"></i> {{ $oldFilesCount > 0 ? 'Hapus File' : 'Sudah Bersih' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Reset Logs -->
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border shadow-xs hover-shadow transition-all">
                                    <div class="card-body p-4 text-center">
                                        <div class="mx-auto bg-danger-soft text-danger rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="fa fa-trash-o fa-2x"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-1">Reset Face Logs</h6>
                                        <p class="text-muted small mb-4">Hapus riwayat log (gambar & data) verifikasi wajah dari database & storage.</p>
                                        
                                        <button type="button" class="btn btn-danger btn-block btn-round btn-confirm"
                                            data-title="Reset Face Logs"
                                            data-message="TINDAKAN PERMANEN! Seluruh riwayat log wajah akan dihapus. Lanjutkan?"
                                            data-action="{{ route('school-settings.reset-face-logs') }}"
                                            data-btn-class="btn-danger" data-btn-text="Hapus Log"
                                            data-confirm-icon="fa-trash" data-toggle="modal" data-target="#confirmModal">
                                            <i class="fa fa-trash mr-1"></i> Kosongkan Log
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal-confirm />
    @include('school-settings.partials._modal_alpha')
@endsection

@section('afterAppScripts')
    <style>
        /* Modern Day Selector Styling */
        .day-checkbox:checked + .day-label {
            background-color: #2b70f0 !important;
            border-color: #1e5cd1 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(43, 112, 240, 0.3) !important;
            transform: translateY(-2px);
        }
        
        .day-checkbox:checked + .day-label .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .day-label:hover {
            border-color: #2b70f0;
            background-color: #f8faff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        }

        .bg-primary-soft { background-color: rgba(43, 112, 240, 0.1); }
        .bg-success-soft { background-color: rgba(40, 167, 69, 0.1); }
        .bg-info-soft    { background-color: rgba(23, 162, 184, 0.1); }
        .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
        .bg-danger-soft  { background-color: rgba(220, 53, 69, 0.1); }

        .image-checkbox input[type="checkbox"] {
            display: none;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Reusable search function untuk modal asli maupun modal dummy
            function applyLiveSearch(inputId, tableId) {
                const searchInput = document.getElementById(inputId);
                const tableBody = document.querySelector('#' + tableId + ' tbody');
                
                if (searchInput && tableBody) {
                    searchInput.addEventListener('input', function () {
                        const filter = this.value.toLowerCase();
                        const rows = tableBody.getElementsByTagName('tr');

                        for (let i = 0; i < rows.length; i++) {
                            let textContent = rows[i].textContent || rows[i].innerText;
                            if (textContent.toLowerCase().indexOf(filter) > -1) {
                                rows[i].style.display = '';
                            } else {
                                rows[i].style.display = 'none';
                            }
                        }
                    });
                }
            }

            // Pasang ke tabel asli denga id modal hasil sinkronisasi
            applyLiveSearch('searchAlphaStudent', 'alphaStudentTable');
        });
    </script>
@endsection