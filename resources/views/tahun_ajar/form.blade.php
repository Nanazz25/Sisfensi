@extends('layouts.app')

@section('title', isset($tahunAjar) ? 'Edit Tahun Ajar' : 'Tambah Tahun Ajar')

@section('content')
<div class="card">
    <div class="header">
        <h2>{{ isset($tahunAjar) ? 'Edit Tahun Ajar' : 'Tambah Tahun Ajar' }}</h2>
    </div>

    <div class="body">
        <form action="{{ isset($tahunAjar)
            ? route('tahun-ajar.update', $tahunAjar->id)
            : route('tahun-ajar.store') }}" method="POST">

            @csrf
            @if(isset($tahunAjar))
                @method('PUT')
            @endif

            <div class="form-group">
                <label>Nama Tahun Ajar</label>
                <input type="text" name="nama" class="form-control"
                    value="{{ old('nama', $tahunAjar->nama ?? '') }}"
                    placeholder="2024/2025" required>
            </div>

            <div class="form-group">
                <label>Semester</label>
                <select name="semester" class="form-control" required>
                    <option value="">-- pilih --</option>
                    <option value="ganjil"
                        {{ old('semester', $tahunAjar->semester ?? '') == 'ganjil' ? 'selected' : '' }}>
                        Ganjil
                    </option>
                    <option value="genap"
                        {{ old('semester', $tahunAjar->semester ?? '') == 'genap' ? 'selected' : '' }}>
                        Genap
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control"
                    value="{{ old('tanggal_mulai', $tahunAjar->tanggal_mulai ?? '') }}" required>
            </div>

            <div class="form-group">
                <label>Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control"
                    value="{{ old('tanggal_selesai', $tahunAjar->tanggal_selesai ?? '') }}" required>
            </div>

            <div class="form-group">
                <label class="custom-switch mt-2">
                    <input type="checkbox" name="is_active" value="1"
                        class="custom-switch-input"
                        {{ old('is_active', $tahunAjar->is_active ?? false) ? 'checked' : '' }}>
                    <span class="custom-switch-indicator"></span>
                    <span class="custom-switch-description">Jadikan Tahun Ajar Aktif</span>
                </label>
            </div>

            <button class="btn btn-primary">
                {{ isset($tahunAjar) ? 'Update' : 'Simpan' }}
            </button>

            <a href="{{ route('tahun-ajar.index') }}" class="btn btn-secondary">
                Kembali
            </a>
        </form>
    </div>
</div>
@endsection
