@extends('layouts.app')

@section('title', 'Pusat Bantuan & Tiket')

@section('afterAppStyles')
<style>
    .card-status { border-radius: 12px; transition: transform 0.3s; }
    .card-status:hover { transform: translateY(-5px); }
    .status-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    
    .filter-tabs .btn { border-radius: 20px; padding: 4px 18px; margin-right: 5px; border: 1px solid #ddd; color: #666; font-size: 12px; }
    .filter-tabs .btn.active { background-color: #007bff; color: #fff; border-color: #007bff; }
</style>
@endsection

@section('content')

@if(auth()->user()->role === 'siswa' && isset($stats))
<div class="row clearfix mb-2">
    <div class="col-lg-3 col-md-6">
        <div class="card card-status shadow-sm border-0">
            <div class="body d-flex align-items-center">
                <div class="status-icon bg-primary text-white mr-3 shadow-sm"><i class="fa fa-ticket"></i></div>
                <div>
                    <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Total Tiket</small>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ $stats['total'] }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card card-status shadow-sm border-0">
            <div class="body d-flex align-items-center">
                <div class="status-icon bg-warning text-white mr-3 shadow-sm"><i class="fa fa-clock-o"></i></div>
                <div>
                    <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Tiket Aktif</small>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ $stats['active'] }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card card-status shadow-sm border-0">
            <div class="body d-flex align-items-center">
                <div class="status-icon bg-info text-white mr-3 shadow-sm"><i class="fa fa-spinner"></i></div>
                <div>
                    <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Sedang Diproses</small>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ $stats['in_progress'] }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card card-status shadow-sm border-0">
            <div class="body d-flex align-items-center">
                <div class="status-icon bg-success text-white mr-3 shadow-sm"><i class="fa fa-check-circle"></i></div>
                <div>
                    <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Tiket Selesai</small>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ $stats['resolved'] }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row clearfix">
    <div class="col-lg-12">
        <div class="card shadow-none border" style="border-radius: 8px;">
            <div class="header d-flex justify-content-between align-items-center">
                <h2>Daftar Tiket Aduan</h2>
                @if(auth()->user()->role === 'siswa')
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-round shadow-sm">
                        <i class="fa fa-plus"></i> Buat Aduan Baru
                    </a>
                @endif
            </div>
            <div class="body">
                <!-- Tabs & Sort -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                    <div class="filter-tabs mb-3 mb-md-0">
                        <a href="{{ route('tickets.index') }}" class="btn {{ !request('status') ? 'active' : '' }}">All Ticket</a>
                        <a href="{{ route('tickets.index', ['status' => 'open']) }}" class="btn {{ request('status') == 'open' ? 'active' : '' }}">Open</a>
                        <a href="{{ route('tickets.index', ['status' => 'closed']) }}" class="btn {{ request('status') == 'closed' ? 'active' : '' }}">Closed</a>
                    </div>

                    <div class="d-flex align-items-center">
                        <span class="text-muted mr-2 small">Urutkan:</span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle btn-round" type="button" data-toggle="dropdown">
                                <i class="fa fa-sort-amount-{{ request('sort', 'desc') == 'desc' ? 'desc' : 'asc' }} mr-1"></i>
                                {{ request('sort', 'desc') == 'desc' ? 'Terbaru' : 'Terlama' }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'desc']) }}">Terbaru</a>
                                <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'asc']) }}">Terlama</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-custom spacing5 mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subjek</th>
                                <th>Kategori</th>
                                <th>Prioritas</th>
                                <th>Status</th>
                                <th>Tgl Dibuat</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr>
                                    <td>{{ $loop->iteration + $tickets->firstItem() - 1 }}</td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="font-weight-bold text-dark">
                                            {{ $ticket->subject }}
                                        </a>
                                        @if($ticket->responses->where('responder_id', '!=', auth()->id())->where('created_at', '>', auth()->user()->last_login_at)->count() > 0)
                                            <span class="badge badge-dot badge-danger ml-1" title="Ada balasan baru"></span>
                                        @endif
                                    </td>
                                    <td>{{ $ticket->category->name }}</td>
                                    <td>
                                        @php
                                            $priorityClass = [
                                                'Low' => 'badge-info',
                                                'Mid' => 'badge-warning',
                                                'High' => 'badge-danger'
                                            ][$ticket->priority] ?? 'badge-default';
                                        @endphp
                                        <span class="badge {{ $priorityClass }}">{{ $ticket->priority }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = [
                                                'Open' => 'badge-default',
                                                'In-Progress' => 'badge-primary',
                                                'Resolved' => 'badge-success',
                                                'Closed' => 'badge-dark'
                                            ][$ticket->status] ?? 'badge-default';
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $ticket->status }}</span>
                                    </td>
                                    <td>{{ $ticket->created_at->format('d M Y, H:i') }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Lihat Detail">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50" alt="no data">
                                        <p class="text-muted">Belum ada tiket aduan yang sesuai.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pagination-responsive mt-4">
                    {{ $tickets->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->role === 'helpdesk' && isset($stats))
<div class="row clearfix row-deck mt-2">
    <!-- Concierge Performance Card -->
    <div class="col-lg-8 col-md-7">
        <div class="card shadow-none border h-100" style="border-radius: 12px; background: linear-gradient(to right, #ffffff, #fdfdfd);">
            <div class="body">
                <div class="row align-items-center h-100">
                    <div class="col-md-8">
                        <h6 class="font-weight-bold text-primary mb-1">Concierge Performance</h6>
                        <p class="text-muted small mb-3">Resolution have improved by <span class="text-success font-weight-bold">14%</span> this week. Keep up the surgical precision, <span class="text-dark font-weight-bold">{{ auth()->user()->name }}</span>.</p>
                        
                        <div class="d-flex align-items-center mt-3">
                            <div class="mr-4 text-center">
                                <h4 class="mb-0 font-weight-bold text-dark">{{ $stats['avg_response'] }}m</h4>
                                <small class="text-muted text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Avg Response</small>
                            </div>
                            <div class="mr-4 border-left pl-4 text-center">
                                <h4 class="mb-0 font-weight-bold text-dark">{{ $stats['score_percent'] }}%</h4>
                                <small class="text-muted text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Score</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-right d-none d-md-block">
                        <div class="icon-box bg-primary-light text-primary rounded-circle shadow-xs mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                            <i class="fa fa-line-chart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Incidents Card -->
    <div class="col-lg-4 col-md-5">
        <div class="card shadow-none border h-100" style="border-radius: 12px;">
            <div class="body d-flex flex-column justify-content-center text-center py-4">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <div class="bg-danger-light text-danger rounded-circle mr-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 32px; height: 32px;">
                        <i class="fa fa-warning" style="font-size: 14px;"></i>
                    </div>
                    <h6 class="mb-0 font-weight-bold text-dark">Active Incidents</h6>
                </div>
                <h2 class="font-weight-bold mb-2 text-danger">{{ str_pad($stats['active_incidents'], 2, '0', STR_PAD_LEFT) }}</h2>
                <a href="#" class="text-info font-weight-bold small mt-1">View system status <i class="fa fa-arrow-right ml-1"></i></a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
