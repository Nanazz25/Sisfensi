@extends('layouts.app')
@section('title', isset($jurusan) ? 'Edit Jurusan' : 'Tambah Jurusan')

@section('content')
    <div class="card">
        <div class="header">
            <h2>{{ isset($jurusan) ? 'Edit Jurusan' : 'Tambah Jurusan' }}</h2>
        </div>

        <div class="body">
            <form method="POST" action="{{ isset($jurusan)
        ? route('jurusan.update', $jurusan->id)
        : route('jurusan.store') }}">
                @csrf
                @if(isset($jurusan)) @method('PUT') @endif

                <div class="form-group">
                    <label>Kode Jurusan</label>
                    <input type="text" name="kode_jurusan" class="form-control"
                        value="{{ old('kode_jurusan', $jurusan->kode_jurusan ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>Nama Jurusan</label>
                    <input type="text" name="nama_jurusan" class="form-control"
                        value="{{ old('nama_jurusan', $jurusan->nama_jurusan ?? '') }}" required>
                </div>

                <button class="btn btn-primary">
                    {{ isset($jurusan) ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('jurusan.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </form>
        </div>
    </div>
@endsection