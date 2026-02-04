@extends('layouts.app')
@section('title', 'Jurusan')

@section('content')

    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Jurusan</h2>
            <a href="{{ route('jurusan.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Jurusan
            </a>
        </div>

        <div class="body">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode Jurusan</th>
                            <th>Nama Jurusan</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jurusans as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->kode_jurusan }}</td>
                                <td>{{ $item->nama_jurusan }}</td>
                                <td>
                                    <a href="{{ route('jurusan.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger btn-delete" data-name="{{ $item->nama_jurusan }}"
                                        data-action="{{ route('jurusan.destroy', $item->id) }}" data-toggle="modal"
                                        data-target="#deleteModal">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Data kosong</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

<x-modal-delete title="Hapus Jurusan" message="Yakin hapus jurusan berikut?" />