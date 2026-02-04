@extends('layouts.app')
@section('title', isset($subject) ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran')

@section('content')
    <div class="card">
        <div class="header">
            <h2>{{ isset($subject) ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran' }}</h2>
        </div>

        <div class="body">
            <form method="POST" action="{{ isset($subject)
        ? route('subjects.update', $subject->id)
        : route('subjects.store') }}">
                @csrf
                @if(isset($subject)) @method('PUT') @endif

                <div class="form-group">
                    <label>Nama Mata Pelajaran</label>
                    <input type="text" name="nama_mapel" class="form-control"
                        value="{{ old('nama_mapel', $subject->nama_mapel ?? '') }}" required>
                </div>

                <button class="btn btn-primary">
                    {{ isset($subject) ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('subjects.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </form>
        </div>
    </div>
@endsection