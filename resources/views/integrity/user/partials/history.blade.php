<!-- VIEW: RIWAYAT MUTASI -->
<div id="view-history" class="view-pane {{ !request()->hasAny(['month', 'type', 'sort']) || request()->has('page') ? 'active' : 'd-none' }}">
    <div class="d-flex align-items-center mb-4">
        <h5 class="font-weight-bold mb-0 mr-3" style="font-weight: 800; color: #333;">Aktivitas Terbaru Anda</h5>
        <div class="flex-grow-1 border-top" style="opacity: 0.05;"></div>
    </div>

    <!-- Filter Mutation -->
    <form action="{{ route('integrity.user.index') }}" method="GET" id="historyFilterForm" class="mb-4">
        <div class="row no-gutters bg-white p-3 shadow-xs rounded-lg border border-light">
            <div class="col-12 col-md-4 mb-2 mb-md-0 pr-md-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                    </div>
                    <input type="text" name="q" class="form-control border-left-0" placeholder="Cari aktivitas..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-6 col-md-2 pr-2 mb-2 mb-md-0">
                <select name="type" class="form-control custom-select" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="plus" {{ request('type') == 'plus' ? 'selected' : '' }}>Saldo (+)</option>
                    <option value="minus" {{ request('type') == 'minus' ? 'selected' : '' }}>Saldo (-)</option>
                </select>
            </div>
            <div class="col-6 col-md-3 pr-md-2 mb-2 mb-md-0">
                <select name="month" class="form-control custom-select" onchange="this.form.submit()">
                    <option value="">Semua Bulan</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex align-items-center">
                <select name="sort" class="form-control custom-select mr-2" onchange="this.form.submit()">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                </select>
                <a href="{{ route('integrity.user.index') }}" class="btn btn-light border shadow-xs" title="Reset Filter">
                    <i class="fa fa-refresh text-muted"></i>
                </a>
            </div>
        </div>
    </form>

    @forelse($mutations as $m)
        <div class="data-card shadow-xs">
            <div class="tx-icon-circle {{ $m->amount > 0 ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' }}">
                <i class="fa {{ $m->amount > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $m->description }}</h6>
                <small class="text-muted text-xs">{{ $m->created_at->format('j M Y, H:i') }}</small>
            </div>
            <div class="text-right ml-2 flex-shrink-0" style="white-space: nowrap; min-width: fit-content;">
                <span class="font-weight-bold {{ $m->amount > 0 ? 'text-success' : 'text-danger' }} h6 mb-0 points-label">
                    {{ $m->amount > 0 ? '+' : '' }}{{ $m->amount }}&nbsp;<small>P</small>
                </span>
            </div>
        </div>
    @empty
        <div class="text-center py-5 bg-light rounded-lg border border-dashed">
             <i class="fa fa-info-circle text-muted mb-2"></i>
             <p class="text-muted small mb-0">Belum ada mutasi poin terdata.</p>
        </div>
    @endforelse
    <div class="mt-4">{{ $mutations->links() }}</div>
</div>
