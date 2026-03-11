@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
    <div class="row clearfix justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title font-weight-bold mb-0 text-primary">
                        <i class="fa fa-lock mr-2"></i> Pengaturan Keamanan
                    </h6>
                </div>
                <div class="card-body px-4 py-4">
                    <div class="alert alert-info border-0 shadow-none mb-4"
                        style="background: rgba(23, 162, 184, 0.1); color: #0c5460;">
                        <i class="fa fa-info-circle mr-2"></i>
                        Pastikan password Anda minimal 8 karakter dan merupakan kombinasi yang sulit ditebak.
                    </div>

                    <form action="{{ route('profile.password.update') }}" method="POST">
                        @csrf

                        <div class="form-group mb-4">
                            <label class="font-weight-600 text-dark">Password Saat Ini</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i
                                            class="fa fa-key text-muted"></i></span>
                                </div>
                                <input type="password" name="current_password"
                                    class="form-control border-left-0 border-right-0 @error('current_password') is-invalid @enderror"
                                    placeholder="Masukkan password sekarang" required>
                                <div class="input-group-append">
                                    <button class="btn btn-light border-left-0 toggle-password" type="button"
                                        style="border: 1px solid #ced4da; border-left: none;">
                                        <i class="fa fa-eye-slash text-muted"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-600 text-dark">Password Baru</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i
                                            class="fa fa-shield text-muted"></i></span>
                                </div>
                                <input type="password" name="password"
                                    class="form-control border-left-0 border-right-0 @error('password') is-invalid @enderror"
                                    placeholder="Minimal 8 karakter" required>
                                <div class="input-group-append">
                                    <button class="btn btn-light border-left-0 toggle-password" type="button"
                                        style="border: 1px solid #ced4da; border-left: none;">
                                        <i class="fa fa-eye-slash text-muted"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-600 text-dark">Konfirmasi Password Baru</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i
                                            class="fa fa-check-circle text-muted"></i></span>
                                </div>
                                <input type="password" name="password_confirmation"
                                    class="form-control border-left-0 border-right-0"
                                    placeholder="Ketik ulang password baru" required>
                                <div class="input-group-append">
                                    <button class="btn btn-light border-left-0 toggle-password" type="button"
                                        style="border: 1px solid #ced4da; border-left: none;">
                                        <i class="fa fa-eye-slash text-muted"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 text-right">
                            <a href="{{ route('profile.show') }}" class="btn btn-light px-4 mr-2 shadow-sm border">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm font-weight-bold">
                                Update Password <i class="fa fa-save ml-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('afterAppScripts')
    <script>
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const group = this.closest('.input-group');
                const input = group.querySelector('input');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        });
    </script>
@endsection
