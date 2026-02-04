@extends('layouts.app')

@section('title', 'Detail Rombongan Belajar')

@section('content')
<div class="row">

    <div class="col-md-4">
        <div class="card">
            <div class="header">
                <h2>Detail Rombel</h2>
            </div>

            <div class="body">
                <table class="table table-sm">
                    <tr>
                        <th>Nama Rombel</th>
                        <td>{{ $rombel->nama_rombel }}</td>
                    </tr>
                    <tr>
                        <th>Tahun Ajar</th>
                        <td>
                            {{ $rombel->tahunAjar->nama }}
                            ({{ ucfirst($rombel->tahunAjar->semester) }})
                        </td>
                    </tr>
                    <tr>
                        <th>Wali Kelas</th>
                        <td>{{ $rombel->waliKelas->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jumlah Siswa</th>
                        <td>{{ $anggota->count() }}</td>
                    </tr>
                </table>

                <a href="{{ route('rombongan-belajar.index') }}"
                   class="btn btn-secondary btn-sm">
                    ← Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">

        <div class="card mb-3">
            <div class="header">
                <h2>Tambah Anggota Rombel</h2>
            </div>

            <div class="body">
                @error('peserta_didik_id')
                    <div class="alert alert-danger">
                        {{ $message }}
                    </div>
                @enderror

                <form action="{{ route('rombels.anggota.store', $rombel->id) }}"
                      method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Siswa</label>
                        <select name="peserta_didik_id"
                                class="form-control"
                                required>
                            <option value="">-- pilih siswa --</option>

                            @foreach ($siswaAvailable as $siswa)
                                <option value="{{ $siswa->id }}">
                                    {{ $siswa->user->name }} - {{ $siswa->nis }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambahkan
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="header">
                <h2>Daftar Anggota</h2>
            </div>

            <div class="body">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>NIS</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($anggota as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->pesertaDidik->user->name }}</td>
                                    <td>{{ $item->pesertaDidik->no_induk }}</td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm btn-delete"
                                            data-name="{{ $item->pesertaDidik->user->name }}"
                                            data-action="{{ route('rombels.anggota.destroy', [
                                                'rombel' => $rombel->id,
                                                'anggotaRombel' => $item->id
                                            ]) }}"
                                            data-toggle="modal"
                                            data-target="#deleteModal">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
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
