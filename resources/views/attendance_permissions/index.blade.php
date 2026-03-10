@extends('layouts.app')

@section('title', 'Daftar Pengajuan Izin/Sakit/Manual')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="header d-flex justify-content-between align-items-center">
                    <h2>Daftar Pengajuan Izin / Sakit / Manual</h2>
                    @if(auth()->user()->role === 'siswa')
                        <div class="btn-group">
                            <a href="{{ route('attendance-permissions.create') }}" class="btn btn-primary shadow-sm">
                                <i class="fa fa-plus mr-1"></i> Ajukan Izin
                            </a>
                        </div>
                    @endif
                </div>
                <div class="body">
                    <form action="{{ route('attendance-permissions.index') }}" method="GET" class="mb-4">
                        <div class="row align-items-end">
                            <div class="col-md-4 col-sm-6">
                                <label class="small font-weight-bold text-muted mb-1">Cari Nama</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i
                                                class="fa fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" name="search" class="form-control border-left-0"
                                        placeholder="Ketik nama..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="small font-weight-bold text-muted mb-1">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui
                                    </option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-custom spacing5 mb-0">
                            <thead>
                                <tr>
                                    <th>Siswa</th>
                                    <th>Waktu</th>
                                    <th>Jenis</th>
                                    <th>Keterangan</th>
                                    <th>Bukti/GPS</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $permit)
                                    <tr class="align-middle">
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $name = $permit->anggotaRombel->pesertaDidik->user->name;
                                                    $initial = strtoupper(substr($name, 0, 1));
                                                    $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1', '#fd7e14', '#20c997', '#007bff'];
                                                    $color = $colors[ord($initial) % count($colors)];
                                                @endphp
                                                <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm text-white avatar-initials"
                                                    style="width: 38px; height: 38px; font-weight: 700; background: {{ $color }}; border: 2px solid #fff; font-size: 14px;">
                                                    {{ $initial }}
                                                </div>
                                                <div class="ml-3">
                                                    <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.88rem;">
                                                        {{ $name }}</h6>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">
                                                        <i
                                                            class="fa fa-graduation-cap mr-1"></i>{{ $permit->anggotaRombel->rombonganBelajar->nama_rombel }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                             <div class="font-weight-bold text-dark mb-1" style="font-size: 0.85rem;">
                                                 <i class="fa fa-calendar-check-o mr-1 text-muted"></i>{{ $permit->jenis === 'manual' ? $permit->created_at->format('d/m/Y') : $permit->tanggal_mulai->format('d/m/Y') }}
                                             </div>
                                             @if(isset($permit->monthly_count) && $permit->monthly_count >= 3)
                                                 <span class="badge badge-danger" style="font-size: 10px;" title="Siswa sudah izin/sakit {{ $permit->monthly_count }}x bulan ini">
                                                     <i class="fa fa-warning mr-1"></i>SERING IZIN ({{ $permit->monthly_count }}x)
                                                 </span>
                                             @else
                                                 <small class="text-muted"><i class="fa fa-clock-o mr-1"></i>{{ $permit->created_at->format('H:i') }} WIB</small>
                                             @endif
                                         </td>
                                        <td class="py-3 text-center">
                                            @php
                                                $badgeClass = 'badge-soft-info';
                                                $icon = 'fa-info-circle';

                                                if ($permit->jenis === 'sakit') {
                                                    $badgeClass = 'badge-soft-warning';
                                                    $icon = 'fa-thermometer-half';
                                                } elseif ($permit->jenis === 'manual') {
                                                    $badgeClass = 'badge-soft-primary';
                                                    $icon = 'fa-hand-pointer-o';
                                                }

                                                $typeText = strtoupper($permit->jenis);
                                                if ($permit->jenis === 'manual') {
                                                    $typeText .= ' - ' . strtoupper($permit->jenis_absensi_manual ?? 'MASUK');
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }} shadow-xs px-2 py-1"
                                                style="font-size: 11px; letter-spacing: 0.3px;">
                                                <i class="fa {{ $icon }} mr-1"></i> {{ $typeText }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <div class="text-muted small" style="max-width: 180px; line-height: 1.4;">
                                                <i class="fa fa-commenting-o mr-1 opacity-50"></i>{{ Str::limit($permit->keterangan, 45) }}
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div class="btn-group">
                                                @if($permit->lampiran)
                                                    <a href="{{ asset('storage/' . $permit->lampiran) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-secondary" title="Lihat Bukti">
                                                        <i class="fa fa-file-text"></i>
                                                    </a>
                                                @endif
                                                @if($permit->latitude && $permit->longitude)
                                                    <a href="https://www.google.com/maps?q={{ $permit->latitude }},{{ $permit->longitude }}"
                                                        target="_blank" class="btn btn-sm btn-outline-secondary" title="Lokasi GPS">
                                                        <i class="fa fa-map-marker"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            @if($permit->status === 'pending')
                                                <span class="badge badge-warning">PENDING</span>
                                            @elseif($permit->status === 'approved')
                                                <span class="badge badge-success">DISETUJUI</span>
                                            @else
                                                <span class="badge badge-danger">DITOLAK</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center">
                                            @if($permit->status === 'pending' && auth()->user()->role !== 'siswa')
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-success btn-confirm"
                                                        data-title="Setujui Pengajuan"
                                                        data-message="Anda yakin menyetujui data dari {{ $permit->anggotaRombel->pesertaDidik->user->name }}?"
                                                        data-action="{{ route('attendance-permissions.update-status', [$permit->id, 'approved']) }}"
                                                        data-btn-class="btn-success" data-btn-text="Setujui"
                                                        data-confirm-icon="fa-check" data-toggle="modal"
                                                        data-target="#confirmModal"><i class="fa fa-check"></i></button>
                                                    <button type="button" class="btn btn-sm btn-danger btn-confirm"
                                                        data-title="Tolak Pengajuan"
                                                        data-message="Anda yakin menolak data dari {{ $permit->anggotaRombel->pesertaDidik->user->name }}?"
                                                        data-action="{{ route('attendance-permissions.update-status', [$permit->id, 'rejected']) }}"
                                                        data-btn-class="btn-danger" data-btn-text="Tolak"
                                                        data-confirm-icon="fa-times" data-toggle="modal"
                                                        data-target="#confirmModal"><i class="fa fa-times"></i></button>
                                                </div>
                                            @elseif(auth()->user()->role === 'siswa')
                                                <span class="text-muted small">-</span>
                                            @else
                                                <i class="fa fa-check-circle text-success" style="font-size: 1.2rem"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data pengajuan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $permissions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal-confirm />
@endsection