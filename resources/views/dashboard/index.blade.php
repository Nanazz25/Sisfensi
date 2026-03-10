@extends('layouts.app')

@section('title', 'Dashboard')

@section('afterAppStyles')
    {{-- Custom styles already loaded globally via layout --}}
@endsection

@section('content')
    <div class="row clearfix row-deck">
        <div class="col-lg-12">
            <div class="card welcome-card bg-gradient-primary text-white border-0 shadow-sm mb-4">
                <div class="body d-flex align-items-center py-4 justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-white-20 rounded-circle text-center mr-3"
                            style="width: 60px; height: 60px; line-height: 60px;">
                            <i class="fa fa-smile-o font-30"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 font-weight-bold">Selamat Datang, {{ Auth::user()->name }}!</h4>
                            <p class="mb-0 opacity-75"><i class="fa fa-calendar-o mr-1"></i>
                                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                        </div>
                    </div>
                    @php 
                        $pendingCount = 0;
                        if(auth()->user()->role === 'admin') {
                            $pendingCount = $stats['pending_permissions'] ?? 0;
                        } elseif(auth()->user()->role === 'guru') {
                            $pendingCount = $walas_data['pending_izin'] ?? 0;
                        }
                    @endphp

                    @if($pendingCount > 0)
                        <a href="{{ route('attendance-permissions.index') }}" class="btn btn-warning shadow-sm pulse py-2 px-3 rounded-pill font-weight-bold text-white">
                            <i class="fa fa-warning mr-1"></i> <span class="d-none d-md-inline">{{ $pendingCount }} Menunggu Verifikasi</span>
                            <span class="d-md-none">{{ $pendingCount }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
            @include('dashboard.partials.admin')
        @elseif(auth()->user()->role === 'guru')
            @include('dashboard.partials.guru')
        @elseif(auth()->user()->role === 'siswa')
            @include('dashboard.partials.siswa')
        @endif
    </div>

    @if(auth()->user()->role === 'guru')
        <!-- Modal Detail Presensi -->
        <div class="modal fade" id="attendanceDetailModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Detail Presensi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modalMeta" class="mb-3">
                            <p class="mb-0">Mapel: <strong id="modalSubject">-</strong></p>
                            <p class="mb-0">Kelas: <strong id="modalClass">-</strong></p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom spacing5 mb-0">
                                <thead>
                                    <tr>
                                        <th>Nama Siswa</th>
                                        <th width="120">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceList">
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('afterAppScripts')
    <script>
        $(function () {
            $('.view-attendance-detail').on('click', function () {
                var scheduleId = $(this).data('id');
                var $modal = $('#attendanceDetailModal');

                $('#attendanceList').html('<tr><td colspan="2" class="text-center"><i class="fa fa-spinner fa-spin mr-1"></i> Memuat data...</td></tr>');
                $modal.modal('show');

                $.get('/dashboard/attendance-detail/' + scheduleId, function (res) {
                    $('#modalSubject').text(res.subject);
                    $('#modalClass').text(res.class);

                    var html = '';
                    if (res.data.length > 0) {
                        res.data.forEach(function (item) {
                            html += `<tr>
                                                            <td><strong>${item.name}</strong></td>
                                                            <td>${item.pills}</td>
                                                        </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="2" class="text-center">Tidak ada data siswa.</td></tr>';
                    }
                    $('#attendanceList').html(html);
                });
            });
        });
    </script>
    <script>
        // Simple GPS check for dashboard
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const gpsEl = document.getElementById('miniGpsStatus');
                if (gpsEl) {
                    gpsEl.innerHTML = '<i class="fa fa-check-circle text-success mr-1"></i> Lokasi Terdeteksi';
                    gpsEl.classList.add('text-success');
                }
            });
        }
    </script>
@endsection