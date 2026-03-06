@extends('layouts.app')

@section('title', 'Pengaturan Sistem Sekolah')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Pengaturan Sistem Sekolah</h2>
                    <small>Atur jam operasional dan pengelolaan Face Recognition</small>
                </div>

                <div class="body">
                    @if(session('success'))
                        <div class="alert alert-success mt-3">
                            <i class="fa fa-check-circle mr-1"></i> {{ session('success') }}

                            @if(session('alpha_data'))
                                <hr>
                                <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal"
                                    data-target="#modalAlphaResult">
                                    <i class="fa fa-users mr-1"></i> Lihat Daftar Siswa
                                </button>

                                <!-- Modal Daftar Siswa -->
                                <div class="modal fade" id="modalAlphaResult" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Daftar Siswa Yang Di-ALPHA-kan (Kemarin)</h5>
                                                <button type="button" class="close"
                                                    data-dismiss="modal"><span>&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Nama Siswa</th>
                                                                <th>Kelas (Rombel)</th>
                                                                <th>Wali Kelas</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach(session('alpha_data') as $student)
                                                                <tr>
                                                                    <td>{{ $student['name'] }}</td>
                                                                    <td>{{ $student['rombel'] }}</td>
                                                                    <td>{{ $student['walas'] }}</td>
                                                                </tr>
                                                            @endforeach
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
                        </div>
                    @endif

                    <form action="{{ route('school-settings.update') }}" method="POST">
                        @csrf

                        <h5 class="mb-3">Pengaturan Jam Sekolah</h5>
                        <div class="row">
                            @foreach($settings->filter(fn($s) => str_contains($s->key, 'jam')) as $setting)
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            {{ str_replace('_', ' ', strtoupper($setting->key)) }}
                                        </label>
                                        <input type="time" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                            class="form-control" required>
                                        <small class="text-muted">
                                            {{ $setting->description }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr>

                        <h5 class="mb-3">Hari Sekolah Operasional</h5>
                        @php
                            $activeDays = explode(',', $settings->where('key', 'hari_sekolah')->first()->value ?? '');
                            $days = [
                                'senin' => 'Senin',
                                'selasa' => 'Selasa',
                                'rabu' => 'Rabu',
                                'kamis' => 'Kamis',
                                'jumat' => 'Jumat',
                                'sabtu' => 'Sabtu',
                                'minggu' => 'Minggu',
                            ];
                        @endphp
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex flex-wrap">
                                    @foreach($days as $key => $label)
                                        <div class="fancy-checkbox mr-4 mb-2">
                                            <label>
                                                <input type="checkbox" name="hari_sekolah[]" value="{{ $key }}"
                                                    {{ in_array($key, $activeDays) ? 'checked' : '' }}>
                                                <span>{{ $label }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted d-block mt-1">Ceklis hari-hari di mana jadwal sekolah aktif.</small>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3">Face Recognition</h5>
                        <div class="row">
                            @foreach($settings->filter(fn($s) => !str_contains($s->key, 'jam') && $s->key !== 'hari_sekolah') as $setting)
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            {{ str_replace('_', ' ', strtoupper($setting->key)) }}
                                        </label>
                                        <input type="number" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                            class="form-control" min="1" max="90" required>
                                        <small class="text-muted">
                                            {{ $setting->description }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary btn-round mt-3">
                            <i class="fa fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </form>

                    <hr class="mt-5">

                    <h5 class="mb-3 text-danger"><i class="fa fa-wrench mr-2"></i>Maintenance & Utility</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="body">
                                    <h6>Sinkronisasi Alpha</h6>
                                    <p class="small text-muted">Gunakan tombol ini jika sistem otomatis gagal meng-Alpha-kan
                                        siswa yang tidak hadir kemarin.</p>
                                    <button type="button" class="btn btn-warning btn-block btn-confirm"
                                        data-title="Sinkronisasi Alpha"
                                        data-message="Yakin ingin menjalankan sinkronisasi absen Alpha untuk tanggal kemarin?"
                                        data-action="{{ route('school-settings.sync-yesterday-alpha') }}"
                                        data-btn-class="btn-warning" data-btn-text="Jalankan Sinkronisasi"
                                        data-confirm-icon="fa-refresh" data-toggle="modal" data-target="#confirmModal">
                                        <i class="fa fa-refresh mr-1"></i> Sinkronisasi Alpha Kemarin
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-danger">
                                <div class="body">
                                    <h6>Reset Face Logs</h6>
                                    <p class="small text-muted">Hapus semua data log foto wajah yang tersimpan di database
                                        dan storage untuk mengosongkan ruang.</p>
                                    <button type="button" class="btn btn-danger btn-block btn-confirm"
                                        data-title="Reset Face Logs"
                                        data-message="Tindakan ini akan menghapus SELURUH log wajah di database dan storage secara permanen. Lanjutkan?"
                                        data-action="{{ route('school-settings.reset-face-logs') }}"
                                        data-btn-class="btn-danger" data-btn-text="Hapus Permanen"
                                        data-confirm-icon="fa-trash" data-toggle="modal" data-target="#confirmModal">
                                        <i class="fa fa-trash mr-1"></i> Reset Seluruh Face Logs
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
@endsection