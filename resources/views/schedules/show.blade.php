@extends('layouts.app')

@section('title', 'Detail Jadwal Kelas ' . $rombel->nama_rombel)

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card shadow-none border">
                <div class="header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="m-0 font-weight-bold">Jadwal Pelajaran: {{ $rombel->nama_rombel }}</h2>
                            <small class="text-muted">Tahun Ajaran: {{ $rombel->tahunAjar->nama ?? '-' }} | Wali Kelas:
                                {{ $rombel->waliKelas->user->name ?? '-' }}</small>
                        </div>
                        <div class="header-action">
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('schedules.create', ['rombel_id' => $rombel->id]) }}"
                                    class="btn btn-success btn-sm">
                                    <i class="fa fa-plus"></i> Tambah Jadwal
                                </a>
                            @endif
                            <a href="{{ route('schedules.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="body p-0 border-bottom">
                    <ul class="nav nav-tabs nav-tabs-minimal border-0" role="tablist">
                        @foreach($schoolDays as $day)
                            <li class="nav-item">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }} font-weight-bold px-4 py-3" 
                                   data-toggle="tab" href="#day-{{ $day }}">
                                    {{ $day }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <style>
                        .nav-tabs-minimal .nav-link { 
                            color: #a0a0a0; 
                            border: none;
                            border-bottom: 2px solid transparent;
                            border-radius: 0;
                            transition: all 0.2s ease;
                            font-size: 12px;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                            white-space: nowrap;
                        }
                        .nav-tabs-minimal .nav-link.active { 
                            color: #333 !important; 
                            background: transparent !important;
                            border-bottom: 2px solid #333;
                        }
                        .nav-tabs-minimal .nav-link:hover:not(.active) {
                            color: #666;
                            border-bottom: 2px solid #e9ecef;
                        }
                    </style>
                </div>

                <!-- Tab Panes -->
                <div class="tab-content p-4">
                    @foreach($schoolDays as $day)
                        <div class="tab-pane {{ $loop->first ? 'active' : '' }}" id="day-{{ $day }}">
                            @if(isset($schedules[$day]))
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th>Jam</th>
                                                <th>Mata Pelajaran</th>
                                                <th>Guru</th>
                                                @if(auth()->user()->role === 'admin')
                                                    <th class="text-center">Aksi</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($schedules[$day] as $item)
                                                <tr>
                                                    <td class="font-weight-bold text-primary" style="width: 150px;">
                                                        {{ substr($item->jam_mulai, 0, 5) }} -
                                                        {{ substr($item->jam_selesai, 0, 5) }}
                                                    </td>
                                                    <td class="font-weight-bold">{{ $item->subject->nama_mapel }}</td>
                                                    <td>{{ $item->teacher->user->name ?? '-' }}</td>
                                                    @if(auth()->user()->role === 'admin')
                                                        <td class="text-center">
                                                            <a href="{{ route('schedules.edit', $item->id) }}"
                                                                class="btn btn-link py-0 text-warning" title="Edit">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                            <button class="btn btn-link py-0 text-danger btn-delete"
                                                                data-name="{{ $item->subject->nama_mapel }}"
                                                                data-action="{{ route('schedules.destroy', $item->id) }}"
                                                                data-toggle="modal" data-target="#deleteModal">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fa fa-calendar-times-o fa-3x text-light mb-3 d-block"></i>
                                    <p class="text-muted italic">Tidak ada jadwal pelajaran untuk hari ini.</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    </div>

    <x-modal-delete title="Hapus Jadwal" message="Yakin ingin menghapus jadwal ini?" />
@endsection