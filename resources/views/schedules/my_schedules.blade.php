@extends('layouts.app')

@section('title', 'Jadwal Mengajar Saya')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card shadow-none border">
                <div class="header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="m-0 font-weight-bold">Jadwal Mengajar Saya</h2>
                            <small class="text-muted">Tahun Ajaran Aktif | {{ auth()->user()->name }}</small>
                        </div>
                    </div>
                </div>
                <div class="body p-0 border-bottom">
                    <ul class="nav nav-tabs nav-tabs-minimal border-0" role="tablist">
                        @foreach($schoolDays as $day)
                            <li class="nav-item">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }} font-weight-bold px-4 py-3" 
                                   data-toggle="tab" href="#day-{{ $day }}">
                                    {{ ucfirst($day) }}
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
                            @if(isset($schedules[$day]) && count($schedules[$day]) > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th>Jam</th>
                                                <th>Mata Pelajaran</th>
                                                <th>Kelas</th>
                                                <th>Jurusan</th>
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
                                                    <td>
                                                        <a href="{{ route('rombongan-belajar.show', $item->rombongan_belajar_id) }}" class="font-weight-bold text-info">
                                                            {{ $item->rombonganBelajar->nama_rombel }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $item->rombonganBelajar->jurusan->nama_jurusan ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fa fa-calendar-times-o fa-3x text-light mb-3 d-block"></i>
                                    <p class="text-muted italic">Tidak ada jadwal mengajar untuk hari ini.</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
