@extends('layouts.app')
@section('title', 'Detail Peserta Didik')

@section('content')

    <div class="row">
        <!-- FOTO -->
        <div class="col-md-4">
            <div class="card">
                <div class="body text-center">

                    @if($pesertaDidik->foto_wajah)
                        <img src="{{ asset('storage/' . $pesertaDidik->foto_wajah) }}" class="img-fluid rounded mb-3"
                            style="max-height:300px; object-fit:cover;">
                    @else
                        <img src="{{ asset('assets/images/no-image.png') }}" class="img-fluid rounded mb-3"
                            style="max-height:300px;">
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
                        <a href="{{ route('peserta-didik.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>

                        <a href="{{ route('peserta-didik.edit', $pesertaDidik->id) }}" class="btn btn-warning">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection