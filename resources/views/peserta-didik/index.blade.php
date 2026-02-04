@extends('layouts.app')
@section('title', 'Peserta Didik')

@section('content')

    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Peserta Didik</h2>

            <a href="{{ route('peserta-didik.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Siswa
            </a>
        </div>

        <div class="body">

            <form method="GET" id="filterForm" class="row mb-3 align-items-center g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                            placeholder="Cari nama / NIS">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="dropdown">
                    <a href="javascript:void(0);" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-filter"></i> Urutkan
                    </a>

                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item sort-option" data-value="desc" href="javascript:void(0);">
                                Terbaru
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item sort-option" data-value="asc" href="javascript:void(0);">
                                Terlama
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="col-auto">
                    <a href="{{ route('peserta-didik.index') }}" class="btn btn-outline-danger">
                        <i class="fa fa-refresh"></i>
                    </a>
                </div>

                <input type="hidden" name="sort" id="sortInput" value="{{ request('sort', 'desc') }}">
            </form>

            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>NIS</th>
                            <th>Dibuat</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa as $item)
                            <tr>
                                <td>{{ $loop->iteration + $siswa->firstItem() - 1 }}</td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->no_induk }}</td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('peserta-didik.show', $item->id) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>

                                    <a href="{{ route('peserta-didik.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger btn-delete" data-name="{{ $item->user->name }}"
                                        data-action="{{ route('peserta-didik.destroy', $item->id) }}" data-toggle="modal"
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

            <div class="mt-3">
                {{ $siswa->links() }}
            </div>
        </div>
    </div>

@endsection

<x-modal-delete title="Hapus Siswa" message="Yakin hapus peserta didik berikut?" />

@section('afterAppScripts')
    <script>
        document.querySelectorAll('.sort-option').forEach(item => {
            item.addEventListener('click', function () {
                document.getElementById('sortInput').value = this.dataset.value;
                document.getElementById('filterForm').submit();
            });
        });
    </script>
@endsection