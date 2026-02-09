@extends('layouts.app')

@section('title', 'Pengaturan Sistem Sekolah')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Pengaturan Sistem Sekolah</h2>
                    <small>Atur jam operasional dan pengelolaan Face Recognition</small>
                </div>

                <div class="body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('school-settings.update') }}" method="POST">
                        @csrf

                        <h5 class="mb-3">Pengaturan Jam Sekolah</h5>
                        <div class="row">
                            @foreach($settings->filter(fn($s) => str_contains($s->key, 'jam')) as $setting)
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            {{ str_replace('_', ' ', strtoupper($setting->key)) }}
                                        </label>
                                        <input type="time" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                            class="form-control" required>
                                        <small class="text-muted">
                                            {{ $setting->description }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr>

                        <h5 class="mb-3">Face Recognition</h5>
                        <div class="row">
                            @foreach($settings->filter(fn($s) => !str_contains($s->key, 'jam')) as $setting)
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            {{ str_replace('_', ' ', strtoupper($setting->key)) }}
                                        </label>
                                        <input type="number" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                            class="form-control" min="1" max="90" required>
                                        <small class="text-muted">
                                            {{ $setting->description }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary btn-round mt-3">
                            <i class="fa fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection