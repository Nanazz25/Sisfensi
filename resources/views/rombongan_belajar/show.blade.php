@extends('layouts.app')

@section('title', 'Detail Rombongan Belajar')

@section('content')
<div class="row">

    {{-- =======================
        DETAIL ROMBEL
    ======================== --}}
    <div class="col-md-4">
        <div class="card">
            <div class="header pb-0">
                <h2 class="mb-0">Detail Rombel</h2>
            </div>

            <div class="body pt-2">
                <table class="table table-sm mb-2">
                    <tr>
                        <th>Rombel</th>
                        <td>{{ $rombel->nama_rombel }}</td>
                    </tr>
                    <tr>
                        <th>Tahun Ajar</th>
                        <td>
                            {{ $rombel->tahunAjar->nama }}
                            <span class="badge badge-info">
                                {{ ucfirst($rombel->tahunAjar->semester) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Wali</th>
                        <td>{{ $rombel->waliKelas->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jumlah Siswa</th>
                        <td>
                            <span class="badge badge-success">
                                {{ $anggota->count() }} orang
                            </span>
                        </td>
                    </tr>
                </table>

                <a href="{{ route('rombongan-belajar.index') }}"
                   class="btn btn-secondary btn-sm btn-block">
                    ← Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">

        <div class="card mb-3">
            <div class="header pb-0">
                <h2 class="mb-0">Tambah Anggota</h2>
            </div>

            <div class="body pt-2">
                @error('peserta_didik_id')
                    <div class="alert alert-danger py-1 mb-2">
                        {{ $message }}
                    </div>
                @enderror

                <form action="{{ route('rombels.anggota.store', $rombel->id) }}"
                      method="POST"
                      class="form-inline">
                    @csrf

                    <select name="peserta_didik_id"
                            class="form-control mr-2"
                            required>
                        <option value="">-- pilih siswa --</option>
                        @foreach ($siswaAvailable as $siswa)
                            <option value="{{ $siswa->id }}">
                                {{ $siswa->user->name }} — {{ $siswa->nis }}
                            </option>
                        @endforeach
                    </select>

                    <button class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambah
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="header pb-0">
                <h2 class="mb-0">Daftar Anggota</h2>
            </div>

            <div class="body pt-2">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Nama</th>
                                <th width="120">NIS</th>
                                <th width="90" class="text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($anggota as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $item->pesertaDidik->user->name }}
                                    </td>
                                    <td>
                                        {{ $item->pesertaDidik->no_induk }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('peserta-didik.show', $item->pesertaDidik->id) }}"
                                           class="btn btn-info btn-sm"
                                           title="Detail">
                                            <i class="fa fa-eye"></i>
                                        </a>

                                        <button type="button"
                                                class="btn btn-danger btn-sm btn-delete"
                                                data-name="{{ $item->pesertaDidik->user->name }}"
                                                data-action="{{ route('rombels.anggota.destroy', [
                                                    'rombel' => $rombel->id,
                                                    'anggotaRombel' => $item->id
                                                ]) }}"
                                                data-toggle="modal"
                                                data-target="#deleteModal"
                                                title="Hapus">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="text-center text-muted">
                                        Belum ada anggota
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

<x-modal-delete
    title="Hapus Anggota"
    message="Yakin ingin menghapus anggota berikut dari rombel?"
/>
