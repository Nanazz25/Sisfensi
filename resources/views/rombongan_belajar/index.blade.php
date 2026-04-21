@extends('layouts.app')
@section('title', 'Rombongan Belajar')

@section('content')

    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Rombongan Belajar</h2>
            <a href="{{ route('rombongan-belajar.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Rombel
            </a>
        </div>

        <div class="body">
            <form method="GET" action="{{ route('rombongan-belajar.index') }}" id="filterForm"
                class="ajax-form compact-form row mb-3 align-items-end">
                <div class="col-12 col-md-3 mb-3 mb-md-0">
                    <label class="font-weight-600 small mb-1">Cari Nama</label>
                    <div class="input-group shadow-xs">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i
                                    class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control border-left-0"
                            placeholder="Contoh: X RPL 1">
                    </div>
                </div>

                <div class="col-6 col-md-2 mb-3 mb-md-0">
                    <label class="font-weight-600 small mb-1">Tahun Ajar</label>
                    <select name="tahun_ajar_id" class="form-control">
                        <option value="">-- Semua --</option>
                        @foreach($tahunAjars as $ta)
                            <option value="{{ $ta->id }}" {{ request('tahun_ajar_id', optional($tahunAjarAktif)->id) == $ta->id ? 'selected' : '' }}>
                                {{ $ta->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2 mb-3 mb-md-0">
                    <label class="font-weight-600 small mb-1">Jurusan</label>
                    <select name="jurusan_id" class="form-control">
                        <option value="">-- Semua --</option>
                        @foreach($jurusans as $jurusan)
                            <option value="{{ $jurusan->id }}" {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                {{ $jurusan->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 mb-3 mb-md-0">
                    <label class="font-weight-600 small mb-1">Angkatan</label>
                    <select name="angkatan" class="form-control">
                        <option value="">-- Semua --</option>
                        <option value="10" {{ request('angkatan') == '10' ? 'selected' : '' }}>Kelas 10 (X)</option>
                        <option value="11" {{ request('angkatan') == '11' ? 'selected' : '' }}>Kelas 11 (XI)</option>
                        <option value="12" {{ request('angkatan') == '12' ? 'selected' : '' }}>Kelas 12 (XII)</option>
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary d-none">Filter</button>
                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-sort-amount-desc"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item sort-option" data-value="desc"
                                    href="javascript:void(0);">Terbaru</a></li>
                            <li><a class="dropdown-item sort-option" data-value="asc" href="javascript:void(0);">Terlama</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <input type="hidden" name="sort" id="sortInput" value="{{ request('sort', 'desc') }}">

                <div class="col-auto ml-auto">
                    <a href="{{ route('laporan.absensi.kelas') }}" class="btn btn-info shadow-sm text-white">
                        <i class="fa fa-print mr-1"></i> Laporan
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Rombel</th>
                            <th>Wali Kelas</th>
                            <th>Tahun Ajar</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rombels as $item)
                            <tr>
                                <td>{{ $loop->iteration + $rombels->firstItem() - 1 }}</td>
                                <td>{{ $item->nama_rombel }}</td>
                                <td>{{ $item->waliKelas->user->name ?? '-' }}</td>
                                <td>{{ $item->tahunAjar->nama }} ({{ ucfirst($item->tahunAjar->semester) }})</td>
                                <td>
                                    <a href="{{ route('rombongan-belajar.show', $item->id) }}" class="btn btn-sm btn-primary"
                                        title="Kelola Anggota">
                                        <i class="fa fa-users"></i>
                                    </a>
                                    <a href="{{ route('rombongan-belajar.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger btn-delete" data-name="{{ $item->nama_rombel }}"
                                        data-action="{{ route('rombongan-belajar.destroy', $item->id) }}" data-toggle="modal"
                                        data-target="#deleteModal">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Data kosong</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-responsive mt-3">{{ $rombels->links() }}</div>
        </div>
    </div>

@endsection

<x-modal-delete title="Hapus Rombel" message="Yakin hapus rombel berikut?" />

@section('afterAppScripts')
    <script>
        document.querySelectorAll('#filterForm select').forEach(select => {
            select.addEventListener('change', function () {
                this.form.submit();
            });
        });

        document.querySelectorAll('.sort-option').forEach(item => {
            item.addEventListener('click', function () {
                const form = document.getElementById('filterForm');
                document.getElementById('sortInput').value = this.dataset.value;
                form.submit();
            });
        });
    </script>
@endsection