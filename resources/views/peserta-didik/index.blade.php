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

            <form method="GET" action="{{ route('peserta-didik.index') }}" id="filterForm"
                class="ajax-form compact-form row mb-3 align-items-center">
                <div class="col-12 col-md-5 mb-3 mb-md-0">
                    <div class="input-group shadow-xs">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i
                                    class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="q" id="searchInput" value="{{ request('q') }}" class="form-control border-left-0"
                            placeholder="Cari nama / NIS...">
                    </div>
                </div>

                <div class="col-6 col-md-auto">
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

                <div class="col-6 col-md-auto">
                    <a href="{{ route('peserta-didik.index') }}" class="btn btn-sm btn-outline-danger btn-block rounded-pill shadow-xs d-flex align-items-center justify-content-center" style="height: 38px;">
                        <i class="fa fa-undo mr-1"></i> Reset
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

            <div class="pagination-responsive mt-3">
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
                const form = document.getElementById('filterForm');
                document.getElementById('sortInput').value = this.dataset.value;
                form.submit();
            });
        });

        // --- Auto Search ---
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        if (searchInput && filterForm) {
            let timeout = null;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => filterForm.submit(), 500);
            });
            if (searchInput.value) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }
        }
    </script>
@endsection