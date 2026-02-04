@extends('layouts.app')
@section('title', 'Jadwal Pelajaran')

@section('content')
    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Jadwal Pelajaran</h2>
            <a href="{{ route('schedules.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Jadwal
            </a>
        </div>

        <div class="body">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Rombel</th>
                            <th>Mapel</th>
                            <th>Guru</th>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->rombonganBelajar->nama_rombel }}</td>
                                <td>{{ $item->subject->nama_mapel }}</td>
                                <td>{{ $item->teacher->user->name }}</td>
                                <td>{{ ucfirst($item->hari) }}</td>
                                <td>{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</td>
                                <td>
                                    <a href="{{ route('schedules.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger btn-delete"
                                        data-name="{{ $item->subject->nama_mapel }}"
                                        data-action="{{ route('schedules.destroy', $item->id) }}" data-toggle="modal"
                                        data-target="#deleteModal">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Data kosong</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

<x-modal-delete title="Hapus Jadwal" message="Yakin hapus jadwal berikut?" />