@extends('layouts.app')

@section('title', 'Ajukan Izin/Sakit')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="header">
                    <h2>Form Pengajuan Izin / Sakit</h2>
                </div>
                <div class="body">
                    <form action="{{ route('attendance-permissions.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Mulai</label>
                                    <input type="date" name="tanggal_mulai"
                                        class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                        value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                                    @error('tanggal_mulai') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai"
                                        class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                        value="{{ old('tanggal_selesai', date('Y-m-d')) }}" required>
                                    @error('tanggal_selesai') <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Jenis Keterangan</label>
                            <select name="jenis" class="form-control" required>
                                <option value="izin" {{ old('jenis') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ old('jenis') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Alasan / Keterangan</label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
                                rows="4" placeholder="Jelaskan alasan Anda..." required>{{ old('keterangan') }}</textarea>
                            @error('keterangan') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Lampiran (Opsional)</label>

                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" name="lampiran" class="custom-file-input" id="lampiranInput"
                                        accept=".jpg,.jpeg,.png,.pdf">
                                    <label class="custom-file-label" for="lampiranInput">
                                        Pilih file...
                                    </label>
                                </div>
                            </div>

                            <small class="text-muted d-block mt-1">
                                Upload surat dokter atau bukti pendukung. (Format: JPG, PNG, PDF. Maksimal 2MB)
                            </small>

                            @error('lampiran')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-paper-plane mr-2"></i> Kirim Pengajuan
                            </button>
                            <a href="{{ route('attendance-permissions.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('afterAppScripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('lampiranInput');

            input.addEventListener('change', function (e) {
                let fileName = e.target.files[0]?.name;
                if (fileName) {
                    e.target.nextElementSibling.innerHTML = fileName;
                }
            });
        });
    </script>
@endsection