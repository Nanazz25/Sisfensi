@extends('layouts.app')

@section('title', 'Penilaian Massal')

@section('afterAppStyles')
    <!-- Font Awesome 5 for stars -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    @vite('resources/css/assessment/create.css')
@endsection

@section('content')
<div class="row clearfix">
    <div class="col-lg-12">
        <form id="assessmentForm" action="{{ route('assessment.store') }}" method="POST">
            @csrf
            
            <!-- STEP 1: PILIH SUBJEK -->
            <div id="step1" class="card">
                <div class="header">
                    <div class="step-header active">
                        <div class="step-circle">1</div>
                        <h2 class="mb-0">Pilih {{ Auth::user()->role === 'admin' ? 'Guru' : 'Siswa' }}</h2>
                    </div>
                </div>
                <div class="body">
                    <div class="mb-3 d-flex justify-content-between">
                        <div class="input-group width250">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                            </div>
                            <input type="text" id="subjectSearch" class="form-control" placeholder="Cari nama...">
                        </div>
                        <span class="badge badge-info p-2 mt-1">Total Terpilih: <span id="selectedCounter">0</span> orang</span>
                    </div>
                    
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <label class="fancy-checkbox">
                                            <input type="checkbox" id="selectAllEvaluatees">
                                            <span></span>
                                        </label>
                                    </th>
                                    <th>Nama</th>
                                    <th>ID / NIP</th>
                                </tr>
                            </thead>
                            <tbody id="subjectList">
                                @foreach($evaluatees as $ev)
                                <tr class="subject-row">
                                    <td>
                                        <label class="fancy-checkbox">
                                            <input type="checkbox" name="evaluatee_ids[]" value="{{ $ev->id }}" class="ev-checkbox">
                                            <span></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm rounded-circle shadow-sm border border-2 border-white overflow-hidden" style="width: 35px; height: 35px; background: #fff;">
                                                <img src="{{ $ev->avatar_url }}" alt="{{ $ev->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                            <div class="ml-2">
                                                <span class="subject-name font-weight-bold">{{ $ev->name }}</span><br>
                                                <small class="text-muted">{{ $ev->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($ev->role === 'guru')
                                            {{ $ev->teacher->nip ?? '-' }}
                                        @else
                                            {{ $ev->pesertaDidik->no_induk ?? '-' }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="footer p-3 border-top text-right">
                    <button type="button" class="btn btn-primary" onclick="goToStep(2)">Lanjutkan Ke Penilaian <i class="fa fa-arrow-right ml-1"></i></button>
                </div>
            </div>

            <!-- STEP 2: PENILAIAN -->
            <div id="step2" class="card d-none">
                <div class="header d-flex justify-content-between">
                    <div class="step-header active">
                        <div class="step-circle">2</div>
                        <h2 class="mb-0">Transaksi Penilaian</h2>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToStep(1)">
                        <i class="fa fa-arrow-left mr-1"></i> Ganti Orang
                    </button>
                </div>
                <div class="body">
                    <div id="selectedBadges" class="mb-3 d-flex flex-wrap gap-2">
                        <!-- Badges will appear here -->
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Tanggal Penilaian</label>
                                <input type="date" name="assessment_date" class="form-control bg-light" value="{{ date('Y-m-d') }}" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Periode</label>
                                <select name="period" class="form-control" required>
                                    @foreach($periods as $p)
                                        <option value="{{ $p }}" {{ str_contains($p, 'Mingguan') ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="category-list-wrapper mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 font-weight-bold text-dark"><i class="fa fa-list-ul mr-2 text-info"></i> Indikator Karakter</h5>
                            <div class="input-group" style="width: 250px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                </div>
                                <input type="text" id="catSearch" class="form-control" placeholder="Cari kategori...">
                            </div>
                        </div>
                        
                        <div id="categoryContainer" style="max-height: 500px; overflow-y: auto; padding-right: 5px;">
                            @foreach($categories as $cat)
                            <div class="category-item p-3 mb-2 rounded bg-white shadow-xs border" data-name="{{ strtolower($cat->name) }}">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <h6 class="mb-1 font-weight-bold text-dark">{{ $cat->name }}</h6>
                                        <small class="text-muted d-block">{{ $cat->description }}</small>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="star-rating" data-category-id="{{ $cat->id }}">
                                            @for($i=1; $i<=5; $i++)
                                                <span class="star-item" data-star="{{ $i }}">
                                                    <i class="fas fa-star"></i>
                                                </span>
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-right">
                                        <input type="hidden" name="scores[{{ $cat->id }}]" class="score-input" value="0">
                                        <div class="h5 mb-0 font-weight-bold text-warning">
                                            <span class="score-display">0</span> <small class="text-muted">/ 10</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <label>Catatan Umum / Feedback</label>
                        <textarea name="general_notes" class="form-control" rows="3" placeholder="Masukkan catatan opsional..."></textarea>
                    </div>
                </div>
                <div class="footer p-3 border-top text-right">
                    <button type="button" class="btn btn-success btn-lg" onclick="showConfirmModal()">
                        <i class="fa fa-check-circle mr-1"></i> Selesaikan Penilaian
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Simpan</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center p-4">
                <i class="fa fa-question-circle fa-4x text-info mb-3"></i>
                <h5 class="font-weight-bold">Simpan Penilaian Massal?</h5>
                <p class="mb-1">Anda akan menyimpan penilaian untuk:</p>
                <div class="mb-3">
                    <span class="badge badge-pill badge-primary px-3 py-2" style="font-size: 14px;">
                        <span id="cfmCount">0</span> Orang
                    </span>
                    <i class="fa fa-arrow-right mx-2 text-muted"></i>
                    <span class="badge badge-pill badge-info px-3 py-2" style="font-size: 14px;">
                        <span id="cfmCatCount">0</span> Indikator
                    </span>
                </div>
                <p class="text-muted small px-3">Data nilai akan tercatat secara individual untuk setiap orang dan indikator yang dipilih.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitAssessment()">Ya, Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('afterAppScripts')
    @vite('resources/js/assessment/create.js')
@endsection
