@extends('layouts.app')
@section('title', 'Form Guru')

@section('content')
    <div class="card">
        <div class="header">
            <h2>{{ $teacher ? 'Edit Guru' : 'Tambah Guru' }}</h2>
        </div>

        <div class="body">
            <form method="POST" action="{{ $teacher
        ? route('teachers.update', $teacher->id)
        : route('teachers.store') }}">
                @csrf
                @if($teacher)
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control"
                        value="{{ old('nama_lengkap', $teacher->nama_lengkap ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control" required>
                        <option value="">-- pilih --</option>
                        <option value="L" {{ old('jenis_kelamin', $teacher->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>
                            Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $teacher->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>
                            Perempuan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>NIP</label>
                    <input type="text" name="nip" class="form-control" value="{{ $generatedNip }}" readonly>
                </div>

                <button class="btn btn-primary">
                    {{ $teacher ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('teachers.index') }}" class="btn btn-secondary">
                    Batal
                </a>
            </form>
        </div>
    </div>
@endsection