{{-- SUMMARY CARDS (Student-Style Icons) --}}
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-info text-white text-center mr-3 shadow-sm">
                <i class="fa fa-users"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Total Siswa</small>
                <h5 class="mb-0 font-weight-bold text-dark">{{ $stats['total_siswa'] }}</h5>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-primary text-white text-center mr-3 shadow-sm">
                <i class="fa fa-graduation-cap"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Total Guru</small>
                <h5 class="mb-0 font-weight-bold text-dark">{{ $stats['total_guru'] }}</h5>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-warning text-white text-center mr-3 shadow-sm">
                <i class="fa fa-clock-o"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Pending Izin</small>
                <h5 class="mb-0 font-weight-bold text-dark">{{ $stats['pending_permissions'] }}</h5>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-dark text-white text-center mr-3 shadow-sm">
                <i class="fa fa-university"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Total Rombel</small>
                <h5 class="mb-0 font-weight-bold text-dark">{{ $stats['total_rombel'] }}</h5>
            </div>
        </div>
    </div>
</div>

{{-- ATTENDANCE SUMMARY --}}
<div class="col-lg-12">
    <div class="card shadow-sm border-0">
        <div class="header d-flex justify-content-between align-items-center pb-0">
            <h2 class="font-weight-bold">Status Kehadiran Hari Ini</h2>
            <div class="d-flex align-items-center">
                <span class="badge badge-soft-warning mr-2 shadow-xs" title="Total Penilaian Hari Ini">
                    <i class="fa fa-check-square-o mr-1"></i>{{ $stats['total_assessment_today'] }} Dinilai
                </span>
                <span class="badge badge-soft-info shadow-xs">
                    <i class="fa fa-clock-o mr-1"></i>{{ date('H:i') }}
                </span>
            </div>
        </div>
        <div class="body pt-3">
            <div class="row text-center mx-0">
                <div class="col-lg-3 col-6 px-1 mb-2">
                    <div class="p-3 rounded bg-light shadow-xs">
                        <small class="mb-0 text-muted small d-block font-weight-bold">HADIR</small>
                        <h4 class="font-weight-bold text-success mb-0">{{ $stats['absensi_hari_ini']['hadir'] }}</h4>
                    </div>
                </div>
                <div class="col-lg-3 col-6 px-1 mb-2">
                    <div class="p-3 rounded bg-light shadow-xs">
                        <small class="mb-0 text-muted small d-block font-weight-bold">TELAT</small>
                        <h4 class="font-weight-bold text-warning mb-0">{{ $stats['absensi_hari_ini']['terlambat'] }}
                        </h4>
                    </div>
                </div>
                <div class="col-lg-3 col-6 px-1 mb-2">
                    <div class="p-3 rounded bg-light shadow-xs">
                        <small class="mb-0 text-muted small d-block font-weight-bold">IZIN/SAKIT</small>
                        <h4 class="font-weight-bold text-info mb-0">{{ $stats['absensi_hari_ini']['izin_sakit'] }}</h4>
                    </div>
                </div>
                <div class="col-lg-3 col-6 px-1 mb-2">
                    <div class="p-3 rounded bg-light shadow-xs">
                        <small class="mb-0 text-muted small d-block font-weight-bold">ALPHA</small>
                        <h4 class="font-weight-bold text-danger mb-0">{{ $stats['absensi_hari_ini']['alpha'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ATTENDANCE CHART & MENU --}}
<div class="col-lg-12">
    <div class="card shadow-sm border-0">
        <div class="header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <h2 class="font-weight-bold mb-3 mb-md-0">Statistik Kehadiran</h2>

            <div class="d-flex flex-column align-items-md-end" style="gap: 5px;">
                <div class="d-flex flex-wrap align-items-center justify-content-md-end" style="gap: 10px;">
                    <!-- Filter Type -->
                    <select id="chartFilterType" class="form-control form-control-sm" style="width: auto;">
                        <option value="all">Semua Data</option>
                        <option value="rombel">Per Kelas</option>
                        <option value="angkatan">Per Angkatan</option>
                        <option value="jurusan">Per Jurusan</option>
                    </select>

                    <!-- Dynamic Filter Value -->
                    <select id="chartFilterValue" class="form-control form-control-sm d-none" style="width: auto;">
                    </select>

                    <!-- Period Filter -->
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary btn-period active"
                            data-period="day">Hari</button>
                        <button type="button" class="btn btn-outline-primary btn-period" data-period="week">Minggu</button>
                        <button type="button" class="btn btn-outline-primary btn-period" data-period="month">Bulan</button>
                        <button type="button" class="btn btn-outline-primary btn-period" data-period="year">Tahun</button>
                        <button type="button" class="btn btn-outline-primary btn-period" data-period="custom">Kustom</button>
                    </div>
                </div>
                <div id="customDateContainer" class="d-none flex-wrap align-items-center justify-content-md-end mt-2" style="gap: 10px;">
                    <div class="d-flex align-items-center" style="gap: 5px;">
                        <input type="date" id="customStartDate" class="form-control form-control-sm" style="width: 140px;">
                        <span class="text-muted small">sampai</span>
                        <input type="date" id="customEndDate" class="form-control form-control-sm" style="width: 140px;">
                    </div>
                    <button type="button" id="btnApplyCustom" class="btn btn-sm btn-primary">Terapkan</button>
                </div>
            </div>
        </div>
        <div class="body pt-0">
            <div class="row">
                <div class="col-lg-9 border-right">
                    <div id="chartLoader" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted small">Memuat Data...</p>
                    </div>
                    <div id="chartNoData" class="text-center py-5 d-none">
                        <i class="fa fa-bar-chart fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Tidak ada data presensi pada periode ini.</p>
                    </div>
                    <div id="chartContainer" style="height: 350px;">
                        <canvas id="mainAttendanceChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-3">
                    <h6 class="font-weight-bold small text-muted text-uppercase mb-3 mt-3 mt-lg-0">Aksi Cepat</h6>
                    <div class="list-group list-group-custom">
                        <a href="{{ route('laporan.absensi.kelas') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center border-0 py-3 mb-2 bg-light rounded shadow-xs">
                            <div class="icon-in-bg bg-info text-white rounded mr-3 shadow-sm"
                                style="width: 35px; height: 35px; line-height:35px; text-align:center;"><i
                                    class="fa fa-file-text-o"></i></div>
                            <span class="font-weight-bold small">Laporan Kelas</span>
                        </a>
                        <a href="{{ route('laporan.absensi.mapel') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center border-0 py-3 mb-2 bg-light rounded shadow-xs">
                            <div class="icon-in-bg bg-primary text-white rounded mr-3 shadow-sm"
                                style="width: 35px; height: 35px; line-height:35px; text-align:center;"><i
                                    class="fa fa-book"></i></div>
                            <span class="font-weight-bold small">Laporan Mapel</span>
                        </a>
                        <a href="{{ route('assessment.index') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center border-0 py-3 mb-2 bg-light rounded shadow-xs">
                            <div class="icon-in-bg bg-warning text-white rounded mr-3 shadow-sm"
                                style="width: 35px; height: 35px; line-height:35px; text-align:center;"><i
                                    class="fa fa-star"></i></div>
                            <span class="font-weight-bold small">Penilaian Karakter</span>
                        </a>
                        <a href="{{ route('users.siswa') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center border-0 py-3 mb-2 bg-light rounded shadow-xs">
                            <div class="icon-in-bg bg-success text-white rounded mr-3 shadow-sm"
                                style="width: 35px; height: 35px; line-height:35px; text-align:center;"><i
                                    class="fa fa-users"></i></div>
                            <span class="font-weight-bold small">Kelola Siswa</span>
                        </a>
                    </div>

                    <div class="mt-4 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold small text-muted text-uppercase mb-0">Ranking Presensi</h6>
                            <small class="text-muted">Bulan Ini</small>
                        </div>
                        @foreach($top_classes as $tc)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small font-weight-bold">{{ $tc['nama'] }}</span>
                                    <span class="small text-muted">{{ $tc['percentage'] }}%</span>
                                </div>
                                <div class="progress progress-xs shadow-xs">
                                    <div class="progress-bar bg-{{ $tc['percentage'] > 90 ? 'success' : ($tc['percentage'] > 70 ? 'info' : 'warning') }}"
                                        style="width: {{ $tc['percentage'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold small text-muted text-uppercase mb-0">Rata-rata Karakter</h6>
                            <small class="text-muted font-weight-bold text-info"><i class="fa fa-star"></i></small>
                        </div>
                        @forelse($stats['top_indicators'] as $ti)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small font-weight-bold">{{ $ti['name'] }}</span>
                                    <span class="small text-dark font-weight-bold">{{ $ti['score'] }}<small class="text-muted">/10</small></span>
                                </div>
                                <div class="progress progress-xs shadow-xs">
                                    <div class="progress-bar bg-gradient-info" style="width: {{ $ti['score'] * 10 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-2">
                                <small class="text-muted italic">Belum ada data nilai bulan ini</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@section('afterAppScripts')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function () {
            let attendanceChart = null;
            let currentPeriod = 'day';
            const ctx = document.getElementById('mainAttendanceChart').getContext('2d');

            const filterData = {
                rombel: @json($rombels->map(fn($r) => ['id' => $r->id, 'text' => $r->nama_rombel])),
                angkatan: [
                    { id: '10', text: 'Kelas 10' },
                    { id: '11', text: 'Kelas 11' },
                    { id: '12', text: 'Kelas 12' }
                ],
                jurusan: @json($jurusans->map(fn($j) => ['id' => $j->id, 'text' => $j->nama_jurusan]))
            };

            function initChart(type, labels, dataset) {
                if (attendanceChart) {
                    attendanceChart.destroy();
                }

                const chartConfig = {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: dataset
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: type === 'doughnut' ? 'right' : 'top',
                                display: true
                            }
                        }
                    }
                };

                if (type === 'line') {
                    chartConfig.options.scales = {
                        y: { beginAtZero: true, grid: { display: false } },
                        x: { grid: { display: false } }
                    };
                    chartConfig.options.elements = {
                        line: { tension: 0.3, borderWidth: 2 },
                        point: { radius: 2 }
                    };
                }

                attendanceChart = new Chart(ctx, chartConfig);
            }

            function loadChartData() {
                const filterType = $('#chartFilterType').val();
                const filterValue = $('#chartFilterValue').val();

                let requestData = {
                    period: currentPeriod,
                    filter_type: filterType,
                    filter_value: filterValue
                };

                if (currentPeriod === 'custom') {
                    const sd = $('#customStartDate').val();
                    const ed = $('#customEndDate').val();
                    if (!sd || !ed) {
                        return; // Wait for valid input
                    }
                    requestData.start_date = sd;
                    requestData.end_date = ed;
                }

                $('#chartLoader').removeClass('d-none');
                $('#chartNoData').addClass('d-none');
                $('#chartContainer').addClass('d-none');

                $.ajax({
                    url: "{{ route('dashboard.attendance-chart') }}",
                    data: requestData,
                    success: function (resp) {
                        $('#chartLoader').addClass('d-none');

                        // Check if data is empty (all zeros)
                        let total = 0;
                        resp.datasets.forEach(ds => {
                            const dataArray = ds.data || [];
                            dataArray.forEach(val => total += (parseFloat(val) || 0));
                        });

                        if (total === 0) {
                            $('#chartNoData').removeClass('d-none');
                            $('#chartContainer').addClass('d-none');
                        } else {
                            $('#chartNoData').addClass('d-none');
                            $('#chartContainer').removeClass('d-none');
                            initChart(resp.type, resp.labels, resp.datasets);
                        }
                    },
                    error: function() {
                        $('#chartLoader').addClass('d-none');
                        $('#chartNoData').removeClass('d-none').find('p').text('Gagal memuat data.');
                    }
                });
            }

            $('#chartFilterType').on('change', function () {
                const type = $(this).val();
                const valueSelect = $('#chartFilterValue');

                if (type === 'all') {
                    valueSelect.addClass('d-none');
                } else {
                    valueSelect.removeClass('d-none').empty();
                    filterData[type].forEach(item => {
                        valueSelect.append(`<option value="${item.id}">${item.text}</option>`);
                    });
                }
                loadChartData();
            });

            $('#chartFilterValue').on('change', loadChartData);

            $('.btn-period').on('click', function () {
                $('.btn-period').removeClass('active');
                $(this).addClass('active');
                currentPeriod = $(this).data('period');

                if (currentPeriod === 'custom') {
                    $('#customDateContainer').removeClass('d-none').addClass('d-flex');
                } else {
                    $('#customDateContainer').addClass('d-none').removeClass('d-flex');
                    loadChartData();
                }
            });

            $('#btnApplyCustom').on('click', function() {
                if (!$('#customStartDate').val() || !$('#customEndDate').val()) {
                    toastr.warning('Silakan pilih rentang tanggal mulai dan akhir.');
                    return;
                }
                loadChartData();
            });

            // Initial Load
            loadChartData();
        });
    </script>
    <style>
        .opacity-50 {
            opacity: 0.5;
        }

        .btn-period.active {
            background-color: #007bff;
            color: #fff;
        }
    </style>
@endsection