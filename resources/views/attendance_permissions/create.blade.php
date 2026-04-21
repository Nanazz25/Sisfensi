@extends('layouts.app')

@section('title', 'Ajukan Izin/Sakit/Manual')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-8 col-md-12">
            <div class="card shadow-sm border-0">
                <div class="header pb-0">
                    <h2 class="font-weight-bold">Form Pengajuan</h2>
                </div>
                <div class="body">
                    @if($monthlyCount >= 3)
                        <div class="alert alert-warning border-0 shadow-xs mb-4 p-3 d-flex align-items-center rounded-lg" id="frequency_warning_alert">
                            <div class="icon-circle bg-warning text-white mr-3 shadow-sm" style="width:40px; height:40px; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                                <i class="fa fa-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 font-weight-bold">Peringatan Frekuensi</h6>
                                <p class="mb-0 small opacity-75">Anda sudah mengajukan izin/sakit sebanyak <strong>{{ $monthlyCount }} kali</strong> bulan ini. Gunakan izin hanya untuk keperluan mendesak.</p>
                            </div>
                        </div>
                    @endif

                    <div class="alert alert-danger border-0 shadow-xs mb-4 p-3 d-none align-items-center rounded-lg" id="manual_limit_alert">
                        <div class="icon-circle bg-danger text-white mr-3 shadow-sm" style="width:40px; height:40px; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 font-weight-bold">Batas Waktu Habis</h6>
                            <p class="mb-0 small opacity-75">Pengajuan <strong>Absen Manual</strong> tidak dapat dilakukan karena sudah melewati jam pulang ({{ substr($jamPulang, 0, 5) }}). Silakan hubungi admin sekolah jika ada kendala mendesak.</p>
                        </div>
                    </div>

                    <form action="{{ route('attendance-permissions.store') }}" method="POST" enctype="multipart/form-data" id="permissionForm">
                        @csrf
                        <input type="hidden" name="latitude" id="lat">
                        <input type="hidden" name="longitude" id="lng">
                        <input type="hidden" name="tanggal_selesai" id="tanggal_selesai_hidden" value="{{ date('Y-m-d') }}">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase" style="letter-spacing: 0.5px;">Jenis Keterangan</label>
                                <select name="jenis" id="jenis_select" class="form-control custom-select rounded-lg" required>
                                    <option value="izin" {{ (old('jenis') == 'izin' || request('type') == 'izin') ? 'selected' : '' }}>Izin (Acara Keluarga, dll)</option>
                                    <option value="sakit" {{ (old('jenis') == 'sakit' || request('type') == 'sakit') ? 'selected' : '' }}>Sakit</option>
                                    <option value="manual" {{ (old('jenis') == 'manual' || request('type') == 'manual') ? 'selected' : '' }}>Absen Manual (Kendala Sistem)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase" style="letter-spacing: 0.5px;">Tanggal Pelaksanaan</label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                    class="form-control rounded-lg @error('tanggal_mulai') is-invalid @enderror"
                                    value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                                <small class="form-text text-muted" id="date_hint">Hanya berlaku untuk 1 hari.</small>
                                @error('tanggal_mulai') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-group mb-4 d-none" id="manual_type_wrapper">
                            <label class="small font-weight-bold text-muted text-uppercase" style="letter-spacing: 0.5px;">Kategori Absensi</label>
                            <div class="d-flex flex-wrap" style="gap: 10px;">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="manual_masuk" name="jenis_absensi_manual" value="masuk" class="custom-control-input" checked>
                                    <label class="custom-control-label" for="manual_masuk">Masuk</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="manual_mapel" name="jenis_absensi_manual" value="mapel" class="custom-control-input">
                                    <label class="custom-control-label" for="manual_mapel">Mapel</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="manual_pulang" name="jenis_absensi_manual" value="pulang" class="custom-control-input">
                                    <label class="custom-control-label" for="manual_pulang">Pulang</label>
                                </div>
                            </div>
                        </div>

                        <!-- GPS Card Modern -->
                        <div id="gps_card" class="card border d-none mb-4 shadow-xs" style="border-radius:15px; background: #f8faff;">
                            <div class="body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-lg shadow-sm mr-3"
                                        style="width:45px; height:45px; display:flex; align-items:center; justify-content:center; font-size: 1.2rem;">
                                        <i class="fa fa-map-marker"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 font-weight-bold" style="font-size: 0.9rem;">Lokasi Perangkat</h6>
                                        <small id="gps_status" class="text-muted">Mendeteksi koordinat...</small>
                                        <div id="coords_display" class="small font-weight-bold text-primary d-none mt-1">-- , --</div>
                                    </div>
                                    <div id="gps_indicator" class="pulse-status" style="width:12px; height:12px; background:#ddd; border-radius:50%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-muted text-uppercase" style="letter-spacing: 0.5px;">Alasan / Keterangan</label>
                            <textarea name="keterangan" class="form-control rounded-lg @error('keterangan') is-invalid @enderror"
                                rows="3" placeholder="Jelaskan keperluan Anda dengan jelas..."
                                required>{{ old('keterangan') }}</textarea>
                            @error('keterangan') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group" id="attachment_wrapper">
                            <label class="small font-weight-bold text-muted text-uppercase" style="letter-spacing: 0.5px;">
                                Lampiran Bukti (Foto/PDF) <span id="lampiran_required_marker" class="text-danger d-none">*Wajib</span>
                            </label>
                            <div class="custom-file">
                                <input type="file" name="lampiran" class="custom-file-input" id="lampiranInput" accept="image/*,.pdf">
                                <label class="custom-file-label rounded-lg" for="lampiranInput" id="lampiranLabel">Pilih file bukti...</label>
                            </div>
                        </div>

                        <div class="mt-5 pt-3 border-top d-flex align-items-center justify-content-between">
                            <a href="{{ route('attendance-permissions.index') }}" class="btn btn-light rounded-pill px-4">Batal</a>
                            <button type="submit" id="submitBtn" class="btn btn-primary rounded-pill px-5 py-2 font-weight-bold shadow">
                                <i class="fa fa-paper-plane mr-2"></i> Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12">
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
                <div class="bg-primary p-4 text-white">
                    <h5 class="font-weight-bold mb-1">Panduan Pengajuan</h5>
                    <p class="mb-0 small opacity-75">Harap perhatikan aturan berikut:</p>
                </div>
                <div class="body p-4 bg-white">
                    <div class="d-flex mb-4">
                        <div class="text-primary mr-3"><i class="fa fa-calendar-check-o fa-lg"></i></div>
                        <div>
                            <h6 class="mb-1 font-weight-bold" style="font-size: 0.85rem;">Satu Hari Per Izin</h6>
                            <p class="text-muted small mb-0">Setiap pengajuan hanya berlaku untuk 1 hari. Jika sakit berlanjut, harap ajukan kembali esok hari.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="text-warning mr-3"><i class="fa fa-clock-o fa-lg"></i></div>
                        <div>
                            <h6 class="mb-1 font-weight-bold" style="font-size: 0.85rem;">Batas Frekuensi</h6>
                            <p class="text-muted small mb-0">Siswa yang izin/sakit lebih dari 3x dalam sebulan akan mendapatkan perhatian khusus dari tim kesiswaan.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-0">
                        <div class="text-info mr-3"><i class="fa fa-shield fa-lg"></i></div>
                        <div>
                            <h6 class="mb-1 font-weight-bold" style="font-size: 0.85rem;">Validasi Manual</h6>
                            <p class="text-muted small mb-0">Absen manual wajib menyertakan lokasi GPS dan alasan kendala teknis yang valid.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($monthlyCount >= 3)
            <div class="card border-0 shadow-sm mt-3" style="border-radius: 15px; background: #fff5f5;" id="frequency_warning_card">
                <div class="body p-3 text-center">
                    <i class="fa fa-exclamation-triangle text-danger mb-2" style="font-size: 2rem;"></i>
                    <h6 class="text-danger font-weight-bold mb-1">Sudah Sering Izin</h6>
                    <p class="small text-muted mb-0">Bulan ini Anda sudah izin {{ $monthlyCount }} kali. Pastikan Anda tetap mengikuti pelajaran tertinggal.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

@section('afterAppScripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('lampiranInput');
            const jenisSelect = document.getElementById('jenis_select');
            const manualTypeWrapper = document.getElementById('manual_type_wrapper');
            const gpsCard = document.getElementById('gps_card');
            const submitBtn = document.getElementById('submitBtn');
            const latInput = document.getElementById('lat');
            const lngInput = document.getElementById('lng');
            const tglMulai = document.getElementById('tanggal_mulai');
            const tglSelesaiHidden = document.getElementById('tanggal_selesai_hidden');
            const dateHint = document.getElementById('date_hint');
            const freqAlert = document.getElementById('frequency_warning_alert');
            const freqCard = document.getElementById('frequency_warning_card');
            const manualLimitAlert = document.getElementById('manual_limit_alert');

            const today = "{{ date('Y-m-d') }}";
            const jamPulangLimit = "{{ $jamPulang }}";

            function isAfterDismissal() {
                const now = new Date();
                const currentTime = now.getHours().toString().padStart(2, '0') + ":" + now.getMinutes().toString().padStart(2, '0');
                return currentTime >= jamPulangLimit;
            }

            // Sync hidden end date
            tglMulai.addEventListener('change', function() {
                tglSelesaiHidden.value = this.value;
            });

            // Handle file name label
            if (input) {
                input.addEventListener('change', function (e) {
                    let fileName = e.target.files[0]?.name;
                    if (fileName) {
                        e.target.nextElementSibling.innerHTML = fileName;
                    }
                });
            }

            // Handle manual logic
            jenisSelect.addEventListener('change', function () {
                const reqMarker = document.getElementById('lampiran_required_marker');
                const lampiranInput = document.getElementById('lampiranInput');

                if (this.value === 'manual') {
                    manualTypeWrapper.classList.remove('d-none');
                    gpsCard.classList.remove('d-none');
                    reqMarker.classList.remove('d-none');
                    lampiranInput.required = true;
                    
                    // Lock to today
                    tglMulai.value = today;
                    tglMulai.readOnly = true;
                    tglSelesaiHidden.value = today;
                    
                    if (isAfterDismissal()) {
                        dateHint.innerHTML = "Absen manual tidak bisa dilakukan karena sudah lewat jam pulang (" + jamPulangLimit.substring(0, 5) + ").";
                        dateHint.classList.add('text-danger');
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50');
                        manualLimitAlert.classList.remove('d-none');
                        manualLimitAlert.classList.add('d-flex');
                    } else {
                        dateHint.innerHTML = "Absen manual hanya bisa dilakukan untuk hari ini.";
                        dateHint.classList.add('text-primary');
                        dateHint.classList.remove('text-danger');
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50');
                        manualLimitAlert.classList.add('d-none');
                        manualLimitAlert.classList.remove('d-flex');
                    }
                    
                    getLocation();
                } else {
                    manualTypeWrapper.classList.add('d-none');
                    gpsCard.classList.add('d-none');
                    manualLimitAlert.classList.add('d-none');
                    manualLimitAlert.classList.remove('d-flex');
                    reqMarker.classList.add('d-none');
                    lampiranInput.required = false;
                    
                    // Unlock date
                    tglMulai.readOnly = false;
                    dateHint.innerHTML = "Hanya berlaku untuk 1 hari.";
                    dateHint.classList.remove('text-danger');
                    dateHint.classList.remove('text-primary');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50');
                }

                // Toggle frequency warnings (only show for izin/sakit)
                const showWarning = (this.value === 'izin' || this.value === 'sakit');
                if (freqAlert) freqAlert.classList.toggle('d-none', !showWarning);
                if (freqCard) freqCard.classList.toggle('d-none', !showWarning);
            });

            // Trigger initial state
            jenisSelect.dispatchEvent(new Event('change'));

            function getLocation() {
                if (!navigator.geolocation) {
                    updateGPSStatus("GPS tidak didukung", "red");
                    return;
                }

                updateGPSStatus("Sedang mengambil lokasi...", "orange");

                navigator.geolocation.getCurrentPosition(function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    latInput.value = lat;
                    lngInput.value = lng;

                    document.getElementById('coords_display').innerText = lat.toFixed(6) + ", " + lng.toFixed(6);
                    document.getElementById('coords_display').classList.remove('d-none');
                    updateGPSStatus("Lokasi berhasil dikunci", "#28a745");
                }, function (error) {
                    console.error("GPS Error:", error);
                    updateGPSStatus("Gagal mengambil lokasi (Cek Izin GPS)", "red");
                }, { enableHighAccuracy: true });
            }

            function updateGPSStatus(text, color) {
                const status = document.getElementById('gps_status');
                const indicator = document.getElementById('gps_indicator');
                if (status) status.innerText = text;
                if (indicator) indicator.style.background = color;
            }
        });
    </script>
@endsection