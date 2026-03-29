@extends('layouts.app')

@section('title', isset($assessmentCategory) ? 'Edit Kategori Penilaian' : 'Tambah Kategori Penilaian')

@section('afterAppStyles')
<style>
    /* Modern Toggle Switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 22px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 14px;
        width: 14px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #00bcd4;
    }
    input:checked + .slider:before {
        transform: translateX(24px);
    }
</style>
@endsection

@section('content')
    <div class="card">
        <div class="header">
            <h2>{{ isset($assessmentCategory) ? 'Edit Kategori Penilaian' : 'Tambah Kategori Penilaian' }}</h2>
        </div>
        <div class="body">
            <form action="{{ isset($assessmentCategory)
                ? route('assessment_category.update', $assessmentCategory->id)
                : route('assessment_category.store') }}" method="POST">
                @csrf
                @if(isset($assessmentCategory))
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" class="form-control"
                        value="{{ old('name', $assessmentCategory->name ?? '') }}" placeholder="Masukkan nama kategori" required>
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <input type="text" name="description" class="form-control"
                        value="{{ old('description', $assessmentCategory->description ?? '') }}" placeholder="Masukkan deskripsi" required>
                </div>

                <div class="form-group">
                    <label>Tipe</label>
                    <select name="type" class="form-control" required>
                        <option value="">-- pilih --</option>
                        <option value="siswa"
                            {{ old('type', $assessmentCategory->type ?? '') == 'siswa' ? 'selected' : '' }}>
                            Siswa
                        </option>
                        <option value="guru"
                            {{ old('type', $assessmentCategory->type ?? '') == 'guru' ? 'selected' : '' }}>
                            Guru
                        </option>
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label class="d-block">Status Aktif</label>
                    <div class="d-flex align-items-center">
                        <label class="switch mb-0">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $assessmentCategory->is_active ?? true) ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                        <span class="ml-2">Aktif</span>
                    </div>
                </div>

                <button class="btn btn-primary">
                    {{ isset($assessmentCategory) ? 'Update' : 'Simpan' }}
                </button>

                <a href="{{ route('assessment_category.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </form>
        </div>
    </div>
@endsection