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
                    <form method="GET" id="filter-form" class="mb-4 bg-light p-3 rounded-lg border">
                        <div class="row mx-n2">
                            <!-- Educational Filter -->
                            <div class="col-lg-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="small font-weight-bold text-uppercase text-primary m-0" style="letter-spacing: 1px;">Konteks Akademik</h6>
                                    <a href="{{ route('laporan.absensi.kelas') }}" class="btn btn-sm btn-outline-secondary py-0" style="font-size: 10px;">
                                        <i class="fa fa-undo mr-1"></i> Reset Filter
                                    </a>
                                </div>
                                <hr class="my-2">
                            </div>

                            @if(auth()->user()->role === 'admin')
                                <div class="col-lg-4 col-md-6 px-2 mb-3">
                                    <label class="small text-muted mb-1">Wali Kelas</label>
                                    <select name="teacher_id" class="form-control select2 ajax-filter">
                                        <option value="">-- Semua Wali Kelas --</option>
                                        @foreach($teachers as $t)
                                            <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>
                                                {{ $t->nama_lengkap }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-lg-4 col-md-6 px-2 mb-3">
                                <label class="small text-muted mb-1">Tahun Ajaran</label>
                                <select name="tahun_ajar_id" class="form-control ajax-filter">
                                    <option value="">-- Semua Tahun Ajar --</option>
                                    @foreach($tahunAjars as $ta)
                                        <option value="{{ $ta->id }}" {{ request('tahun_ajar_id') == $ta->id ? 'selected' : '' }}>
                                            {{ $ta->nama }} ({{ ucfirst($ta->semester) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-4 col-md-6 px-2 mb-3">
                                <label class="small text-muted mb-1 font-weight-bold">Pilih Kelas <span
                                        class="text-danger">*</span></label>
                                <select name="rombel_id" id="rombel_id" class="form-control border-primary ajax-filter"
                                    required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($rombels as $r)
                                        <option value="{{ $r->id }}" {{ (request('rombel_id') == $r->id || (isset($rombel) && $rombel->id == $r->id)) ? 'selected' : '' }}>
                                            {{ $r->nama_rombel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Timing Filter -->
                            <div class="col-lg-12 mb-3 mt-2">
                                <h6 class="small font-weight-bold text-uppercase text-secondary m-0"
                                    style="letter-spacing: 1px;">Periode Waktu</h6>
                                <hr class="my-2">
                            </div>

                            <div class="col-lg-3 col-md-6 px-2 mb-3">
                                <label class="small text-muted mb-1 text-info font-weight-bold">Bulan (Opsional)</label>
                                <select name="month" class="form-control ajax-filter">
                                    <option value="">-- Pilih Bulan --</option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-3 col-md-6 px-2 mb-3">
                                <label class="small text-muted mb-1 text-info font-weight-bold">Tahun (Opsional)</label>
                                <select name="year" class="form-control ajax-filter">
                                    <option value="">-- Pilih Tahun --</option>
                                    @foreach(range(date('Y'), date('Y') - 5) as $y)
                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-3 col-md-6 px-2 mb-3">
                                <label class="small text-muted mb-1">Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control ajax-filter" value="{{ $start }}">
                            </div>

                            <div class="col-lg-3 col-md-6 px-2 mb-3">
                                <label class="small text-muted mb-1">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control ajax-filter" value="{{ $end }}">
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
                const formData = filterForm.serialize();
                const url = window.location.pathname + '?' + formData;
                window.history.pushState({}, '', url);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        const htmlDoc = $(response);

                        // 1. Update Rombel Select if it exists
                        const newRombelSelect = htmlDoc.find('#rombel_id').html();
                        const oldVal = rombelSelect.val();
                        rombelSelect.html(newRombelSelect);

                        // Re-select if it still exists in the list
                        if (htmlDoc.find(`#rombel_id option[value="${oldVal}"]`).length > 0) {
                            rombelSelect.val(oldVal);
                        }

                        // 2. Update results container
                        const newContent = htmlDoc.find('#report-results').html();
                        resultsContainer.html(newContent);

                        // 3. Update export buttons
                        $('.header-action').html(htmlDoc.find('.header-action').html());
                    }
                });
            }

            $('.ajax-filter').on('change', function () {
                updateResults();
            });
        });
    </script>
@endpush