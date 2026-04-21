@extends('layouts.app')

@section('title', 'Monitoring Integritas Siswa')

@section('afterAppStyles')
<style>
    .podium-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: none !important;
        overflow: hidden;
    }
    .podium-card:hover { rotate: -1deg; scale: 1.02; }
    .gold-bg { background: linear-gradient(135deg, #fffcf0 0%, #fffae0 100%); border-bottom: 4px solid #FFD700 !important; }
    .silver-bg { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 4px solid #C0C0C0 !important; }
    .bronze-bg { background: linear-gradient(135deg, #fff5f0 0%, #ffece0 100%); border-bottom: 4px solid #CD7F32 !important; }
    
    .leaderboard-item { transition: all 0.3s ease; border-bottom: 1px solid #f8f9fa; }
    .leaderboard-item:hover { background: #fcfdfe; transform: translateX(5px); }
    .badge-soft-success { background: #e6ffed; color: #22c55e; }
    .badge-soft-danger { background: #fee2e2; color: #ef4444; }
    .badge-soft-primary { background: #e0f2fe; color: #0ea5e9; }

    /* Custom Navigation Pills */
    .nav-pills-custom .nav-link {
        color: #64748b !important;
        background: #f1f5f9 !important;
        border-radius: 20px;
        margin-left: 5px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .nav-pills-custom .nav-link.active {
        background: #007bff !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
    }
    .nav-pills-custom .nav-link:hover:not(.active) { background: #e2e8f0 !important; }

    /* Responsive Header & Filter Fix */
    @media (max-width: 991px) {
        .header h2 { font-size: 1.1rem !important; margin-bottom: 15px; }
        .nav-pills-custom { width: 100%; display: flex; overflow-x: auto; padding-bottom: 5px; white-space: nowrap; }
        .nav-pills-custom .nav-item { flex: 0 0 auto; }
        
        /* Filter select responsiveness */
        .header form .col-6, .header form .col-12 {
            padding-left: 4px !important;
            padding-right: 4px !important;
        }
        .header form select, .header form input {
            min-width: 0 !important;
            font-size: 0.8rem !important;
        }
    }

    @media (max-width: 576px) {
        /* FIX TOTAL: Paksa semua container untuk kolom tunggal */
        #podiumWrapperTop, #leaderboardTabContent .row {
            display: flex !important;
            flex-direction: column !important;
            flex-wrap: nowrap !important;
        }
        
        .podium-col {
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
        }
        
        .podium-card { 
            padding: 1.25rem 1rem !important; 
            margin-bottom: 10px !important;
            width: 100% !important;
        }

        .podium-card img { width: 55px !important; height: 55px !important; }
        .podium-card h6 { font-size: 0.85rem !important; }
        
        /* Tabel Fix */
        .table-responsive {
            display: block !important;
            width: 100% !important;
            overflow-x: auto !important;
        }
        
        .table-custom {
            min-width: 450px !important; /* Paksa scroll daripada gepeng */
        }
    }

    /* Table Responsiveness */
    .table-responsive {
        border: none;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>
@endsection

@section('content')
<div class="row clearfix mb-4">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0" id="mainMonitoringCard">
<style>
    @media (max-width: 576px) {
        #mainMonitoringCard #podiumWrapperTop {
            display: flex !important;
            flex-direction: column !important;
            flex-wrap: nowrap !important;
        }
        #mainMonitoringCard .podium-col {
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
            display: block !important;
            margin-bottom: 15px !important;
        }
        #mainMonitoringCard .podium-card {
            padding: 1.25rem 0.75rem !important;
            width: 100% !important;
            display: block !important;
        }
        #mainMonitoringCard .podium-card img {
            width: 55px !important;
            height: 55px !important;
        }
    }
</style>
            <div class="header pb-2">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center w-100 mb-2">
                    <div class="mb-2 mb-lg-0">
                        <h2 class="font-weight-bold mb-0"><i class="fa fa-trophy text-warning mr-2"></i>Hall of Fame & Monitoring</h2>
                    </div>
                    
                    <div class="header-filters">
                        <form method="GET" action="{{ route('integrity.user.index') }}" class="row no-gutters m-0 mt-lg-0 mt-2 align-items-center justify-content-lg-end">
                            <div class="col-6 col-md-auto px-1 mb-2">
                                <select name="tingkat" class="form-control form-control-sm rounded-pill shadow-xs w-100" onchange="this.form.submit()">
                                    <option value="">Semua Angkatan</option>
                                    @foreach($tingkats as $t)
                                        <option value="{{ $t }}" {{ request('tingkat') == $t ? 'selected' : '' }}>Angkatan {{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-auto px-1 mb-2">
                                <select name="jurusan_id" class="form-control form-control-sm rounded-pill shadow-xs w-100" onchange="this.form.submit()">
                                    <option value="">Semua Jurusan</option>
                                    @foreach($jurusans as $j)
                                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-auto px-1 mb-2">
                                <select name="rombel_id" class="form-control form-control-sm rounded-pill shadow-xs w-100" onchange="this.form.submit()">
                                    <option value="">Semua Rombel</option>
                                    @foreach($rombels as $r)
                                        <option value="{{ $r->id }}" {{ request('rombel_id') == $r->id ? 'selected' : '' }}>{{ $r->nama_rombel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(request()->hasAny(['tingkat', 'jurusan_id', 'rombel_id', 'q', 'my_class']))
                                <div class="col-6 col-md-auto px-1 mb-2">
                                    <a href="{{ route('integrity.user.index') }}" class="btn btn-sm btn-outline-danger btn-block rounded-pill shadow-xs"><i class="fa fa-undo"></i> Reset</a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
                
                <ul class="nav nav-pills nav-pills-custom mb-2" id="leaderboardTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active small py-1 px-3" id="top-tab" data-toggle="tab" href="#top-rank" role="tab">Peringkat Atas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link small py-1 px-3" id="bottom-tab" data-toggle="tab" href="#bottom-rank" role="tab">Peringkat Bawah</a>
                    </li>
                </ul>
            </div>
            <div class="body">
                <div class="tab-content" id="leaderboardTabContent">
                    <!-- Tab Top Rank -->
                    <div class="tab-pane fade show active" id="top-rank" role="tabpanel">
                        <div class="row" id="podiumWrapperTop">
                            @forelse($topUsers->take(3) as $index => $top)
                            <div class="col-12 col-md-4 mb-3 podium-col">
                                @php
                                    $bgClass = $index == 0 ? 'gold-bg' : ($index == 1 ? 'silver-bg' : 'bronze-bg');
                                    $trophyColor = $index == 0 ? 'gold' : ($index == 1 ? 'silver' : 'bronze');
                                @endphp
                                <div class="podium-card text-center p-4 rounded shadow-sm {{ $bgClass }}">
                                    <div class="position-relative d-inline-block mb-3">
                                        <img src="{{ $top->avatar_url }}" class="rounded-circle shadow" width="70" height="70" style="border: 3px solid #fff; object-fit: cover;">
                                        <div class="rank-badge-overlay position-absolute" style="bottom: -5px; right: -5px;">
                                            <i class="fa fa-trophy {{ $trophyColor }} fa-2x shadow-text"></i>
                                        </div>
                                    </div>
                                    @if(auth()->user()->role === 'admin' && $top->pesertaDidik)
                                        <a href="{{ route('peserta-didik.show', $top->pesertaDidik->id) }}" class="text-decoration-none">
                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $top->name }}</h6>
                                        </a>
                                    @else
                                        <h6 class="mb-0 font-weight-bold text-dark">{{ $top->name }}</h6>
                                    @endif
                                    <p class="mb-2 small text-muted">{{ $top->active_rombel->rombonganBelajar->nama_rombel ?? 'Siswa' }}</p>
                                    
                                    <div class="d-inline-flex align-items-center bg-white-50 px-3 py-1 rounded-pill" style="backdrop-filter: blur(4px); background: rgba(255,255,255,0.4);">
                                        <i class="fa fa-shield mr-1 text-primary" style="font-size: 10px;"></i>
                                        <span class="font-weight-bold text-primary" style="font-size: 10px; letter-spacing: 0.5px;">{{ strtoupper($top->level) }}</span>
                                    </div>
                                    <div class="badge badge-success px-4 py-2 rounded-pill shadow-xs" style="font-size: 0.85rem;">
                                        {{ number_format($top->total_points ?? 0) }} P
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-4 text-muted">Belum ada data prestasi.</div>
                            @endforelse
                        </div>
                        
                        @if($topUsers->count() > 3)
                        <div class="table-responsive mt-3">
                            <table class="table table-hover table-custom spacing5 mb-0">
                                <tbody>
                                    @foreach($topUsers->slice(3, 7) as $u)
                                    <tr class="leaderboard-item">
                                        <td class="w40 text-center font-weight-bold text-muted">#{{ $loop->iteration + 3 }}</td>
                                        <td class="w40"><img src="{{ $u->avatar_url }}" class="rounded-circle" width="30"></td>
                                        <td>
                                            @if(auth()->user()->role === 'admin' && $u->pesertaDidik)
                                                <a href="{{ route('peserta-didik.show', $u->pesertaDidik->id) }}" class="font-weight-bold text-dark">{{ $u->name }}</a>
                                            @else
                                                <span class="font-weight-bold">{{ $u->name }}</span>
                                            @endif
                                            <div class="mt-1 d-flex align-items-center">
                                                <small class="text-muted mr-2">{{ $u->active_rombel->rombonganBelajar->nama_rombel ?? 'Siswa' }}</small>
                                                <span class="text-primary font-weight-bold" style="font-size: 9px; opacity: 0.8;">
                                                    <i class="fa fa-shield mr-1"></i>{{ $u->level }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-soft-success rounded-pill px-3">{{ number_format($u->total_points ?? 0) }} P</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>

                    <!-- Tab Ranking Bawah -->
                    <div class="tab-pane fade" id="bottom-rank" role="tabpanel">
                        <div class="alert alert-soft-warning py-2 px-3 mb-4 rounded-pill">
                            <i class="fa fa-info-circle mr-2"></i> Monitoring siswa dengan akumulasi poin terendah (Peringkat Bawah).
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom spacing5">
                                <thead>
                                    <tr>
                                        <th class="text-center">Rank</th>
                                        <th>Siswa</th>
                                        <th>Poin</th>
                                        <th>Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bottomUsers->take(10) as $index => $u)
                                    <tr class="leaderboard-item">
                                        <td class="text-center font-weight-bold text-danger">#{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $u->avatar_url }}" class="rounded-circle mr-2" width="35" style="filter: grayscale(80%);">
                                                <div>
                                                    @if(auth()->user()->role === 'admin' && $u->pesertaDidik)
                                                        <a href="{{ route('peserta-didik.show', $u->pesertaDidik->id) }}" class="font-weight-bold text-dark">{{ $u->name }}</a>
                                                    @else
                                                        <span class="font-weight-bold text-dark">{{ $u->name }}</span>
                                                    @endif
                                                    <div class="mt-1 d-flex align-items-center">
                                                        <small class="text-danger mr-2">{{ $u->active_rombel->rombonganBelajar->nama_rombel ?? 'Siswa' }}</small>
                                                        <span class="text-danger font-weight-bold" style="font-size: 9px; opacity: 0.8;">
                                                            <i class="fa fa-warning mr-1"></i>{{ $u->level }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-danger rounded-pill px-3 py-2 font-weight-bold">
                                                {{ number_format($u->total_points ?? 0) }} P
                                            </span>
                                        </td>
                                        <td><span class="text-muted small italic">Perlu Pembinaan</span></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center p-4">Semua siswa dalam kondisi baik.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row clearfix">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center flex-wrap">
                <h2 class="font-weight-bold mb-lg-0 mb-3">Status Kedisiplinan Siswa</h2>
                
                <form method="GET" action="{{ route('integrity.user.index') }}" class="row no-gutters m-0 align-items-center justify-content-lg-end">
                    @if($isWalas)
                    <div class="col-auto px-2 mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="myClassCheck" 
                                   {{ request('my_class') ? 'checked' : '' }}
                                   onchange="window.location.href = updateQueryStringParameter(window.location.href, 'my_class', this.checked ? '1' : '')">
                            <label class="custom-control-label font-weight-bold" for="myClassCheck">Kelas Saya</label>
                        </div>
                    </div>
                    @endif

                    <div class="col-6 col-md-auto px-1 mb-2">
                        <select name="rombel_id" class="form-control form-control-sm rounded-pill shadow-xs w-100" onchange="this.form.submit()">
                            <option value="">Pilih Rombel</option>
                            @foreach($rombels as $r)
                                <option value="{{ $r->id }}" {{ request('rombel_id') == $r->id ? 'selected' : '' }}>{{ $r->nama_rombel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-auto px-1 mb-2">
                        <div class="input-group input-group-sm shadow-xs rounded-pill overflow-hidden">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control border-0" placeholder="Cari nama...">
                            <div class="input-group-append">
                                <button class="btn btn-primary px-3" type="submit"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom spacing5">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <label class="custom-control custom-checkbox mb-0">
                                        <input type="checkbox" id="selectAllStudents" class="custom-control-input">
                                        <span class="custom-control-label"></span>
                                    </label>
                                </th>
                                <th>Siswa</th>
                                <th class="d-none d-md-table-cell">NIS</th>
                                <th>Poin</th>
                                <th class="d-none d-sm-table-cell">Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tr id="selectAllPromptRow" style="display: none; background-color: #e0f2fe;">
                            <td colspan="6" class="text-center py-2 text-primary" style="font-size: 0.85rem;">
                                <span id="selectAllPromptText">Semua siswa di halaman ini terpilih.</span>
                                @if(isset($allFilteredStudentIds) && count($allFilteredStudentIds) > 0)
                                <button class="btn btn-sm btn-link font-weight-bold text-primary p-0 ml-1" id="btnSelectAllFiltered">
                                    Pilih seluruh {{ count($allFilteredStudentIds) }} siswa?
                                </button>
                                <span id="allSelectedText" class="font-weight-bold text-success" style="display: none;">
                                    <i class="fa fa-check-circle"></i> Seluruh {{ count($allFilteredStudentIds) }} siswa telah terpilih.
                                    <button class="btn btn-sm btn-link text-danger p-0 ml-1" id="btnClearGlobalSelection">Batal</button>
                                </span>
                                @endif
                            </td>
                        </tr>
                        <tbody>
                            @foreach($students as $student)
                            <tr>
                                <td>
                                    <label class="custom-control custom-checkbox mb-0">
                                        <input type="checkbox" class="custom-control-input student-checkbox" 
                                               value="{{ $student->id }}" 
                                               id="user_{{ $student->id }}"
                                               data-name="{{ $student->name }}">
                                        <span class="custom-control-label" for="user_{{ $student->id }}"></span>
                                    </label>
                                </td>
                                <td style="min-width: 120px;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-info" style="line-height: 1;">
                                            @if(auth()->user()->role === 'admin' && $student->pesertaDidik)
                                                <a href="{{ route('peserta-didik.show', $student->pesertaDidik->id) }}" class="font-weight-bold text-primary small d-block mb-1">{{ $student->name }}</a>
                                            @else
                                                <span class="font-weight-bold text-dark small d-block mb-1">{{ $student->name }}</span>
                                            @endif
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-shield mr-1 text-muted" style="font-size: 8px;"></i>
                                                <span class="text-muted" style="font-size: 8px; font-weight: 600;">{{ strtoupper($student->level) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell small text-muted">{{ $student->pesertaDidik->no_induk ?? '-' }}</td>
                                <td class="px-0 text-center">
                                    <span class="font-weight-bold small {{ $student->total_points >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.75rem;">
                                        {{ $student->total_points ?? 0 }}P
                                    </span>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <span class="badge badge-light border text-primary small">{{ $student->level }}</span>
                                </td>
                                <td class="text-right pr-2">
                                    @if(auth()->user()->role === 'admin')
                                    <button class="btn btn-xs btn-danger rounded-pill px-2 reset-point-btn" 
                                            data-ids="{{ $student->id }}" title="Reset Poin Hari Ini">
                                        <i class="fa fa-undo"></i>
                                    </button>
                                    @endif
                                    <button class="btn btn-xs btn-primary rounded-pill px-2 award-point-btn" 
                                            data-ids="{{ $student->id }}" 
                                            data-name="{{ $student->name }}"
                                            data-toggle="modal" data-target="#awardPointModal">
                                        <i class="fa fa-plus"></i><span class="d-none d-md-inline ml-1">Poin</span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 d-flex justify-content-center overflow-auto">
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Bulk Action Bar -->
<div id="bulkActionBar" class="position-fixed w-100" style="bottom: 20px; left: 0; z-index: 1040; display: none;">
    <div class="container">
        <div class="card bg-dark text-white shadow-lg border-0 rounded-pill mx-auto mb-0" style="max-width: 600px;">
            <div class="card-body py-2 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <span class="badge badge-primary rounded-circle mr-3" id="selectedCount" style="width: 25px; height: 25px; line-height: 25px; padding: 0;">0</span>
                    <span class="font-weight-bold small">Siswa Terpilih</span>
                </div>
                <div>
                    <button class="btn btn-sm btn-link text-white-50 mr-2" id="clearSelection">Batal</button>
                    @if(auth()->user()->role === 'admin')
                    <button class="btn btn-sm btn-danger rounded-pill px-3 mr-2" id="bulkResetBtn">
                        <i class="fa fa-undo mr-1"></i> Reset Hari Ini
                    </button>
                    @endif
                    <button class="btn btn-sm btn-primary rounded-pill px-3" id="bulkAwardBtn">
                        <i class="fa fa-plus mr-1"></i> Beri Poin Massal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Award Point -->
<div class="modal fade" id="awardPointModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title">Beri Poin Manual</h4>
            </div>
            <div class="modal-body text-center">
                <form action="{{ route('integrity.manual.give') }}" method="POST" id="awardPointForm">
                    @csrf
                    <p class="mb-4">Pilih jenis poin yang akan diberikan kepada <br><strong id="modalStudentName" class="h5"></strong></p>
                    <div id="userIdContainer">
                        <input type="hidden" name="user_ids[]" id="modalUserId">
                    </div>
                    
                    <div class="list-group">
                        @forelse($manualRules as $mRule)
                        <label class="list-group-item list-group-item-action text-left p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <input type="radio" name="rule_id" value="{{ $mRule->id }}" required class="mr-2">
                                <span class="font-weight-bold">{{ $mRule->rule_name }}</span>
                            </div>
                            <span class="badge {{ $mRule->point_modifier > 0 ? 'badge-success' : 'badge-danger' }} px-3 py-2 rounded-pill">
                                {{ $mRule->point_modifier > 0 ? '+' : '' }}{{ $mRule->point_modifier }} P
                            </span>
                        </label>
                        @empty
                        <div class="alert alert-warning">Admin belum membuat aturan manual.</div>
                        @endforelse
                    </div>

                    <div class="form-group mt-4 text-left">
                        <label class="font-weight-bold">Alasan / Keterangan Tambahan <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Contoh: Siswa membantu merapikan buku di perpustakaan..." required></textarea>
                        <small class="text-muted">Wajib diisi agar siswa mengetahui alasan pemberian poin ini.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Batal
                </button>
                <button type="submit" form="awardPointForm" class="btn btn-primary" {{ $manualRules->isEmpty() ? 'disabled' : '' }}>
                    <i class="fa fa-check mr-1"></i> Konfirmasi Poin
                </button>
            </div>
        </div>
    </div>
</div>

<x-modal-confirm />

@section('afterAppScripts')
<script>
    let globalSelectedIds = [];
    let isAllSelected = false;
    const allFilteredStudentIds = @json($allFilteredStudentIds ?? []);

    $(document).ready(function() {
        // Handle individual action button
        $(document).on('click', '.award-point-btn', function() {
            const ids = $(this).data('ids').toString().split(',');
            const name = $(this).data('name');
            setupModal(ids, name);
        });

        const bulkBar = $('#bulkActionBar');
        const countBadge = $('#selectedCount');

        // Master Select All Logic
        $(document).on('change', '#selectAllStudents', function() {
            const isChecked = $(this).prop('checked');
            $('.student-checkbox').prop('checked', isChecked);
            
            globalSelectedIds = [];
            if(isChecked) {
                $('.student-checkbox:checked').each(function() {
                    globalSelectedIds.push($(this).val());
                });
                
                if(allFilteredStudentIds.length > $('.student-checkbox').length) {
                    $('#selectAllPromptRow').show();
                    $('#selectAllPromptText').show();
                    $('#btnSelectAllFiltered').show();
                    $('#allSelectedText').hide();
                }
            } else {
                $('#selectAllPromptRow').hide();
                isAllSelected = false;
            }
            updateBulkBar();
        });

        $(document).on('click', '#btnSelectAllFiltered', function(e) {
            e.preventDefault();
            globalSelectedIds = [...allFilteredStudentIds];
            isAllSelected = true;
            $('#selectAllPromptText').hide();
            $(this).hide();
            $('#allSelectedText').show();
            updateBulkBar();
        });

        $(document).on('click', '#btnClearGlobalSelection', function(e) {
            e.preventDefault();
            $('#selectAllStudents').prop('checked', false).trigger('change');
        });

        // Individual Checkbox Logic
        $(document).on('change', '.student-checkbox', function() {
            const id = String($(this).val());
            if($(this).is(':checked')) {
                if(!globalSelectedIds.includes(id)) globalSelectedIds.push(id);
                // Check master if all are checked
                if($('.student-checkbox:checked').length === $('.student-checkbox').length) {
                    $('#selectAllStudents').prop('checked', true);
                }
            } else {
                globalSelectedIds = globalSelectedIds.filter(val => val !== id);
                if(isAllSelected) {
                    isAllSelected = false;
                    $('#selectAllPromptRow').hide();
                }
                $('#selectAllStudents').prop('checked', false);
            }
            updateBulkBar();
        });

        function updateBulkBar() {
            if (globalSelectedIds.length > 0) {
                countBadge.text(globalSelectedIds.length);
                bulkBar.fadeIn();
            } else {
                bulkBar.fadeOut();
            }
        }

        // Clear Selection
        $(document).on('click', '#clearSelection', function(e) {
            e.preventDefault();
            $('#selectAllStudents').prop('checked', false).trigger('change');
        });

        // Bulk Award Button Click
        $(document).on('click', '#bulkAwardBtn', function() {
            if(globalSelectedIds.length === 0) return;
            setupModal(globalSelectedIds, globalSelectedIds.length + ' Siswa Terpilih');
            $('#awardPointModal').modal('show');
        });

        // Individual Reset Button Click
        $(document).on('click', '.reset-point-btn', function() {
            const ids = $(this).data('ids').toString().split(',');
            showResetConfirmModal(ids, 'siswa ini');
        });

        // Bulk Reset Button Click
        $(document).on('click', '#bulkResetBtn', function() {
            if(globalSelectedIds.length === 0) return;
            showResetConfirmModal(globalSelectedIds, globalSelectedIds.length + ' siswa terpilih');
        });

        function showResetConfirmModal(ids, targetText) {
            $('#confirmModalTitle').text('Konfirmasi Reset Poin');
            $('#confirmModalMessage').text('Yakin ingin mereset/menghapus semua mutasi poin HARI INI untuk ' + targetText + '?');
            $('#confirmModalItemName').text('');
            $('#confirmForm').attr('action', "{{ route('integrity.manual.reset_today') }}");
            
            $('#confirmForm').find('.dynamic-ids').remove();
            ids.forEach(id => {
                $('#confirmForm').append(`<input type="hidden" name="user_ids[]" value="${id}" class="dynamic-ids">`);
            });

            const submitBtn = $('#confirmModalSubmitBtn');
            submitBtn.attr('class', 'btn btn-danger');
            $('#confirmModalSubmitText').text('Reset Poin');
            $('#confirmModalIcon').attr('class', 'fa mr-1 fa-undo');

            $('#confirmModal').modal('show');
        }

        function setupModal(ids, nameDisplay) {
            $('#modalStudentName').text(nameDisplay);
            
            // Clear and refill hidden inputs
            const container = $('#userIdContainer');
            container.empty();
            ids.forEach(id => {
                container.append(`<input type="hidden" name="user_ids[]" value="${id}">`);
            });
        }
    });

    function updateQueryStringParameter(uri, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            return uri + separator + key + "=" + value;
        }
    }
</script>
@endsection

@endsection
