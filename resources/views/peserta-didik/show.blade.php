@extends('layouts.app')
@section('title', 'Detail Peserta Didik')

@section('content')

    <div class="row">
        <!-- FOTO -->
        <div class="col-md-4">
            <div class="card">
                <div class="body text-center">

                    @if($pesertaDidik->foto_wajah)
                        <img src="{{ route('peserta-didik.photo', $pesertaDidik->id) }}"
                            onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($pesertaDidik->nama_lengkap ?? $pesertaDidik->user->name) }}&background=00bcd4&color=fff&bold=true&size=512';"
                            class="shadow mb-3"
                            style="max-width: 200px; width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 12px; display: block; margin: 0 auto;">

                    @else
                        <img src="{{ optional($pesertaDidik->user)->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($pesertaDidik->nama_lengkap) . '&background=00bcd4&color=fff&bold=true&size=512' }}"
                            class="shadow mb-3"
                            style="max-width: 200px; width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 12px; display: block; margin: 0 auto;">
                    @endif

                    <span class="badge badge-info">
                        {{ $pesertaDidik->user->name }}
                    </span>

                </div>
            </div>
        </div>

        <!-- DETAIL -->
        <div class="col-md-8">
            <div class="card">
                <div class="header">
                    <h2>Informasi Peserta Didik</h2>
                </div>

                <div class="body">
                    <table class="table table-striped">
                        <tr>
                            <th width="200">Nama Lengkap</th>
                            <td>{{ $pesertaDidik->user->name }}</td>
                        </tr>
                        <tr>
                            <th>No Induk</th>
                            <td>{{ $pesertaDidik->no_induk }}</td>
                        </tr>
                        <tr>
                            <th>NISN</th>
                            <td>{{ $pesertaDidik->nisn }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $pesertaDidik->user->email }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Kelamin</th>
                            <td>{{ $pesertaDidik->jenis_kelamin }}</td>
                        </tr>
                        <tr>
                            <th>Tempat Lahir</th>
                            <td>{{ $pesertaDidik->tempat_lahir }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Lahir</th>
                            <td>{{ \Carbon\Carbon::parse($pesertaDidik->tanggal_lahir)->translatedFormat('j F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Face Embedding</th>
                            <td>
                                @if($pesertaDidik->face_embedding)
                                    <span class="badge badge-success">
                                        Tersimpan
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        Belum ada
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td>{{ $pesertaDidik->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>

                        <a href="{{ route('peserta-didik.edit', $pesertaDidik->id) }}" class="btn btn-warning">
                            <i class="fa fa-edit"></i> Edit
                        </a>

                        @if(!$pesertaDidik->face_embedding && (auth()->user()->role === 'admin' || auth()->id() === $pesertaDidik->user_id))
                            <a href="{{ route('face.enroll') }}" class="btn btn-primary">
                                <i class="fa fa-camera"></i> Registrasi Wajah
                            </a>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection