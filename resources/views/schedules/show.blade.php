@extends('layouts.app')

@section('title', 'Detail Jadwal Kelas ' . $rombel->nama_rombel)

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="header py-4 px-4 bg-white border-bottom">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center w-100">
                        <div class="d-flex align-items-center mb-3 mb-md-0 w-100 w-md-auto">
                            <div class="icon-box bg-primary-soft text-primary mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                                <i class="fa fa-calendar-check-o fa-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-weight-bold mb-0 text-dark">Jadwal Pelajaran: {{ $rombel->nama_rombel }}</h4>
                                <div class="d-flex flex-wrap align-items-center text-muted x-small mt-1" style="font-size: 0.75rem;">
                                    <span class="mr-3 text-uppercase font-weight-600"><i class="fa fa-tag mr-1 opacity-50"></i> TA: {{ $rombel->tahunAjar->nama ?? '-' }}</span>
                                    <span class="text-uppercase font-weight-600"><i class="fa fa-user-circle mr-1 opacity-50"></i> Walas: {{ $rombel->waliKelas->user->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="header-action d-flex flex-wrap w-100 w-md-auto justify-content-center justify-content-md-end">
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('schedules.create', ['rombel_id' => $rombel->id]) }}"
                                    class="btn btn-primary btn-sm font-weight-bold px-4 py-2 mr-md-2 mb-2 mb-md-0 flex-grow-1 flex-md-grow-0 shadow-sm rounded-pill transition-all hover-lift">
                                    <i class="fa fa-plus mr-1"></i> TAMBAH JADWAL
                                </a>
                            @endif
                            <a href="{{ route('schedules.index') }}" class="btn btn-light btn-sm font-weight-bold px-4 py-2 mb-2 mb-md-0 flex-grow-1 flex-md-grow-0 border shadow-xs rounded-pill">
                                <i class="fa fa-undo mr-1"></i> KEMBALI
                            </a>
                        </div>
                    </div>
                </div>
                <div class="body p-0 border-bottom bg-light-soft">
                    <ul class="nav nav-tabs nav-tabs-minimal border-0 flex-nowrap overflow-auto scrollbar-hidden" role="tablist">
                        @foreach($schoolDays as $day)
                            <li class="nav-item">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }} font-weight-bold px-4 py-3 text-uppercase" 
                                   data-toggle="tab" href="#day-{{ $day }}" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                    {{ $day }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <style>
                        .scrollbar-hidden::-webkit-scrollbar { display: none; }
                        .scrollbar-hidden { -ms-overflow-style: none; scrollbar-width: none; }
                        
                        .nav-tabs-minimal .nav-link { 
                            color: #888; 
                            border: none;
                            border-bottom: 3px solid transparent;
                            border-radius: 0;
                            transition: all 0.3s ease;
                            white-space: nowrap;
                        }
                        .nav-tabs-minimal .nav-link.active { 
                            color: #2b70f0 !important; 
                            background: transparent !important;
                            border-bottom: 3px solid #2b70f0;
                        }
                        .nav-tabs-minimal .nav-link:hover:not(.active) {
                            color: #2b70f0;
                            opacity: 0.8;
                        }
                        .bg-light-soft { background-color: #fcfdfe; }
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