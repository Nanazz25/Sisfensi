@extends('layouts.app')

@section('title', 'Laporan Absensi Mata Pelajaran')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card shadow-none border">
                <div class="header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="m-0 font-weight-bold">Laporan Absensi Mata Pelajaran</h2>
                        <small class="text-muted">Pantau kehadiran siswa berdasarkan mata pelajaran</small>
                    </div>
                    <div class="btn-group shadow-none">
                        <a target="_blank"
                            href="{{ $rombel ? route('laporan.absensi.mapel.pdf', request()->query()) : 'javascript:void(0)' }}"
                            class="btn btn-sm btn-outline-danger {{ !$rombel ? 'disabled text-muted' : '' }}"
                            title="{{ $rombel ? 'Export PDF' : 'Pilih kelas terlebih dahulu' }}">
                            <i class="fa fa-file-pdf-o"></i> <span class="d-none d-sm-inline ml-1">PDF</span>
                        </a>
                        <a href="{{ $rombel ? route('laporan.absensi.mapel.excel', request()->query()) : 'javascript:void(0)' }}"
                            class="btn btn-sm btn-outline-success {{ !$rombel ? 'disabled text-muted' : '' }}"
                            title="{{ $rombel ? 'Export Excel' : 'Pilih kelas terlebih dahulu' }}">
                            <i class="fa fa-file-excel-o"></i> <span class="d-none d-sm-inline ml-1">Excel</span>
                        </a>
                    </div>
                </div>

                <div class="body">
                    <form method="GET" id="filter-form" class="mb-3">
                        <div class="row align-items-end no-gutters mx-n1">
                            @if(auth()->user()->role === 'admin')
                                <div class="col-lg-3 col-md-4 px-1 mb-2">
                                    <label class="small text-muted mb-1 font-weight-bold">Guru Pengajar</label>
                                    <select name="teacher_id" class="form-control form-control-sm ajax-filter">
                                        <option value="">-- Semua Guru --</option>
                                        @foreach($teachers as $t)
                                            <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>
                                                {{ $t->nama_lengkap }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div
                                class="{{ auth()->user()->role === 'admin' ? 'col-lg-2 col-md-4' : 'col-lg-3 col-md-4' }} px-1 mb-2">
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

                            <div
                                class="{{ auth()->user()->role === 'admin' ? 'col-lg-2 col-md-4' : 'col-lg-3 col-md-4' }} px-1 mb-2">
                                <label class="small text-muted mb-1 font-weight-bold">Mata Pelajaran</label>
                                <select name="subject_id" class="form-control form-control-sm ajax-filter">
                                    <option value="">-- Semua Mapel --</option>
                                    @foreach($subjects as $s)
                                        <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>
                                            {{ $s->nama_mapel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-3 px-1 mb-2">
                                <label class="small text-muted mb-1 font-weight-bold">Dari</label>
                                <input type="date" name="start_date" class="form-control form-control-sm ajax-filter"
                                    value="{{ $start }}">
                            </div>

                            <div class="col-lg-2 col-md-3 px-1 mb-2">
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
                                <p class="m-0 small text-dark">Periode:
                                    {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} -
                                    {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}
                                </p>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover js-basic-example dataTable">
                                    <thead>
                                        <tr>
                                            <th>Hari / Tanggal</th>
                                            <th>Mata Pelajaran</th>
                                            @if(auth()->user()->role === 'admin')
                                                <th>Guru Pengajar</th>
                                            @endif
                                            <th class="text-center">Kehadiran</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data as $row)
                                            <tr>
                                                <td>
                                                    <span
                                                        class="d-block font-weight-bold">{{ $row->tanggal->translatedFormat('l') }}</span>
                                                    <small class="text-muted">{{ $row->tanggal->translatedFormat('d F Y') }}</small>
                                                </td>
                                                <td class="font-weight-bold text-primary">{{ $row->subject_name }}</td>
                                                @if(auth()->user()->role === 'admin')
                                                    <td class="small">{{ $row->teacher_name }}</td>
                                                @endif
                                                <td class="text-center">
                                                    <span class="font-weight-bold">{{ $row->hadir_summary }}</span>
                                                    <div class="progress w-100 mt-1 mx-auto" style="height: 5px; max-width: 80px;">
                                                        @php
                                                            $parts = explode(' / ', $row->hadir_summary);
                                                            $percent = $parts[1] > 0 ? ($parts[0] / $parts[1]) * 100 : 0;
                                                        @endphp
                                                        <div class="progress-bar bg-info" style="width: {{ $percent }}%"></div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-info btn-detail-mapel"
                                                        data-schedule-id="{{ $row->schedule_id }}"
                                                        data-tanggal="{{ $row->tanggal->toDateString() }}">
                                                        <i class="fa fa-eye"></i> Detail
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted font-italic">Data belum tersedia
                                                    untuk periode ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-calendar-o fa-4x text-light mb-3"></i>
                                <p class="text-muted">Silakan pilih kelas untuk memuat laporan mata pelajaran.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Absensi Mapel -->
    <div class="modal fade" id="modalDetailMapel" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content shadow-none border">
                <div class="modal-header bg-info py-2">
                    <h5 class="modal-title font-weight-bold text-white small" id="detail-title">Detail Absensi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-3 bg-light border-bottom small">
                        <div class="row mb-1">
                            <div class="col-6">
                                <span class="text-muted d-block uppercase tracking-wider">Mapel</span>
                                <strong class="text-dark" id="detail-subject">-</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block uppercase tracking-wider">Kelas</span>
                                <strong class="text-dark" id="detail-class">-</strong>
                            </div>
                        </div>
                        <div class="mt-1">
                            <span class="text-muted d-block uppercase tracking-wider">Tanggal</span>
                            <strong class="text-dark" id="detail-date">-</strong>
                        </div>
                    </div>
                    <div id="detail-loading" class="text-center py-5 d-none">
                        <div class="spinner-border text-info" role="status"></div>
                        <p class="mt-2 text-muted">Memuat ...</p>
                    </div>
                    <div class="table-responsive" style="max-height: 450px;">
                        <table class="table table-hover table-sm">
                            <thead class="bg-white sticky-top shadow-none border-bottom">
                                <tr>
                                    <th class="px-3 border-0">Nama Siswa</th>
                                    <th class="text-center border-0">Scan</th>
                                    <th class="text-right px-3 border-0">Status</th>
                                </tr>
                            </thead>
                            <tbody id="detail-student-list">
                                <!-- Ajax Content -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
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
                        $('.btn-group').html($(response).find('.btn-group').html());
                    }
                });
            }

            $('.ajax-filter').on('change', function () { updateResults(); });

            $(document).on('click', '.btn-detail-mapel', function () {
                const scheduleId = $(this).data('schedule-id');
                const tanggal = $(this).data('tanggal');
                const modal = $('#modalDetailMapel');
                $('#detail-student-list').html('');
                $('#detail-loading').removeClass('d-none');
                modal.modal('show');

                $.ajax({
                    url: "{{ route('laporan.absensi.mapel.detail') }}",
                    type: 'GET',
                    data: { schedule_id: scheduleId, tanggal: tanggal },
                    success: function (response) {
                        $('#detail-loading').addClass('d-none');
                        $('#detail-subject').text(response.subject);
                        $('#detail-class').text(response.class);
                        $('#detail-date').text(response.date);
                        let html = '';
                        response.data.forEach(function (s) {
                            html += `<tr><td class="px-3 py-2 font-weight-bold text-dark">${s.name}</td><td class="py-2 text-center text-muted small">${s.waktu}</td><td class="px-3 py-2 text-right">${s.pills}</td></tr>`;
                        });
                        $('#detail-student-list').html(html);
                    }
                });
            });
        });
    </script>
@endpush