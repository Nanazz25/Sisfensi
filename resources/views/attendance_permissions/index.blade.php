@extends('layouts.app')

@section('title', 'Daftar Pengajuan Izin/Sakit')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header d-flex justify-content-between align-items-center">
                    <h2>Daftar Pengajuan Izin / Sakit</h2>
                    @if(auth()->user()->role === 'siswa')
                        <a href="{{ route('attendance-permissions.create') }}" class="btn btn-primary shadow-sm">
                            <i class="fa fa-plus mr-1"></i> Ajukan Izin
                        </a>
                    @endif
                </div>
                <div class="body">
                    <form action="{{ route('attendance-permissions.index') }}" method="GET"
                        class="ajax-form compact-form mb-4">
                        <div class="row">
                            <div class="col-md-5 col-sm-12">
                                <div class="form-group mb-md-0">
                                    <label class="small font-weight-bold text-muted">Instant Search</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i
                                                    class="fa fa-search text-muted"></i></span>
                                        </div>
                                        <input type="text" name="search" class="form-control border-left-0"
                                            placeholder="Cari nama siswa..." value="{{ request('search') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group mb-md-0">
                                    <label class="small font-weight-bold text-muted">Filter Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">-- Semua Status --</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                        </option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                            Approved</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                            Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-custom spacing5">
                            <thead>
                                <tr>
                                    <th>Siswa</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Keterangan</th>
                                    <th>Lampiran</th>
                                    <th>Status</th>
                                    <th>Disetujui Oleh</th>
                                    @if(auth()->user()->role !== 'siswa')
                                        <th class="text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $permit)
                                    <tr>
                                        <td>
                                            <strong>{{ $permit->anggotaRombel->pesertaDidik->user->name }}</strong><br>
                                            <small>{{ $permit->anggotaRombel->rombonganBelajar->nama_rombel }}</small>
                                        </td>
                                        <td>
                                            {{ $permit->tanggal_mulai->format('d/m/Y') }}
                                            @if($permit->tanggal_mulai != $permit->tanggal_selesai)
                                                - {{ $permit->tanggal_selesai->format('d/m/Y') }}
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $permit->jenis === 'sakit' ? 'badge-warning' : 'badge-info' }}">
                                                {{ strtoupper($permit->jenis) }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($permit->keterangan, 50) }}</td>
                                        <td>
                                            @if($permit->lampiran)
                                                <a href="{{ asset('storage/' . $permit->lampiran) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-paperclip"></i> Lihat
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($permit->status === 'pending')
                                                <span class="badge badge-warning">PENDING</span>
                                            @elseif($permit->status === 'approved')
                                                <span class="badge badge-success">APPROVED</span>
                                            @else
                                                <span class="badge badge-danger">REJECTED</span>
                                            @endif
                                        </td>
                                        <td>{{ $permit->approver->name ?? '-' }}</td>
                                        @if(auth()->user()->role !== 'siswa')
                                            <td class="text-center">
                                                @if($permit->status === 'pending')
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-success btn-confirm"
                                                            data-title="Setujui Izin" data-message="Yakin ingin menyetujui izin dari:"
                                                            data-name="{{ $permit->anggotaRombel->pesertaDidik->user->name }}"
                                                            data-action="{{ route('attendance-permissions.update-status', [$permit->id, 'approved']) }}"
                                                            data-btn-class="btn-success" data-btn-text="Setujui"
                                                            data-confirm-icon="fa-check" data-toggle="modal" data-target="#confirmModal"
                                                            title="Setujui">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger btn-confirm"
                                                            data-title="Tolak Izin" data-message="Yakin ingin menolak izin dari:"
                                                            data-name="{{ $permit->anggotaRombel->pesertaDidik->user->name }}"
                                                            data-action="{{ route('attendance-permissions.update-status', [$permit->id, 'rejected']) }}"
                                                            data-btn-class="btn-danger" data-btn-text="Tolak"
                                                            data-confirm-icon="fa-times" data-toggle="modal" data-target="#confirmModal"
                                                            title="Tolak">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">Sudah diproses</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data pengajuan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $permissions->links() }}
                </div>
            </div>
        </div>
    </div>

    <x-modal-confirm />
@endsection