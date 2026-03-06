@extends('layouts.app')

@section('title', 'Laporan Absensi Harian')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card shadow-none border">
                <div class="header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="m-0 font-weight-bold">Laporan Absensi Harian</h2>
                        <small class="text-muted">Rekapitulasi kehadiran harian per kelas (Scan Pagi)</small>
                    </div>
                    <div class="header-action">
                        <div class="btn-group shadow-none">
                            <a target="_blank"
                                href="{{ $rombel ? route('laporan.absensi.kelas.pdf', request()->query()) : 'javascript:void(0)' }}"
                                class="btn btn-sm btn-outline-danger {{ !$rombel ? 'disabled text-muted' : '' }}"
                                title="{{ $rombel ? 'Export PDF' : 'Pilih kelas terlebih dahulu' }}">
                                <i class="fa fa-file-pdf-o"></i> <span class="d-none d-sm-inline ml-1">PDF</span>
                            </a>
                            <a href="{{ $rombel ? route('laporan.absensi.kelas.excel', request()->query()) : 'javascript:void(0)' }}"
                                class="btn btn-sm btn-outline-success {{ !$rombel ? 'disabled text-muted' : '' }}"
                                title="{{ $rombel ? 'Export Excel' : 'Pilih kelas terlebih dahulu' }}">
                                <i class="fa fa-file-excel-o"></i> <span class="d-none d-sm-inline ml-1">Excel</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="body">
                    <form method="GET" id="filter-form" class="mb-3">
                        <div class="row align-items-end no-gutters mx-n1">
                            @if(auth()->user()->role === 'admin')
                                <div class="col-lg-3 col-md-4 px-1 mb-2">
                                    <label class="small text-muted mb-1 font-weight-bold">Guru Wali Kelas</label>
                                    <select name="teacher_id" class="form-control form-control-sm ajax-filter">
                                        <option value="">-- Semua Walas --</option>
                                        @foreach($teachers as $t)
                                            <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>
                                                {{ $t->nama_lengkap }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div
                                class="{{ auth()->user()->role === 'admin' ? 'col-lg-3 col-md-4' : 'col-lg-4 col-md-6' }} px-1 mb-2">
                                <label class="small text-muted mb-1 font-weight-bold">Kelas</label>
                                <select name="rombel_id" id="rombel_id" class="form-control form-control-sm ajax-filter"
                                    required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($rombels as $r)
                                        <option value="{{ $r->id }}" {{ (request('rombel_id') == $r->id || (isset($rombel) && $rombel->id == $r->id)) ? 'selected' : '' }}>
                                            {{ $r->nama_rombel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-4 px-1 mb-2">
                                <label class="small text-muted mb-1 font-weight-bold">Dari</label>
                                <input type="date" name="start_date" class="form-control form-control-sm ajax-filter"
                                    value="{{ $start }}">
                            </div>

                            <div class="col-lg-2 col-md-4 px-1 mb-2">
                                <label class="small text-muted mb-1 font-weight-bold">Sampai</label>
                                <input type="date" name="end_date" class="form-control form-control-sm ajax-filter"
                                    value="{{ $end }}">
                            </div>

                            <div class="col-lg-1 col-md-2 px-1 mb-2">
                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div id="report-results">
                        @if($rombel)
                            <div class="alert alert-info py-2 shadow-none border mb-4">
                                <h5 class="m-0 font-weight-bold">Kelas: {{ $rombel->nama_rombel }}</h5>
                                <p class="m-0 small text-dark">Wali Kelas: {{ $rombel->waliKelas->user->name ?? '-' }}</p>
                                <p class="m-0 small text-muted">Periode:
                                    {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} -
                                    {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}
                                </p>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover mt-3 js-basic-example dataTable">
                                    <thead>
                                        <tr>
                                            <th>Nama Siswa</th>
                                            <th class="text-center">Hadir</th>
                                            <th class="text-center">Izin</th>
                                            <th class="text-center">Sakit</th>
                                            <th class="text-center">Alpha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data as $row)
                                            <tr>
                                                <td class="font-weight-bold text-dark">{{ $row['nama'] }}</td>
                                                <td class="text-center">
                                                    <span class="text-success font-weight-bold">{{ $row['hadir'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="text-secondary">{{ $row['izin'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="text-info">{{ $row['sakit'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="text-danger font-weight-bold">{{ $row['alpha'] }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted italic">Data tidak ditemukan
                                                    untuk periode ini</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-file-text-o fa-4x text-light mb-3"></i>
                                <p class="mt-3 text-muted">Silakan pilih kelas untuk melihat laporan harian.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const filterForm = $('#filter-form');
            const resultsContainer = $('#report-results');
            const rombelSelect = $('#rombel_id');

            function updateResults() {
                if (!rombelSelect.val()) return;
                const formData = filterForm.serialize();
                const url = window.location.pathname + '?' + formData;
                window.history.pushState({}, '', url);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        const newContent = $(response).find('#report-results').html();
                        resultsContainer.html(newContent);
                        $('.header-action').html($(response).find('.header-action').html());
                    }
                });
            }

            $('.ajax-filter').on('change', function () { updateResults(); });
        });
    </script>
@endpush