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
                class="ajax-form compact-form row mb-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i
                                    class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control border-left-0"
                            placeholder="Cari nama rombel...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="tahun_ajar_id" class="form-control">
                        <option value="">-- Tahun Ajar Aktif --</option>
                        @foreach($tahunAjars as $ta)
                            <option value="{{ $ta->id }}" {{ request('tahun_ajar_id', optional($tahunAjarAktif)->id) == $ta->id ? 'selected' : '' }}>
                                {{ $ta->nama }} ({{ ucfirst($ta->semester) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-sort-amount-desc mr-1"></i> Urutkan
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
                        <i class="fa fa-print"></i> Laporan
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

            <div class="mt-3">{{ $rombels->links() }}</div>
        </div>
    </div>

@endsection

<x-modal-delete title="Hapus Rombel" message="Yakin hapus rombel berikut?" />

@section('afterAppScripts')
    <script>
        document.querySelectorAll('.sort-option').forEach(item => {
            item.addEventListener('click', function () {
                const form = document.getElementById('filterForm');
                document.getElementById('sortInput').value = this.dataset.value;
                form.dispatchEvent(new Event('submit', { cancelable: true }));
            });
        });
    </script>
@endsection