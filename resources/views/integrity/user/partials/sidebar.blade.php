<!-- SIDEBAR STATIS (KIRI) -->
<div class="col-lg-5 col-xl-4 mb-4">
    <div class="wallet-sidebar">
        <!-- Header Profil -->
        <div class="d-flex align-items-center mb-4 pb-2">
            <img src="{{ $user->avatar_url }}" class="rounded-circle border border-primary p-1 mr-3 shadow-sm" width="60" height="60">
            <div>
                <small class="text-muted d-block font-weight-bold">Halo, Selamat Datang!</small>
                <h5 class="mb-0 text-dark" style="font-weight: 800;">{{ $user->name }}</h5>
            </div>
        </div>

        <!-- Kartu Saldo Premium -->
        <div class="balance-card shadow">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-xs text-uppercase opacity-75 d-block" style="letter-spacing: 1px;">Saldo Integritas Saya</span>
                <a href="{{ route('integrity.user.index') }}" class="text-white refresh-balance-btn" title="Refresh Saldo">
                    <i class="fa fa-refresh"></i>
                </a>
            </div>
            <div class="d-flex align-items-end mb-4">
                <h1 class="mb-0 mr-2 text-white" style="font-weight: 800; font-size: 3.5rem; letter-spacing: -2px;">{{ number_format($user->current_points) }}</h1>
                <span class="font-weight-bold mb-2 opacity-90">POIN</span>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-3 border-top border-white-20">
                <div class="text-left">
                    <small class="d-block opacity-75 text-white text-xs mb-1">
                        Peringkat Karakter 
                        <i class="fa fa-info-circle ml-1" style="cursor: pointer;" data-toggle="modal" data-target="#rankLevelsModal" title="Klik untuk lihat tingkatan"></i>
                    </small>
                    <span class="font-weight-bold text-white"><i class="fa fa-shield mr-1"></i> Level {{ $user->level }}</span>
                </div>
                <div class="text-right">
                    <small class="d-block opacity-75 text-white text-xs mb-1">Aset Aktif</small>
                    <span class="font-weight-bold text-white">{{ $inventory->where('status', 'AVAILABLE')->count() }} Voucher</span>
                </div>
            </div>
        </div>

        <!-- Navigasi Menu Dashboard -->
        <div class="action-list shadow-sm">
            <button onclick="switchView('history', this)" class="action-btn active">
                <div class="btn-icon"><i class="fa fa-history"></i></div>
                <span class="font-weight-bold">Riwayat Transaksi</span>
            </button>
            <button onclick="switchView('shop', this)" class="action-btn">
                <div class="btn-icon text-success"><i class="fa fa-shopping-cart"></i></div>
                <span class="font-weight-bold">Marketplace Integritas</span>
            </button>
            <button onclick="switchView('inventory', this)" class="action-btn">
                <div class="btn-icon text-warning"><i class="fa fa-briefcase"></i></div>
                <span class="font-weight-bold">Gudang Inventori</span>
            </button>
            <button data-toggle="modal" data-target="#rulesModal" class="action-btn">
                <div class="btn-icon text-info"><i class="fa fa-book"></i></div>
                <span class="font-weight-bold">Panduan Poin</span>
            </button>
        </div>
    </div>
</div>
