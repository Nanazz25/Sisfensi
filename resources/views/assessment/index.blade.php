@extends('layouts.app')

@section('title', 'Daftar Penilaian')

@section('afterAppStyles')
    @vite('resources/css/assessment.css')
@endsection

@section('content')
<div class="row clearfix mb-2">
    <div class="col-lg-12 mb-2">
        <div class="card shadow-sm border-0">
            <div class="body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 font-weight-bold text-dark">
                    @if($periodFilter)
                        Progres Penilaian: {{ $periodFilter }}
                    @else
                        Progres Penilaian Bulan Ini ({{ now()->translatedFormat('F') }})
                    @endif
                </h6>
                <span class="badge badge-info shadow-xs p-2">
                    <i class="fa fa-info-circle mr-1"></i> {{ $assessedCount }} / {{ $totalTargets }} {{ Auth::user()->role === 'admin' ? 'Guru' : 'Siswa' }} Terdata
                </span>
                </div>
                <div class="progress progress-sm rounded-pill mb-2" style="height: 12px;">
                    <div class="progress-bar bg-gradient-success shadow-sm" role="progressbar" style="width: {{ $progressPercent }}%;" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        @if($progressPercent == 100)
                            <i class="fa fa-check-circle text-success mr-1"></i> Luar biasa! Semua target telah diselesaikan.
                        @else
                            Selesaikan {{ $totalTargets - $assessedCount }} lagi untuk mencapai target 100%.
                        @endif
                    </small>
                    <span class="font-weight-bold text-success">{{ $progressPercent }}%</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="body p-2">
                <h6 class="mb-2 font-weight-bold text-dark"><i class="fa fa-bar-chart mr-2 text-info"></i>Rata-rata Skor per Kategori</h6>
                @if($averageChartData->count() > 0)
                    <div style="height: 180px; position: relative;">
                        <canvas id="averageChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-3">
                        <small class="text-muted italic">Belum ada data nilai pada periode ini</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row clearfix">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="header d-flex justify-content-between align-items-center pb-0">
                <h2 class="font-weight-bold">Daftar {{ Auth::user()->role === 'admin' ? 'Guru' : 'Siswa' }} yang Dinilai</h2>
                <a href="{{ route('assessment.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                    <i class="fa fa-plus-circle mr-1"></i> Penilaian Massal
                </a>
            </div>
            <div class="body pt-3">
                {{-- Filter Section --}}
                <div class="mb-4">
                    <form method="GET" action="{{ route('assessment.index') }}" id="filterForm" class="row no-gutters">
                        <div class="col-12 col-lg-4 mb-2 pr-lg-2">
                            <div class="input-group shadow-xs rounded-pill overflow-hidden border">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0"><i class="fa fa-search text-muted"></i></span>
                                </div>
                                <input type="text" name="q" id="searchInput" value="{{ request('q') }}" class="form-control border-0" placeholder="Cari nama atau email...">
                            </div>
                        </div>

                        <div class="col-6 col-lg-3 mb-2 px-1 px-lg-0 pr-lg-2">
                            <select name="period" class="form-control rounded-pill border shadow-xs" onchange="document.getElementById('filterForm').submit()">
                                <option value="">-- Periode --</option>
                                @foreach($availablePeriods as $ap)
                                    <option value="{{ $ap }}" {{ $periodFilter == $ap ? 'selected' : '' }}>{{ $ap }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-auto mb-2 px-1 px-lg-0 pr-lg-2">
                            <div class="btn-group shadow-xs rounded-pill border w-100">
                                <button type="button" class="btn btn-white btn-sm px-3 dropdown-toggle border-0 w-100" data-toggle="dropdown" style="height: 38px;">
                                    <i class="fa {{ request('sort') == 'asc' ? 'fa-sort-amount-asc' : 'fa-sort-amount-desc' }} mr-1 text-muted"></i> 
                                    {{ request('sort') == 'asc' ? 'Lama' : 'Baru' }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow border-0">
                                    <a class="dropdown-item sort-option" data-value="desc" href="javascript:void(0);"><i class="fa fa-clock-o mr-2"></i>Terbaru</a>
                                    <a class="dropdown-item sort-option" data-value="asc" href="javascript:void(0);"><i class="fa fa-history mr-2"></i>Terlama</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-auto mb-2 ml-md-auto px-1">
                            <a href="{{ route('assessment.index') }}" class="btn btn-sm btn-outline-danger btn-block rounded-pill shadow-xs px-3 d-flex align-items-center justify-content-center" style="height: 38px;">
                                <i class="fa fa-undo mr-1"></i> RESET
                            </a>
                        </div>
                        <input type="hidden" name="sort" id="sortInput" value="{{ request('sort', 'desc') }}">
                    </form>
                </div>

                {{-- Card Grid --}}
                <div class="row px-2">
                    @forelse($evaluatees as $evaluatee)
                    <div class="col-lg-4 col-md-6 col-6 assessment-item">
                        <div class="student-card shadow-xs">
                            {{-- Status Badge --}}
                            <div class="status-badge-floating {{ $evaluatee->is_assessed_in_period ? 'status-done' : 'status-pending' }}">
                                <i class="fa {{ $evaluatee->is_assessed_in_period ? 'fa-check-circle' : 'fa-clock-o' }} mr-1"></i>
                                {{ $evaluatee->is_assessed_in_period ? 'Selesai' : 'Belum' }}
                            </div>

                            {{-- Avatar --}}
                            <div class="card-avatar-wrapper">
                                <img src="{{ $evaluatee->avatar_url }}" alt="{{ $evaluatee->name }}">
                                <div class="role-icon-small" title="{{ ucfirst($evaluatee->role) }}">
                                    <i class="fa {{ $evaluatee->role === 'guru' ? 'fa-briefcase text-warning' : 'fa-graduation-cap text-info' }}" style="font-size: 10px;"></i>
                                </div>
                            </div>

                            {{-- Info --}}
                            <div class="student-info">
                                <span class="student-name text-truncate">{{ $evaluatee->name }}</span>
                                <span class="student-email text-truncate">{{ $evaluatee->email }}</span>

                                {{-- Stats Table-like Row --}}
                                <div class="card-stats-row">
                                    <div class="stat-item">
                                        <span class="stat-label">{{ $evaluatee->role === 'guru' ? 'NIP' : 'NIS' }}</span>
                                        <span class="stat-value">{{ $evaluatee->role === 'guru' ? ($evaluatee->teacher->nip ?? '-') : ($evaluatee->pesertaDidik->no_induk ?? '-') }}</span>
                                    </div>
                                    <div class="stat-item d-none d-sm-block">
                                        <span class="stat-label">Terakhir</span>
                                        <span class="stat-value">
                                            {{ $evaluatee->last_assessment ? $evaluatee->last_assessment->assessment_date->translatedFormat('d M y') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Action --}}
                                <a href="{{ route('assessment.show', $evaluatee->id) }}" class="btn btn-primary btn-block btn-card-action shadow-sm">
                                    <i class="fa fa-line-chart mr-1"></i> <span class="d-none d-sm-inline">Detail & Riwayat</span><span class="d-inline d-sm-none">Detail</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 py-5 text-center">
                        <div class="p-5 bg-light rounded shadow-xs" style="border: 2px dashed #dee2e6;">
                            <i class="fa fa-users fa-4x text-light mb-4"></i>
                            <h5 class="text-muted font-weight-bold">Tidak ada data ditemukan</h5>
                            <p class="text-muted mb-0">Coba gunakan kata kunci lain atau pilih periode yang berbeda.</p>
                        </div>
                    </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-4 pagination-responsive">
                    {{ $evaluatees->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('afterAppScripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.averageChartData = {
            labels: {!! json_encode($averageChartData->pluck('name')) !!},
            values: {!! json_encode($averageChartData->pluck('score')) !!}
        };
    </script>
    @vite('resources/js/assessment/index.js')
@endsection
