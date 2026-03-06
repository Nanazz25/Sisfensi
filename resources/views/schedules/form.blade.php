@extends('layouts.app')
@section('title', isset($schedule) ? 'Edit Jadwal' : 'Tambah Jadwal')

@section('content')
    <div class="card">
        <div class="header">
            <h2>{{ isset($schedule) ? 'Edit Jadwal' : 'Tambah Jadwal' }}</h2>
        </div>

        <div class="body">
            <form method="POST" action="{{ isset($schedule)
        ? route('schedules.update', $schedule->id)
        : route('schedules.store') }}">
                @csrf
                @if(isset($schedule)) @method('PUT') @endif

                <div class="form-group">
                    <label>Rombongan Belajar</label>
                    <select name="rombongan_belajar_id" class="form-control" required>
                        <option value="">-- pilih --</option>
                        @foreach($rombels as $r)
                            @php
                                $selected = old('rombongan_belajar_id', $schedule->rombongan_belajar_id ?? request('rombel_id')) == $r->id;
                            @endphp
                            <option value="{{ $r->id }}" {{ $selected ? 'selected' : '' }}>
                                {{ $r->nama_rombel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Mata Pelajaran</label>
                    <select name="subject_id" class="form-control" required>
                        <option value="">-- pilih --</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ old('subject_id', $schedule->subject_id ?? '') == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Guru</label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">-- pilih --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ old('teacher_id', $schedule->teacher_id ?? '') == $t->id ? 'selected' : '' }}>
                                {{ $t->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Hari</label>
                    <select name="hari" class="form-control" required>
                        @foreach(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $hari)
                            <option value="{{ $hari }}" {{ old('hari', $schedule->hari ?? '') == $hari ? 'selected' : '' }}>
                                {{ ucfirst($hari) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Jam Mulai</label>
                    <input type="time" name="jam_mulai" class="form-control"
                        value="{{ old('jam_mulai', $schedule->jam_mulai ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>Jam Selesai</label>
                    <input type="time" name="jam_selesai" class="form-control"
                        value="{{ old('jam_selesai', $schedule->jam_selesai ?? '') }}" required>
                </div>

                <button class="btn btn-primary">
                    {{ isset($schedule) ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('schedules.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
@endsection