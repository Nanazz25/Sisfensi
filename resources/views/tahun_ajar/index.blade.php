@extends('layouts.app')
@section('title', 'Tahun Ajar')

@section('content')

    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Tahun Ajar</h2>

            <a href="{{ route('tahun-ajar.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Tahun Ajar
            </a>
        </div>

        <div class="body">

            <form method="GET" id="filterForm" class="row mb-3 align-items-center g-2">

                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" name="q" id="searchInput" value="{{ request('q') }}" class="form-control"
                            placeholder="Cari tahun ajar">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit">
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
                                <i class="fa fa-clock-o"></i> Terbaru
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item sort-option" data-value="asc" href="javascript:void(0);">
                                <i class="fa fa-history"></i> Terlama
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="col-auto">
                    <a href="{{ route('tahun-ajar.index') }}" class="btn btn-sm btn-outline-danger btn-block rounded-pill shadow-xs d-flex align-items-center justify-content-center" style="height: 38px; width: 45px;">
                        <i class="fa fa-undo"></i>
                    </a>
                </div>

                <input type="hidden" name="sort" id="sortInput" value="{{ request('sort', 'desc') }}">
            </form>

            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tahun Ajar</th>
                            <th>Semester</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($tahunAjars as $item)
                            <tr>
                                <td>{{ $loop->iteration + $tahunAjars->firstItem() - 1 }}</td>
                                <td>{{ $item->nama }}</td>
                                <td class="text-capitalize">{{ $item->semester }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}
                                    -
                                    {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}
                                </td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('tahun-ajar.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger btn-delete" data-name="{{ $item->nama }}"
                                        data-action="{{ route('tahun-ajar.destroy', $item->id) }}" data-toggle="modal"
                                        data-target="#deleteModal">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Data kosong</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $tahunAjars->links() }}
            </div>

        </div>
    </div>

@endsection

<x-modal-delete title="Hapus Tahun Ajar" message="Yakin hapus tahun ajar berikut?" />

@section('afterAppScripts')
    <script>
        document.querySelectorAll('.sort-option').forEach(item => {
            item.addEventListener('click', function () {
                document.getElementById('sortInput').value = this.dataset.value;
                document.getElementById('filterForm').submit();
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