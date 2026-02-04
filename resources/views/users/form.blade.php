@extends('layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Tambah User')

@php
    $backRoute = isset($user)
        ? match ($user->role) {
            'admin' => route('users.admin'),
            'guru' => route('users.guru'),
            'siswa' => route('users.siswa'),
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
                    </select>
                </div>

                @if(!isset($user))
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                @endif

                @if(isset($user))
                    <div class="form-group">
                        <label>Password (opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                    </div>
                @endif

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