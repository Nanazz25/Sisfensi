@extends('layouts.app')
@section('title', 'Data Guru')

@section('content')

    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Data Guru</h2>

            <a href="{{ route('teachers.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Guru
            </a>
        </div>

        <div class="body">

            {{-- FILTER --}}
            <form method="GET" id="filterForm" class="row mb-3 align-items-center g-2">

                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                            placeholder="Cari nama / NIP">
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
                    <a href="{{ route('teachers.index') }}" class="btn btn-outline-danger">
                        <i class="fa fa-refresh"></i>
                    </a>
                </div>

                <input type="hidden" name="sort" id="sortInput" value="{{ request('sort', 'desc') }}">
            </form>

            {{-- TABLE --}}
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>Dibuat</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($teachers as $item)
                            <tr>
                                <td>{{ $loop->iteration + $teachers->firstItem() - 1 }}</td>
                                <td>{{ $item->nama_lengkap }}</td>
                                <td>
                                    @if($item->nip)
                                        {{ $item->nip }}
                                    @elseif($item->nuptk)
                                        <small class="text-muted">NUPTK:</small> {{ $item->nuptk }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('teachers.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger btn-delete" data-name="{{ $item->user->name }}"
                                        data-action="{{ route('teachers.destroy', $item->id) }}" data-toggle="modal"
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
                {{ $teachers->links() }}
            </div>

        </div>
    </div>

@endsection

<x-modal-delete title="Hapus Guru" message="Yakin hapus guru berikut?" />

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