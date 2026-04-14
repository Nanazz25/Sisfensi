@extends('layouts.app')

@section('title', 'My Profile')

@section('afterAppStyles')
    @vite('resources/css/profile.css')
@endsection

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card profile-card border-0 shadow-sm overflow-hidden">
                {{-- Header/Banner --}}
                <div class="profile-banner bg-gradient-primary" style="height: 120px;"></div>

                <div class="body pt-0">
                    <div class="row mt-n5 px-4 mb-4 profile-header-row text-center text-md-left">
                        <div class="col-12 col-md-auto profile-image-container d-flex justify-content-center justify-content-md-start">
                            <div class="profile-image border-5 border-white rounded-circle shadow d-flex align-items-center justify-content-center"
                                style="width: 130px; height: 130px; overflow: hidden; margin-top: -65px; border: 5px solid #fff; background: #fff;">
                                <img src="{{ $user->avatar_url }}" style="width: 100%; height: 100%; object-fit: cover;"
                                    alt="Profile Picture">
                            </div>
                        </div>
                        <div class="col-12 col-md profile-name-section mt-3 mt-md-0 pb-1 d-flex flex-column justify-content-between desktop-profile-text">
                            <div class="name-email-container">
                                <h3 class="font-weight-bold mb-1">{{ $user->name }}</h3>
                                <p class="text-muted mb-0"><i class="fa fa-envelope-o mr-1"></i> {{ $user->email }}</p>
                            </div>
                            <div class="mt-3 mt-md-2 role-badges text-center text-md-right mt-md-auto">
                                <span
                                    class="badge badge-pill badge-primary px-3 py-2 text-uppercase font-12">{{ $user->role }}</span>
                                @if($user->role === 'siswa' && $rombel)
                                    <span
                                        class="badge badge-pill badge-info px-3 py-2 font-12 ml-1 ml-md-1">{{ $rombel->rombonganBelajar->nama_rombel }}</span>
                                @elseif($user->role === 'guru' && $rombel)
                                    <span class="badge badge-pill badge-success px-3 py-2 font-12 ml-1 ml-md-1">Wali Kelas
                                        {{ $rombel->nama_rombel }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr class="mt-0">

                    <div class="row px-md-3">
                        {{-- Left Column: Account Info --}}
                        <div class="col-lg-4 col-md-5 border-right info-sidebar">
                            <h6 class="font-weight-bold mb-3"><i class="fa fa-id-card-o text-primary mr-2"></i> Informasi
                                Akun</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <small class="text-muted d-block uppercase font-10">ID Akun (Email)</small>
                                    <span class="text-dark font-weight-600">{{ $user->email }}</span>
                                </li>
                                <li class="mb-3">
                                    <small class="text-muted d-block uppercase font-10">Status Akun</small>
                                    <span class="text-success font-weight-600"><i class="fa fa-circle mr-1"
                                            style="font-size: 8px;"></i> Aktif</span>
                                </li>
                                <li class="mb-3">
                                    <small class="text-muted d-block uppercase font-10">Terdaftar Sejak</small>
                                    <span
                                        class="text-dark font-weight-600">{{ $user->created_at->translatedFormat('d F Y') }}</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Right Column: Personal & Academic Detail --}}
                        <div class="col-lg-8 col-md-7 info-content">
                            <h6 class="font-weight-bold mb-3"><i class="fa fa-user-circle-o text-primary mr-2"></i> Detail
                                Pribadi</h6>

                            <div class="row">
                                @if($user->role === 'siswa' && $user->pesertaDidik)
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block uppercase font-10">NISN</small>
                                        <span class="font-weight-bold text-dark">{{ $user->pesertaDidik->nisn ?? '-' }}</span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block uppercase font-10">No. Induk (NIS)</small>
                                        <span
                                            class="font-weight-bold text-dark">{{ $user->pesertaDidik->no_induk ?? '-' }}</span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block uppercase font-10">Jenis Kelamin</small>
                                        <span
                                            class="text-dark font-weight-600">{{ $user->pesertaDidik->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block uppercase font-10">Lahir</small>
                                        <span class="text-dark font-weight-600">{{ $user->pesertaDidik->tempat_lahir ?? '-' }},
                                            {{ $user->pesertaDidik->tanggal_lahir ?? '-' }}</span>
                                    </div>
                                @elseif($user->role === 'guru' && $user->teacher)
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block uppercase font-10">NIP</small>
                                        <span class="font-weight-bold text-dark">{{ $user->teacher->nip ?? '-' }}</span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block uppercase font-10">NUPTK</small>
                                        <span class="font-weight-bold text-dark">{{ $user->teacher->nuptk ?? '-' }}</span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block uppercase font-10">Jenis Kelamin</small>
                                        <span
                                            class="text-dark font-weight-600">{{ $user->teacher->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block uppercase font-10">Lahir</small>
                                        <span class="text-dark font-weight-600">{{ $user->teacher->tempat_lahir ?? '-' }},
                                            {{ $user->teacher->tanggal_lahir ?? '-' }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                <button onclick="history.back()" class="btn btn-outline-secondary px-4 shadow-sm">
                                    <i class="fa fa-arrow-left mr-1"></i> Kembali
                                </button>
                                <a href="{{ route('profile.password.edit') }}" class="btn btn-primary px-4 shadow-sm">
                                    <i class="fa fa-lock mr-1"></i> Ubah Password
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection