@extends('layouts.app')

@section('title', 'Kelola Presensi Manual')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Kelola Presensi Manual</h2>
                    <small>Gunakan halaman ini untuk merubah status kehadiran secara manual jika terjadi kesalahan input
                        atau error sistem.</small>
                </div>
                <div class="body">
                    <form action="{{ route('attendance.manual') }}" method="GET">
                        <div class="bg-light p-4 rounded-lg border shadow-xs">
                            <!-- Baris 1: Konteks Utama (Kelas & Kategori) -->
                            <div class="row align-items-end mb-3">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1">Pilih Rombongan Belajar (Kelas)</label>
                                    <select name="rombel_id" class="form-control select2" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($rombels as $rombel)
                                            <option value="{{ $rombel->id }}" {{ request('rombel_id') == $rombel->id ? 'selected' : '' }}>
                                                {{ $rombel->nama_rombel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1">Kategori Presensi</label>
                                    <select name="type" class="form-control" id="attendanceType">
                                        <option value="masuk" {{ request('type', 'masuk') == 'masuk' ? 'selected' : '' }}>Harian (Masuk/Pulang)</option>
                                        <option value="pelajaran" {{ request('type') == 'pelajaran' ? 'selected' : '' }}>Mata Pelajaran (Kelas)</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-0 {{ request('type') == 'pelajaran' ? '' : 'd-none' }}" id="subjectFilter">
                                    <label class="small font-weight-bold text-muted mb-1">Mata Pelajaran Terjadwal</label>
                                    <select name="schedule_id" class="form-control">
                                        <option value="">-- Pilih Mapel --</option>
                                        @foreach($schedules ?? [] as $sch)
                                            <option value="{{ $sch->id }}" {{ request('schedule_id') == $sch->id ? 'selected' : '' }}>
                                                {{ $sch->subject->nama_mapel }} ({{ date('H:i', strtotime($sch->jam_mulai)) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Baris 2: Filter Pencarian & Aksi -->
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1">Cari Nama Siswa / NIS</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white"><i class="fa fa-user-circle-o text-muted"></i></span>
                                        </div>
                                        <input type="text" name="q" class="form-control" placeholder="Contoh: Siswa..." value="{{ request('q') }}">
                                    </div>
                                </div>

                                <div class="col-md-3 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1">Filter Status Kehadiran</label>
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <option value="belum_absen" {{ request('status') == 'belum_absen' ? 'selected' : '' }}>Belum Absen (Prioritas)</option>
                                        <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                        <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                        <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin / Sakit</option>
                                        <option value="alpha" {{ request('status') == 'alpha' ? 'selected' : '' }}>Alpha (Tanpa Keterangan)</option>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-muted mb-1">Tanggal Data</label>
                                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                                </div>

                                <div class="col-md-2 d-flex">
                                    <button type="submit" class="btn btn-primary flex-grow-1 mr-2 shadow-sm font-weight-bold">
                                        <i class="fa fa-search mr-1"></i> CARI
                                    </button>
                                    <a href="{{ route('attendance.manual') }}" class="btn btn-outline-secondary shadow-xs" title="Reset Filter">
                                        <i class="fa fa-refresh"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if(request('rombel_id'))
                        <hr>
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
                                        <th>Status Saat Ini</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $student)
                                        <tr class="student-row">
                                            <td>
                                                <label class="custom-control custom-checkbox mb-0">
                                                    <input type="checkbox" class="custom-control-input student-checkbox" 
                                                           value="{{ $student->id }}" 
                                                           id="pd_{{ $student->id }}"
                                                           data-pd-id="{{ $student->peserta_didik_id }}"
                                                           data-name="{{ $student->pesertaDidik->user->name }}">
                                                    <span class="custom-control-label" for="pd_{{ $student->id }}"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avtar-pic rounded-circle bg-light mr-2" style="width: 35px; height: 35px; line-height: 35px; text-align: center;">
                                                        <span class="font-weight-bold text-primary">{{ substr($student->pesertaDidik->user->name, 0, 1) }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="d-block font-weight-bold">{{ $student->pesertaDidik->user->name }}</span>
                                                        <small class="text-muted">{{ $student->pesertaDidik->no_induk }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $record = $student->current_attendance;
                                                @endphp

                                                @if($record)
                                                    @switch($record->status)
                                                        @case('hadir') <span class="badge badge-success">HADIR</span> @break
                                                        @case('terlambat') <span class="badge badge-warning">TERLAMBAT</span> @break
                                                        @case('izin') <span class="badge badge-info text-white">IZIN</span> @break
                                                        @case('sakit') <span class="badge badge-info text-white">SAKIT</span> @break
                                                        @default <span class="badge badge-danger">ALPHA</span>
                                                    @endswitch
                                                    @if(in_array($record->status, ['hadir', 'terlambat']))
                                                        <div class="mt-1"><small class="text-muted"><i class="fa fa-clock-o mr-1"></i>{{ date('H:i', strtotime($record->waktu_absen)) }}</small></div>
                                                    @endif
                                                @else
                                                    <span class="badge badge-default">BELUM ABSEN</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <form action="{{ route('attendance.manual-adjust') }}" method="POST" class="d-flex justify-content-end align-items-center flex-wrap">
                                                    @csrf
                                                    <input type="hidden" name="anggota_rombel_ids[]" value="{{ $student->id }}">
                                                    <input type="hidden" name="peserta_didik_ids[]" value="{{ $student->peserta_didik_id }}">
                                                    <input type="hidden" name="rombel_id" value="{{ request('rombel_id') }}">
                                                    <input type="hidden" name="tanggal" value="{{ $date }}">
                                                    <input type="hidden" name="type" value="{{ request('type', 'masuk') }}">
                                                    <input type="hidden" name="schedule_id" value="{{ request('schedule_id') }}">

                                                    <div class="input-group input-group-sm mb-1 mb-md-0" style="width: 130px;">
                                                        <select name="status" class="form-control custom-select" required>
                                                            <option value="">Status...</option>
                                                            <option value="pending">Pending</option>
                                                            <option value="hadir">Hadir</option>
                                                            <option value="terlambat">Terlambat</option>
                                                            <option value="izin">Izin</option>
                                                            <option value="sakit">Sakit</option>
                                                            <option value="alpha">Alpha</option>
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button type="submit" class="btn btn-primary btn-sm" title="Simpan Perubahan">
                                                                <i class="fa fa-save"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Kelas ini tidak memiliki anggota.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<!-- Floating Bulk Action Bar -->
<div id="bulkActionBar" class="position-fixed w-100" style="bottom: 20px; left: 0; z-index: 1040; display: none;">
    <div class="container">
        <div class="card bg-dark text-white shadow-lg border-0 rounded-pill mx-auto mb-0" style="max-width: 700px;">
            <div class="card-body py-2 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <span class="badge badge-primary rounded-circle mr-3" id="selectedCount" style="width: 25px; height: 25px; line-height: 25px; padding: 0; text-align: center;">0</span>
                    <span class="font-weight-bold small">Siswa Terpilih</span>
                </div>
                <form action="{{ route('attendance.manual-adjust') }}" method="POST" class="d-flex align-items-center">
                    @csrf
                    <div id="bulkIdContainer"></div>
                    <input type="hidden" name="rombel_id" value="{{ request('rombel_id') }}">
                    <input type="hidden" name="tanggal" value="{{ $date }}">
                    <input type="hidden" name="type" value="{{ request('type', 'masuk') }}">
                    <input type="hidden" name="schedule_id" value="{{ request('schedule_id') }}">
                    
                    <button type="button" class="btn btn-sm btn-link text-white-50 mr-2" id="clearSelection">Batal</button>
                    
                    <div class="input-group input-group-sm mr-2" style="width: 150px;">
                        <select name="status" class="form-control" required>
                            <option value="">Ubah Status Ke...</option>
                            <option value="pending">Pending (Reset Poin)</option>
                            <option value="hadir">Hadir</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpha">Alpha</option>
                        </select>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="fa fa-check mr-1"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('afterAppScripts')
<script>
    $(document).ready(function() {
        const bulkBar = $('#bulkActionBar');
        const countBadge = $('#selectedCount');
        const bulkIdContainer = $('#bulkIdContainer');

        // Toggle Subject Filter
        $('#attendanceType').on('change', function() {
            if ($(this).val() === 'pelajaran') {
                $('#subjectFilter').removeClass('d-none');
            } else {
                $('#subjectFilter').addClass('d-none');
                $('#subjectFilter select').val('');
            }
        });

        // Master Select All Logic (Event Delegation)
        $(document).on('change', '#selectAllStudents', function() {
            const isChecked = $(this).prop('checked');
            $('.student-checkbox').prop('checked', isChecked);
            updateBulkBar();
        });

        // Individual Checkbox Logic (Event Delegation)
        $(document).on('change', '.student-checkbox', function() {
            const allCheckboxes = $('.student-checkbox');
            const checkedCheckboxes = $('.student-checkbox:checked');
            
            $('#selectAllStudents').prop('checked', allCheckboxes.length === checkedCheckboxes.length);
            updateBulkBar();
        });

        function updateBulkBar() {
            const selected = $('.student-checkbox:checked');
            if (selected.length > 0) {
                countBadge.text(selected.length);
                
                // Refill hidden inputs for bulk form
                bulkIdContainer.empty();
                selected.each(function() {
                    const id = $(this).val();
                    const pdId = $(this).data('pd-id');
                    bulkIdContainer.append(`<input type="hidden" name="anggota_rombel_ids[]" value="${id}">`);
                    bulkIdContainer.append(`<input type="hidden" name="peserta_didik_ids[]" value="${pdId}">`);
                });
                
                bulkBar.fadeIn();
            } else {
                bulkBar.fadeOut();
            }
        }

        // Clear Selection
        $(document).on('click', '#clearSelection', function(e) {
            e.preventDefault();
            $('.student-checkbox').prop('checked', false);
            $('#selectAllStudents').prop('checked', false);
            updateBulkBar();
        });
    });
</script>
@endsection
@endsection