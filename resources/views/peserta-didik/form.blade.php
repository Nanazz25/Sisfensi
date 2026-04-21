@extends('layouts.app')
@section('title', $siswa ? 'Edit Peserta Didik' : 'Tambah Peserta Didik')

@section('afterAppStyles')
<style>
    .form-control:focus { border-color: #00bcd4; box-shadow: 0 0 0 0.2rem rgba(0, 188, 212, 0.15); }
    .select2-container--default .select2-selection--single { height: 38px; border-radius: 8px; border: 1px solid #ced4da; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>
@endsection

@section('content')

    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="header bg-white py-4 px-4" style="border-radius: 15px 15px 0 0; border-bottom: 2px solid #f8f9fa;">
            <h5 class="mb-0 font-weight-bold text-dark">
                <i class="fa {{ $siswa ? 'fa-edit' : 'fa-id-card-o' }} mr-2 text-info"></i>
                {{ $siswa ? 'Perbarui Profil Peserta Didik' : 'Pendaftaran Profil Peserta Didik' }}
            </h5>
            <p class="text-muted small mb-0 mt-1">Lengkapi data master siswa sesuai dengan dokumen resmi untuk keperluan administrasi sekolah.</p>
        </div>

        <div class="body p-4">
            <form method="POST" action="{{ $siswa
        ? route('peserta-didik.update', $siswa->id)
        : route('peserta-didik.store') }}">

                @csrf
                @if($siswa)
                    @method('PUT')
                @else
                    <!-- 1. USER ID -->
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark mb-2">Akun User Siswa <span class="text-danger">*</span></label>
                        <select name="user_id" id="userSelect" class="form-control select2 rounded-lg @error('user_id') is-invalid @enderror" required>
                            <option value="">-- Pilih akun yang tersedia --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" 
                                    data-name="{{ $user->name }}"
                                    {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Siswa harus memiliki akun di <b>Menu Manajemen User</b> terlebih dahulu.</small>
                        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endif

                <div class="row">
                    <!-- 2. NAMA LENGKAP -->
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark mb-2">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" id="namaLengkap" class="form-control rounded-lg @error('nama_lengkap') is-invalid @enderror"
                                value="{{ old('nama_lengkap', $siswa->nama_lengkap ?? '') }}" placeholder="Masukkan nama lengkap sesuai ijazah" required>
                            @error('nama_lengkap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- 3. NO INDUK -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark mb-2">No Induk / NIS <span class="text-danger">*</span></label>
                            <input type="text" name="no_induk" class="form-control rounded-lg @error('no_induk') is-invalid @enderror"
                                value="{{ old('no_induk', $siswa ? $siswa->no_induk : $nis) }}" placeholder="Contoh: 2026001" required>
                            @error('no_induk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- 4. NISN -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark mb-2">NISN <span class="text-danger">*</span></label>
                            <input type="text" name="nisn" class="form-control rounded-lg @error('nisn') is-invalid @enderror" 
                                value="{{ old('nisn', $siswa->nisn ?? '') }}" placeholder="Masukkan 10 digit NISN" required>
                            @error('nisn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- 5. NIK -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark mb-2">NIK (Nomor Induk Kependudukan)</label>
                            <input type="text" name="nik" class="form-control rounded-lg @error('nik') is-invalid @enderror" 
                                value="{{ old('nik', $siswa->nik ?? '') }}" placeholder="Masukkan 16 digit NIK (Opsional)">
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- 6. JENIS KELAMIN -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark mb-2">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-control rounded-lg @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- 7. TEMPAT LAHIR -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark mb-2">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control rounded-lg" 
                                value="{{ old('tempat_lahir', $siswa->tempat_lahir ?? '') }}" placeholder="Contoh: Jakarta">
                        </div>
                    </div>

                    <!-- 8. TANGGAL LAHIR -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark mb-2">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control rounded-lg" 
                                value="{{ old('tanggal_lahir', $siswa ? ($siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('Y-m-d') : '') : '') }}">
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mb-4 mt-2 border-0 shadow-xs" style="border-radius: 10px;">
                    <div class="d-flex">
                        <div class="mr-3"><i class="fa fa-camera fa-2x"></i></div>
                        <div>
                            <h6 class="font-weight-bold mb-1">Registrasi Biometrik</h6>
                            <p class="small mb-0">Foto wajah dan data sensor AI (embedding) akan didaftarkan secara terpisah melalui menu <b>Detail Siswa > Registrasi Wajah</b> setelah data master ini disimpan.</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-5">
                    <a href="{{ route('peserta-didik.index') }}" class="btn btn-light px-4 font-weight-bold">
                        <i class="fa fa-arrow-left mr-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-info px-5 py-2 font-weight-bold shadow-sm" style="border-radius: 10px;">
                        <i class="fa fa-check-circle mr-2"></i> {{ $siswa ? 'Simpan Perubahan' : 'Daftarkan Siswa' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('afterAppScripts')
<script>
    $(document).ready(function() {
        // Auto-fill Nama Lengkap from selected User
        $('#userSelect').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const userName = selectedOption.data('name');
            const nameInput = $('#namaLengkap');
            
            if (userName && (nameInput.val() === '' || nameInput.val().toLowerCase().includes('copy'))) {
                nameInput.val(userName);
            }
        });

        // Initialize Select2 if available
        if ($.fn.select2) {
            $('.select2').select2({
                placeholder: "-- Pilih Akun --",
                allowClear: true,
                width: '100%'
            });
        }
    });
</script>
@endsection