@extends('layouts.app')
@section('title', 'User Admin')

@section('content')

    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>User Admin</h2>

            <a href="{{ route('users.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah User
            </a>
        </div>

        <div class="body">

            <form method="GET" action="{{ route('users.admin') }}" id="filterForm"
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
                    <a href="{{ route('users.admin') }}" class="btn btn-link text-danger p-0">
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
                            <th>Password</th>
                            <th>Dibuat</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $item)
                            <tr>
                                <td>{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->email }}</td>
                                <td>
                                    @if($item->password_changed)
                                        <span class="badge badge-success" title="User sudah merubah password"><i
                                                class="fa fa-check-circle mr-1"></i> Sudah Dirubah</span>
                                    @else
                                        <code>{{ $item->initial_password ?? '-' }}</code>
                                    @endif
                                </td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('users.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <form action="{{ route('users.reset-password', $item->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Reset password untuk {{ $item->name }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Reset Password">
                                            <i class="fa fa-refresh"></i>
                                        </button>
                                    </form>

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
                form.dispatchEvent(new Event('submit', { cancelable: true }));
            });
        });
    </script>
@endsection