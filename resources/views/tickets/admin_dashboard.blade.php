@extends('layouts.app')

@section('title', 'Analitik Performa Helpdesk')

@section('afterAppStyles')
<style>
    .card-status { border-radius: 12px; transition: transform 0.3s; }
    .card-status:hover { transform: translateY(-5px); }
    .status-icon { width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
    
    .circle-chart-wrapper { display: flex; justify-content: center; align-items: center; padding: 20px; }
    .circle-chart {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: conic-gradient(#ffc107 {{ ($satisfactionAvg / 5) * 100 }}%, #f0f0f0 0);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
    }
    .circle-chart::after {
        content: "";
        width: 130px;
        height: 130px;
        background: #fff;
        border-radius: 50%;
        position: absolute;
    }
    .circle-chart .inner-content { position: relative; z-index: 1; text-align: center; }
    .circle-chart .value { font-size: 2.5rem; font-weight: 800; color: #333; line-height: 1; }
    .circle-chart .label { font-size: 0.8rem; color: #888; text-transform: uppercase; font-weight: bold; }

    .op-progress { height: 8px; border-radius: 4px; background-color: #f0f0f0; margin-top: 5px; }
    .op-item { margin-bottom: 20px; }
    .op-info { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px; }
</style>
@endsection

@section('content')
<div class="row clearfix">
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-status shadow-sm border-0">
            <div class="body">
                <div class="d-flex align-items-center">
                    <div class="status-icon bg-primary text-white mr-3 shadow-sm">
                        <i class="fa fa-ticket"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Total Tiket</small>
                        <h5 class="mb-0 font-weight-bold text-dark">{{ number_format($stats['total']) }}</h5>
                        <small class="text-success font-weight-bold" style="font-size: 10px;"><i class="fa fa-caret-up"></i> +12%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-status shadow-sm border-0">
            <div class="body">
                <div class="d-flex align-items-center">
                    <div class="status-icon bg-warning text-white mr-3 shadow-sm">
                        <i class="fa fa-folder-open"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Tiket Open</small>
                        <h5 class="mb-0 font-weight-bold text-dark">{{ number_format($stats['open']) }}</h5>
                        <small class="text-danger font-weight-bold" style="font-size: 10px;">Immediate attention</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-status shadow-sm border-0">
            <div class="body">
                <div class="d-flex align-items-center mb-2">
                    <div class="status-icon bg-info text-white mr-3 shadow-sm">
                        <i class="fa fa-spinner"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">In Progress</small>
                        <h5 class="mb-0 font-weight-bold text-dark">{{ number_format($stats['in_progress']) }}</h5>
                    </div>
                </div>
                <div class="progress progress-xs mb-0 mt-2">
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $stats['total'] > 0 ? ($stats['in_progress'] / $stats['total']) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-status shadow-sm border-0">
            <div class="body">
                <div class="d-flex align-items-center">
                    <div class="status-icon bg-success text-white mr-3 shadow-sm">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Tiket Closed</small>
                        <h5 class="mb-0 font-weight-bold text-dark">{{ number_format($stats['closed']) }}</h5>
                        <small class="text-success font-weight-bold" style="font-size: 10px;"><i class="fa fa-check"></i> Efficiency</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row clearfix">
    <!-- Operator Performance (Response Time) -->
    <div class="col-lg-8 col-md-12">
        <div class="card shadow-none border" style="border-radius: 8px;">
            <div class="header">
                <h2 class="font-weight-800">Rata-rata Response Time per Operator</h2>
            </div>
            <div class="body">
                @foreach($operatorPerformance as $op)
                    <div class="op-item">
                        <div class="op-info">
                            <span class="font-weight-bold text-dark">{{ $op->operator_name }}</span>
                            @php
                                $mins = round($op->avg_response_seconds / 60);
                                // Percentage for progress bar (cap at 60 mins for 100%)
                                $percent = min(100, ($mins / 60) * 100);
                                $colorClass = $mins <= 15 ? 'bg-success' : ($mins <= 45 ? 'bg-warning' : 'bg-danger');
                            @endphp
                            <span class="text-muted small font-weight-bold">{{ $mins }} Menit</span>
                        </div>
                        <div class="progress op-progress">
                            <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ 100 - $percent }}%"></div>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">Menangani {{ $op->tickets_handled }} tiket</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Rating Satisfaction -->
    <div class="col-lg-4 col-md-12">
        <div class="card shadow-none border" style="border-radius: 8px; height: calc(100% - 30px);">
            <div class="header">
                <h2 class="font-weight-800 text-center">Kepuasan Pelapor</h2>
            </div>
            <div class="body">
                <div class="circle-chart-wrapper">
                    <div class="circle-chart">
                        <div class="inner-content">
                            <div class="value">{{ number_format($satisfactionAvg, 1) }}</div>
                            <div class="label">AVG Stats</div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <div class="rating-stars mb-1" style="font-size: 1.2rem; color: #ffc107;">
                        @for($i=1; $i<=5; $i++)
                            <i class="fa fa-star{{ $i <= round($satisfactionAvg) ? '' : '-o' }}"></i>
                        @endfor
                    </div>
                    <p class="text-muted small">Berdasarkan total rating dari seluruh tiket yang diselesaikan.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row clearfix">
    <!-- Top 5 Operator -->
    <div class="col-lg-12">
        <div class="card shadow-none border" style="border-radius: 8px;">
            <div class="header">
                <h2 class="font-weight-800">Top 5 Operator Terbaik</h2>
            </div>
            <div class="body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom spacing5 mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Operator</th>
                                <th>Tiket Selesai</th>
                                <th>Rerata Respon</th>
                                <th>Rerata Penyelesaian</th>
                                <th>Status Performa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topOperators as $top)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="font-weight-bold">{{ $top->operator_name }}</td>
                                    <td>{{ $top->tickets_handled }}</td>
                                    <td>{{ round($top->avg_response_seconds / 60) }} m</td>
                                    <td>{{ round($top->avg_resolution_seconds / 3600, 1) }} jam</td>
                                    <td>
                                        @php
                                            $score = ($top->tickets_handled * 2) - ($top->avg_response_seconds / 300);
                                            $badgeClass = $score > 5 ? 'badge-success' : ($score > 0 ? 'badge-info' : 'badge-warning');
                                            $badgeText = $score > 5 ? 'Excellent' : ($score > 0 ? 'Good' : 'Average');
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
