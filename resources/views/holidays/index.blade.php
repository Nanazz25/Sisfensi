@extends('layouts.app')

@section('title', 'Manajemen Hari Libur')

@section('afterAppStyles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
    <style>
        .fc { font-family: 'Nunito', sans-serif; }
        .fc .fc-toolbar-title { font-size: 1.5rem !important; font-weight: 800 !important; color: #444; text-transform: uppercase; letter-spacing: 1px; }
        .fc .fc-button-primary { background-color: #00bcd4 !important; border-color: #00bcd4 !important; border-radius: 8px !important; padding: 0.5rem 1.2rem !important; font-weight: bold !important; text-transform: capitalize !important; box-shadow: 0 2px 4px rgba(0, 188, 212, 0.2); transition: all 0.3s ease; }
        .fc .fc-button-primary:hover { background-color: #00a0b5 !important; border-color: #00a0b5 !important; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 188, 212, 0.3); }
        .fc .fc-button-primary:disabled { background-color: #b2ebf2 !important; border-color: #b2ebf2 !important; opacity: 1; }
        
        .fc .fc-col-header-cell-cushion { padding: 12px !important; font-weight: 800; color: #666; text-transform: uppercase; font-size: 0.9rem; text-decoration: none; letter-spacing: 0.5px; }
        .fc .fc-daygrid-day-number { font-weight: 700; color: #888; padding: 10px !important; text-decoration: none; font-size: 1rem; }
        .fc-theme-standard td, .fc-theme-standard th { border-color: #f0f0f0 !important; }
        .fc-day-today { background-color: rgba(0, 188, 212, 0.04) !important; }
        
        .fc-event { border-radius: 6px !important; padding: 4px 8px !important; font-weight: 700 !important; border: none !important; margin-bottom: 3px !important; box-shadow: 0 2px 4px rgba(0,0,0,0.05); font-size: 0.85rem !important; }
        .fc-daygrid-event-harness { margin: 0 6px !important; }

        /* Specific Table Styling to match Rombel */
        .card .header h2 { font-size: 1.1rem; font-weight: 700; color: #333; margin-bottom: 0; }
        .table thead th { background: transparent; border-bottom: 2px solid #eee; text-transform: none; font-weight: 700; color: #444; font-size: 0.9rem; padding: 12px 15px; }
        .table td { vertical-align: middle; padding: 12px 15px; border-top: 1px solid #f5f5f5; }
        .font-weight-600 { font-weight: 600; }
        .shadow-xs { box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .rounded-circle { border-radius: 50% !important; }
        .mw-35 { width: 35px; height: 35px; min-width: 35px; }
        
        @media (max-width: 768px) {
            .fc .fc-toolbar-title { font-size: 1.2rem !important; }
            .fc .fc-toolbar.fc-header-toolbar { flex-direction: column; gap: 15px; }
            .fc .fc-col-header-cell-cushion { font-size: 0.75rem; padding: 6px !important; }
        }
    </style>
@endsection

@section('content')
    <div class="row clearfix mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="header bg-white py-4 px-4" style="border-radius: 12px 12px 0 0; border-bottom: 1px solid #f8f9fa;">
                    <h5 class="mb-0 font-weight-bold color-444">Kalender Kehadiran & Streak Absensi</h5>
                </div>
                <div class="body p-4">
                    <div id="holiday-calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="header d-flex justify-content-between align-items-center bg-white py-3 px-4"
            style="border-radius: 12px 12px 0 0; border-bottom: 1px solid #f8f9fa;">
            <h2>Daftar Hari Libur</h2>
            <button type="button" class="btn btn-success px-4 font-weight-bold shadow-sm" style="border-radius: 8px;" data-toggle="modal"
                data-target="#addHolidayModal">
                <i class="fa fa-plus mr-2"></i> Tambah Libur
            </button>
        </div>
        <div class="body p-4">
            <form method="GET" action="{{ route('holidays.index') }}" id="filterForm" class="row mb-4 align-items-end mx-0">
                <div class="col-12 col-md-4 mb-3 mb-md-0 px-1">
                    <label class="font-weight-600 small mb-1 text-muted">Cari Keterangan</label>
                    <div class="input-group shadow-xs rounded-lg overflow-hidden border">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-0"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="q" id="searchInput" value="{{ request('q') }}" class="form-control border-0"
                            placeholder="Ketik keterangan libur...">
                    </div>
                </div>

                <div class="col-auto px-1">
                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle shadow-xs px-3" style="border-radius: 8px; height: 38px;" data-toggle="dropdown">
                            <i class="fa fa-sort-amount-desc mr-1"></i> Urutkan
                        </button>
                        <ul class="dropdown-menu shadow-sm border-0">
                            <li><a class="dropdown-item sort-option py-2" data-value="desc" href="javascript:void(0);"><i class="fa fa-clock-o mr-2"></i> Terbaru</a></li>
                            <li><a class="dropdown-item sort-option py-2" data-value="asc" href="javascript:void(0);"><i class="fa fa-history mr-2"></i> Terlama</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-auto px-1">
                    <a href="{{ route('holidays.index') }}" class="btn btn-outline-danger shadow-xs d-flex align-items-center justify-content-center" 
                        style="height: 38px; width: 45px; border-radius: 8px;" title="Reset">
                        <i class="fa fa-undo"></i>
                    </a>
                </div>

                <input type="hidden" name="sort" id="sortInput" value="{{ request('sort', 'desc') }}">
            </form>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $h)
                            <tr>
                                <td>{{ $loop->iteration + $holidays->firstItem() - 1 }}</td>
                                <td class="font-weight-600">
                                    <span class="text-info">
                                        <i class="fa fa-calendar-o mr-1"></i> {{ $h->date->translatedFormat('d M Y') }}
                                    </span>
                                </td>
                                <td class="font-weight-bold text-dark">{{ $h->description }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-warning mw-35 mr-1 rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-xs" 
                                        data-toggle="modal" data-target="#editHolidayModal{{ $h->id }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form action="{{ route('holidays.destroy', $h) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger mw-35 rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-xs" 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus hari libur ini?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editHolidayModal{{ $h->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                                <form action="{{ route('holidays.update', $h) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                                                        <h5 class="font-weight-bold">Edit Hari Libur</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="form-group mb-3 text-left">
                                                            <label class="font-weight-bold small text-muted text-uppercase">Tanggal</label>
                                                            <input type="date" name="date" class="form-control rounded" value="{{ $h->date->format('Y-m-d') }}" required>
                                                        </div>
                                                        <div class="form-group mb-0 text-left">
                                                            <label class="font-weight-bold small text-muted text-uppercase">Keterangan</label>
                                                            <input type="text" name="description" class="form-control rounded px-3" value="{{ $h->description }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 p-4">
                                                        <button type="button" class="btn btn-light rounded px-4" data-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-warning rounded px-4 shadow-sm font-weight-bold">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fa fa-info-circle mr-1"></i> Data hari libur tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $holidays->links() }}</div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="addHolidayModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <form action="{{ route('holidays.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <h5 class="font-weight-bold">Tambah Hari Libur</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-group mb-3 text-left">
                            <label class="font-weight-bold small text-muted text-uppercase">Tanggal</label>
                            <input type="date" name="date" class="form-control rounded" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group mb-0 text-left">
                            <label class="font-weight-bold small text-muted text-uppercase">Keterangan Libur</label>
                            <input type="text" name="description" class="form-control rounded px-3" placeholder="Contoh: Libur Nasional" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4">
                        <button type="button" class="btn btn-light rounded px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded px-4 shadow-sm font-weight-bold">Simpan Libur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('afterAppScripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var schoolDaysArr = '{{ $schoolDaysStr }}'.split(',');
            var dayMapEnToId = { 0: 'minggu', 1: 'senin', 2: 'selasa', 3: 'rabu', 4: 'kamis', 5: 'jumat', 6: 'sabtu' };

            var calendarEl = document.getElementById('holiday-calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                dayHeaderFormat: { weekday: 'short' }, // 3 letters
                headerToolbar: {
                    left: 'title',
                    right: 'prev,next today'
                },
                height: 650,
                events: function(fetchInfo, successCallback, failureCallback) {
                    var events = [];
                    
                    // 1. Database Holidays
                    @foreach($allHolidays as $h)
                    events.push({
                        id: 'holiday_db_{{ $h->id }}',
                        title: '{{ $h->description }}',
                        start: '{{ $h->date->format('Y-m-d') }}',
                        backgroundColor: '#dc3545',
                        borderColor: '#dc3545',
                        textColor: '#ffffff',
                        allDay: true
                    });
                    @endforeach

                    // 2. Weekend/Operational Holidays
                    var current = new Date(fetchInfo.start);
                    var end = new Date(fetchInfo.end);
                    
                    while (current < end) {
                        var dayIndex = current.getDay();
                        var dayNameId = dayMapEnToId[dayIndex];
                        
                        if (!schoolDaysArr.includes(dayNameId)) {
                            events.push({
                                id: 'holiday_weekend',
                                title: 'Libur',
                                start: new Date(current),
                                backgroundColor: '#ffe5e5',
                                borderColor: '#ffe5e5',
                                textColor: '#dc3545',
                                allDay: true
                            });
                        }
                        current.setDate(current.getDate() + 1);
                    }
                    
                    successCallback(events);
                },
                eventClick: function(info) {
                    toastr.info("Keterangan: " + info.event.title);
                }
            });
            calendar.render();
            
            window.addEventListener('resize', () => calendar.updateSize());
        });

        $(function() {
            $('.sort-option').on('click', function() {
                $('#sortInput').val($(this).data('value'));
                $('#filterForm').submit();
            });

            let timeout = null;
            $('#searchInput').on('keyup', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    $('#filterForm').submit();
                }, 500);
            });

            var searchNode = $('#searchInput')[0];
            if (searchNode) {
                var len = searchNode.value.length;
                searchNode.focus();
                searchNode.setSelectionRange(len, len);
            }
        });
    </script>
@endsection
