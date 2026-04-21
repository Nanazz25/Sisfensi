@extends('layouts.app')

@section('title', 'Detail Penilaian')

@section('afterAppStyles')
    @vite('resources/css/assessment.css')
@endsection

@section('content')
<div class="row clearfix">
    <div class="col-lg-4 col-md-12">
        <div class="card student-card overflow-hidden h-auto">
            <div class="profile-banner bg-gradient-info" style="height: 100px; background: linear-gradient(45deg, #2196F3, #00BCD4);"></div>
            <div class="body pt-0 text-center">
                <div class="card-avatar-wrapper shadow shadow-xs mx-auto" style="margin-top: -40px; width: 100px; height: 100px;">
                    <img src="{{ $evaluatee->avatar_url }}" alt="{{ $evaluatee->name }}">
                    <div class="role-icon-small" title="{{ ucfirst($evaluatee->role) }}" style="width: 32px; height: 32px; bottom: -5px; right: -5px;">
                        <i class="fa {{ $evaluatee->role === 'guru' ? 'fa-briefcase text-warning' : 'fa-graduation-cap text-info' }}" style="font-size: 14px;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <h5 class="mb-1 font-weight-bold text-dark text-uppercase">{{ $evaluatee->name }}</h5>
                    <span class="badge badge-soft-info px-3 py-1 rounded-pill mb-3" style="background: rgba(0, 188, 212, 0.1); color: #00BCD4; font-weight: 700;">{{ ucwords($evaluatee->role) }}</span>
                </div>
                
                <div class="card-stats-row mx-3 mb-4">
                    <div class="stat-item">
                        <span class="stat-label">{{ $evaluatee->role === 'guru' ? 'NIP' : 'NIS' }}</span>
                        <span class="stat-value">{{ $evaluatee->role === 'guru' ? ($evaluatee->teacher->nip ?? '-') : ($evaluatee->pesertaDidik->no_induk ?? '-') }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Email</span>
                        <span class="stat-value" style="font-size: 10px;">{{ $evaluatee->email }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8 col-md-12">
        <div class="card">
            <div class="header">
                <h2>Grafik Radar Karakter</h2>
            </div>
            <div class="body">
                @if($assessments->count() > 0)
                    <div style="max-width: 500px; margin: 0 auto;">
                        <canvas id="radarChart"></canvas>
                    </div>
                @else
                    <div class="text-center p-5">
                        <i class="fa fa-bar-chart fa-4x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada data penilaian untuk ditampilkan di grafik.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row clearfix">
    <div class="col-lg-12">
        <div class="card">
            <div class="header">
                <h2>Riwayat Penilaian</h2>
            </div>
            <div class="body">

                {{-- Filter Section --}}
                <div class="mb-4">
                    <form method="GET" action="{{ route('assessment.show', $evaluatee->id) }}" id="filterForm" class="row align-items-center">
                        <div class="col-lg-4 col-md-5 mb-2">
                            <div class="input-group shadow-xs rounded-pill overflow-hidden border">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0"><i class="fa fa-search text-muted"></i></span>
                                </div>
                                <input type="text" name="q" id="searchInput" value="{{ request('q') }}" class="form-control border-0" placeholder="Cari periode atau catatan...">
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 mb-2">
                            <select name="period" class="form-control rounded-pill border shadow-xs" onchange="document.getElementById('filterForm').submit()">
                                <option value="">Semua Periode</option>
                                @foreach($availablePeriods as $ap)
                                    <option value="{{ $ap }}" {{ request('period') == $ap ? 'selected' : '' }}>{{ $ap }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-auto mb-2">
                            <div class="btn-group shadow-xs rounded-pill border">
                                <button type="button" class="btn btn-white btn-sm px-3 dropdown-toggle border-0" data-toggle="dropdown" style="height: 38px;">
                                    <i class="fa {{ request('sort') == 'asc' ? 'fa-sort-amount-asc' : 'fa-sort-amount-desc' }} mr-1 text-muted"></i> 
                                    {{ request('sort') == 'asc' ? 'Terlama' : 'Terbaru' }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow border-0">
                                    <a class="dropdown-item sort-option" data-value="desc" href="javascript:void(0);"><i class="fa fa-clock-o mr-2"></i>Terbaru</a>
                                    <a class="dropdown-item sort-option" data-value="asc" href="javascript:void(0);"><i class="fa fa-history mr-2"></i>Terlama</a>
                                </div>
                            </div>
                        </div>

                        <div class="col mb-2 text-right">
                            <a href="{{ route('assessment.show', $evaluatee->id) }}" class="btn btn-sm btn-outline-danger rounded-pill shadow-xs px-3">
                                <i class="fa fa-undo mr-1"></i> RESET
                            </a>
                        </div>
                        <input type="hidden" name="sort" id="sortInput" value="{{ request('sort', 'desc') }}">
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Periode</th>
                                <th>Kategori</th>
                                <th>Skor</th>
                                <th>Penilai</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessments as $assessment)
                            @php
                                $totalScore = $assessment->details->sum('score');
                                $count = $assessment->details->count();
                                $avgScore = $count > 0 ? round($totalScore / $count, 1) : 0;
                            @endphp
                            <tr>
                                <td>{{ $assessment->assessment_date->format('d M Y') }}</td>
                                <td><span class="badge badge-default">{{ $assessment->period }}</span></td>
                                <td>
                                    <div class="text-wrap" style="max-width: 200px;">
                                        @foreach($assessment->details as $detail)
                                            <span class="badge badge-outline-secondary mb-1">{{ $detail->category->name ?? 'N/A' }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $avgScore >= 7 ? 'success' : ($avgScore >= 5 ? 'warning' : 'danger') }}">
                                        {{ $avgScore }} / 10
                                    </span>
                                    @if($count > 1)
                                        <br><small class="text-muted">({{ $count }} Indikator)</small>
                                    @endif
                                </td>
                                <td>{{ $assessment->evaluator->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($assessment->general_notes, 30) }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#detail-{{ $assessment->id }}">
                                        <i class="fa fa-search"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $assessments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($assessments as $assessment)
<div class="modal fade" id="detail-{{ $assessment->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rincian Penilaian - {{ $assessment->assessment_date->format('d M Y') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Indikator</th>
                                <th>Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessment->details as $detail)
                            <tr>
                                <td>{{ $detail->category->name }}</td>
                                <td>
                                    <div class="progress progress-xs mb-0" style="width: 100px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $detail->score * 10 }}%"></div>
                                    </div>
                                    <span class="small"><strong>{{ $detail->score }}</strong> / 10</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <hr>
                <h6 class="text-primary"><i class="fa fa-comment"></i> Catatan/Feedback:</h6>
                <div class="p-3 bg-light rounded border italic text-muted" style="font-size: 13px; line-height: 1.6;">
                    {{ $assessment->general_notes ?? 'Tidak ada catatan.' }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('afterAppScripts')
@if($assessments->count() > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.radarChartData = {
            labels: {!! json_encode($radarData->pluck('name')) !!},
            values: {!! json_encode($radarData->pluck('score')) !!}
        };
    </script>
    @vite('resources/js/assessment/show.js')
@endif
@endsection
