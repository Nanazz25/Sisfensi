@extends('layouts.app')
@section('title', 'Mata Pelajaran')

@section('content')

    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Mata Pelajaran</h2>
            <a href="{{ route('subjects.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Mapel
            </a>
        </div>

        <div class="body">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Mata Pelajaran</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_mapel }}</td>
                                <td>
                                    <a href="{{ route('subjects.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger btn-delete" data-name="{{ $item->nama_mapel }}"
                                        data-action="{{ route('subjects.destroy', $item->id) }}" data-toggle="modal"
                                        data-target="#deleteModal">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Data kosong</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

<x-modal-delete title="Hapus Mata Pelajaran" message="Yakin hapus mata pelajaran berikut?" />