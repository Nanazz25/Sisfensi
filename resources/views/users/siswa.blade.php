@extends('layouts.app')
@section('title', 'User siswa')

@section('content')

    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>User siswa</h2>

            <a href="{{ route('users.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah User
            </a>
        </div>

        <div class="body">

            <form method="GET" action="{{ route('users.siswa') }}" id="filterForm"
                class="ajax-form compact-form row mb-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i
                                    class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control border-left-0"
                            placeholder="Cari nama / email...">
                    </div>
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

                <div class="col-auto">
                    <a href="{{ route('users.siswa') }}" class="btn btn-link text-danger p-0">
                        <i class="fa fa-refresh"></i> Reset
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
                            <th>Email</th>
                            <th>Dibuat</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $item)
                            <tr>
                                <td>{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->email }}</td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('users.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger btn-delete" data-name="{{ $item->name }}"
                                        data-action="{{ route('users.destroy', $item->id) }}" data-toggle="modal"
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
                {{ $users->links() }}
            </div>

        </div>
    </div>

@endsection

<x-modal-delete title="Hapus User" message="Yakin hapus user berikut?" />

@section('afterAppScripts')
    <script>
        document.querySelectorAll('.sort-option').forEach(item => {
            item.addEventListener('click', function () {
                const form = document.getElementById('filterForm');
                document.getElementById('sortInput').value = this.dataset.value;
                form.dispatchEvent(new Event('submit', {
                    cancelable: true
                }));
            });
        });
    </script>
@endsection