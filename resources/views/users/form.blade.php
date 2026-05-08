@extends('layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Tambah User')

@php
    $backRoute = isset($user)
        ? match ($user->role) {
            'admin' => route('users.admin'),
            'guru' => route('users.guru'),
            'siswa' => route('users.siswa'),
            'helpdesk' => route('users.helpdesk'),
            default => url()->previous(),
        }
        : url()->previous();
@endphp

@section('content')
    <div class="card">
        <div class="header">
            <h2>{{ isset($user) ? 'Edit User' : 'Tambah User' }}</h2>
        </div>

        <div class="body">
            <form action="{{ isset($user)
        ? route('users.update', $user->id)
        : route('users.store') }}" method="POST">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}"
                        required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}"
                        required>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control" required>
                        <option value="">-- pilih --</option>
                        <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="guru" {{ old('role', $user->role ?? '') == 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="siswa" {{ old('role', $user->role ?? '') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="helpdesk" {{ old('role', $user->role ?? '') == 'helpdesk' ? 'selected' : '' }}>Helpdesk</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Password {{ isset($user) ? '(opsional)' : '' }}</label>
                    <div class="input-group shadow-xs">
                        <input type="password" name="password" id="passwordInput" class="form-control" 
                            {{ !isset($user) ? 'required' : '' }}
                            placeholder="{{ isset($user) ? 'Kosongkan jika tidak diubah' : 'Masukkan password baru' }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary toggle-password" type="button" 
                                style="border-color: #ced4da; border-left: none;">
                                <i class="fa fa-eye-slash text-muted"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary">
                    {{ isset($user) ? 'Update' : 'Simpan' }}
                </button>

                <a href="{{ $backRoute }}" class="btn btn-secondary">
                    Kembali
                </a>
            </form>
        </div>
    </div>
@endsection

@section('afterAppScripts')
    <script>
        document.querySelector('.toggle-password').addEventListener('click', function () {
            const input = document.getElementById('passwordInput');
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
    </script>
@endsection