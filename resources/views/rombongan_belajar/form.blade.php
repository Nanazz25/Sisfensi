@extends('layouts.app')
@section('title', isset($rombel) ? 'Edit Rombel' : 'Tambah Rombel')

@section('content')
<div class="card">
    <div class="header">
        <h2>{{ isset($rombel) ? 'Edit Rombel' : 'Tambah Rombel' }}</h2>
    </div>

    <div class="body">
        <form method="POST"
            action="{{ isset($rombel)
                ? route('rombongan-belajar.update', $rombel->id)
                : route('rombongan-belajar.store') }}">
            @csrf
            @if(isset($rombel)) @method('PUT') @endif

            <div class="form-group">
                <label>Jurusan</label>
                <select name="jurusan_id" class="form-control" required>
                    <option value="">-- pilih --</option>
                    @foreach($jurusans as $jurusan)
                        <option value="{{ $jurusan->id }}"
                            {{ old('jurusan_id', $rombel->jurusan_id ?? '') == $jurusan->id ? 'selected' : '' }}>
                            {{ $jurusan->nama_jurusan }} ({{ $jurusan->kode_jurusan }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Nama Rombel</label>
                <input type="text" name="nama_rombel" class="form-control"
                    value="{{ old('nama_rombel', $rombel->nama_rombel ?? '') }}" required>
            </div>

            <div class="form-group">
                <label>Tahun Ajar</label>
                <select name="tahun_ajar_id" class="form-control" required>
                    <option value="">-- pilih --</option>
                    @foreach($tahunAjars as $ta)
                        <option value="{{ $ta->id }}"
                            {{ old('tahun_ajar_id', $rombel->tahun_ajar_id ?? '') == $ta->id ? 'selected' : '' }}>
                            {{ $ta->nama }} ({{ ucfirst($ta->semester) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Wali Kelas</label>
                <select name="wali_kelas_id" class="form-control" required>
                    <option value="">-- pilih --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}"
                            {{ old('wali_kelas_id', $rombel->wali_kelas_id ?? '') == $t->id ? 'selected' : '' }}>
                            {{ $t->user->name }} ({{ $t->nip }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="btn btn-primary">
                {{ isset($rombel) ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('rombongan-belajar.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection
