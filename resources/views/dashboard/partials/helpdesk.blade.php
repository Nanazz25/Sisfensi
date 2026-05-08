<!-- Status Cards (Admin-Style) -->
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-primary text-white text-center mr-3 shadow-sm">
                <i class="fa fa-ticket"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Total Aduan</small>
                <h5 class="mb-0 font-weight-bold text-dark">{{ $stats['total'] }}</h5>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-warning text-white text-center mr-3 shadow-sm">
                <i class="fa fa-spinner"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Aktif / Antrean</small>
                <h5 class="mb-0 font-weight-bold text-dark">{{ $stats['open'] + $stats['in_progress'] }}</h5>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-success text-white text-center mr-3 shadow-sm">
                <i class="fa fa-check-circle"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Selesai Dihandle</small>
                <h5 class="mb-0 font-weight-bold text-dark">{{ $stats['resolved'] }}</h5>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card card-status shadow-sm border-0">
        <div class="body d-flex align-items-center">
            <div class="status-icon bg-info text-white text-center mr-3 shadow-sm">
                <i class="fa fa-clock-o"></i>
            </div>
            <div>
                <small class="text-muted d-block font-weight-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Rata Respon</small>
                <h5 class="mb-0 font-weight-bold text-dark">{{ $performance['avg_response'] }}m</h5>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-8">
    <div class="card shadow-sm border-0">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Aduan Terbaru</h2>
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-custom spacing5 mb-0">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Subjek</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_tickets as $ticket)
                            <tr>
                                <td class="w60">
                                    <img src="{{ $ticket->reporter->avatar_url }}" data-toggle="tooltip" title="{{ $ticket->reporter->name }}" class="rounded-circle avatar" alt="Avatar">
                                </td>
                                <td>
                                    <p class="mb-0 font-weight-bold">{{ Str::limit($ticket->subject, 30) }}</p>
                                    <span class="text-muted small">{{ $ticket->category->name }}</span>
                                </td>
                                <td>
                                    @php
                                        $prioColor = [
                                            'Low' => 'secondary',
                                            'Medium' => 'info',
                                            'High' => 'warning',
                                            'Urgent' => 'danger'
                                        ][$ticket->priority] ?? 'primary';
                                    @endphp
                                    <span class="badge badge-{{ $prioColor }}">{{ $ticket->priority }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColor = [
                                            'Open' => 'warning',
                                            'In-Progress' => 'info',
                                            'Resolved' => 'success',
                                            'Closed' => 'secondary'
                                        ][$ticket->status] ?? 'primary';
                                    @endphp
                                    <span class="badge badge-{{ $statusColor }}">{{ $ticket->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="fa fa-check-circle text-success d-block mb-2" style="font-size: 30px; opacity: 0.3;"></i>
                                    <p class="text-muted mb-0">Belum ada aduan masuk.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-4">
    <div class="card shadow-sm border-0">
        <div class="header">
            <h2>Performa Saya</h2>
        </div>
        <div class="body text-center pb-5">
            <div class="card shadow-none border rounded p-4 mb-3 bg-light">
                <h1 class="mb-0 font-weight-bold text-primary">{{ $performance['score'] }}%</h1>
                <small class="text-muted font-weight-bold">Kepuasan Siswa</small>
            </div>
            <p class="text-muted small">Berdasarkan penilaian bintang yang diberikan oleh siswa setelah tiket diselesaikan.</p>
            <div class="mt-4">
                <a href="{{ route('tickets.admin-dashboard') }}" class="btn btn-primary btn-block py-2">
                    <i class="fa fa-line-chart mr-2"></i> Lihat Analitik Lengkap
                </a>
            </div>
        </div>
    </div>
</div>
