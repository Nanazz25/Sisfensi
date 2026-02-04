@extends('layouts.app')
@section('title', $siswa ? 'Edit Peserta Didik' : 'Tambah Peserta Didik')

@section('content')

    <div class="card">
        <div class="header">
            <h2>{{ $siswa ? 'Edit Peserta Didik' : 'Tambah Peserta Didik' }}</h2>
        </div>

        <div class="body">
            <form method="POST" action="{{ $siswa
        ? route('peserta-didik.update', $siswa->id)
        : route('peserta-didik.store') }}">

                @csrf
                @if($siswa)
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control"
                        value="{{ old('nama_lengkap', $siswa->nama_lengkap ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>No Induk</label>
                    <input type="text" name="no_induk" class="form-control"
                        value="{{ old('no_induk', $siswa->no_induk ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>NISN</label>
                    <input type="text" name="nisn" class="form-control" value="{{ old('nisn', $siswa->nisn ?? '') }}"
                        required>
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control" required>
                        <option value="">-- pilih --</option>
                        <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>L
                        </option>
                        <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>P
                        </option>
                    </select>
                </div>

                <button class="btn btn-primary">
                    {{ $siswa ? 'Update' : 'Simpan' }}
                </button>

                <a href="{{ route('peserta-didik.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </form>
        </div>
    </div>

@endsection