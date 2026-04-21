<div class="card shadow-sm border-0 h-100" id="leaderboardCardArea">
<style>
    @media (max-width: 576px) {
        #leaderboardCardArea .header { 
            display: block !important; 
            padding: 15px !important;
        }
        #leaderboardCardArea .header h2 { 
            font-size: 1.1rem !important; 
            margin-bottom: 10px !important; 
        }
        #leaderboardCardArea .nav-pills-custom {
            margin-left: 0 !important;
            justify-content: flex-start !important;
        }
        #leaderboardCardArea .nav-pills-custom .nav-link {
            margin-left: 0 !important;
            margin-right: 5px !important;
            padding: 3px 12px !important;
            font-size: 0.7rem !important;
        }
        #leaderboardCardArea .leaderboard-item {
            padding: 10px 8px !important;
        }
        #leaderboardCardArea .user-avatar {
            flex-shrink: 0 !important;
        }
        #leaderboardCardArea .user-avatar img {
            width: 32px !important;
            height: 32px !important;
            object-fit: cover !important;
        }
        #leaderboardCardArea .rank-badge {
            flex-shrink: 0 !important;
            width: 25px !important;
            text-align: center;
        }
    }
</style>
    <div class="header d-flex justify-content-between align-items-center flex-wrap pb-0">
        <h2 class="font-weight-bold">Leaderboard Siswa</h2>
        <ul class="nav nav-pills nav-pills-custom" id="leaderboardTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active small py-1 px-3" id="top-tab" data-toggle="tab" href="#top-rank" role="tab">Atas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link small py-1 px-3" id="bottom-tab" data-toggle="tab" href="#bottom-rank" role="tab">Bawah</a>
            </li>
        </ul>
    </div>
    <div class="body p-0">
        <div class="tab-content" id="leaderboardTabContent">
            <!-- Tab Top Rank -->
            <div class="tab-pane fade show active" id="top-rank" role="tabpanel">
                <div class="leaderboard-container">
                    @forelse($topUsers->take(10) as $index => $u)
                    <div class="leaderboard-item d-flex align-items-center p-3 {{ $index < 3 ? 'top-rank' : '' }}">
                        <div class="rank-badge mr-3">
                            @if($index == 0) <i class="fa fa-trophy gold fa-lg"></i>
                            @elseif($index == 1) <i class="fa fa-trophy silver fa-lg"></i>
                            @elseif($index == 2) <i class="fa fa-trophy bronze fa-lg"></i>
                            @else <span class="rank-number text-muted small">{{ $index + 1 }}</span>
                            @endif
                        </div>
                        <div class="user-avatar mr-3">
                            <img src="{{ $u->avatar_url }}" class="rounded-circle shadow-sm border border-white" width="40" height="40" style="object-fit: cover;">
                        </div>
                        <div class="user-info ml-2 overflow-hidden" style="min-width: 0;">
                            <h6 class="mb-0 font-weight-bold {{ $index < 3 ? 'text-primary' : 'text-dark' }} text-truncate" style="font-size: 0.85rem;">
                                {{ $u->name }}
                            </h6>
                            <div class="mt-1 d-flex align-items-center">
                                <span class="text-primary font-weight-bold" style="font-size: 8px; opacity: 0.8; white-space: nowrap;">
                                    <i class="fa fa-shield mr-1"></i>{{ $u->level }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center p-5 text-muted">Belum ada siswa terpuji.</div>
                    @endforelse
                </div>
            </div>

            <!-- Tab Bottom Rank -->
            <div class="tab-pane fade" id="bottom-rank" role="tabpanel">
                <div class="leaderboard-container">
                    @forelse($bottomUsers->take(10) as $index => $u)
                    <div class="leaderboard-item d-flex align-items-center p-3">
                        <div class="rank-badge mr-3 text-danger font-weight-bold italic" style="font-size: 0.8rem;">
                            #{{ $bottomUsers->count() - $index }}
                        </div>
                        <div class="user-avatar mr-3">
                            <img src="{{ $u->avatar_url }}" class="rounded-circle shadow-sm grayscale" width="40" height="40" style="object-fit: cover; filter: grayscale(100%);">
                        </div>
                        <div class="user-info ml-2 overflow-hidden" style="min-width: 0;">
                            <h6 class="mb-0 font-weight-bold text-dark text-truncate" style="font-size: 0.85rem;">
                                {{ $u->name }}
                            </h6>
                            <div class="mt-1 d-flex align-items-center">
                                <span class="text-danger font-weight-bold" style="font-size: 8px; opacity: 0.8; white-space: nowrap;">
                                    <i class="fa fa-warning mr-1"></i>{{ $u->level }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center p-5 text-muted">Semua siswa berperilaku baik.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white border-0 text-center pb-4">
        <a href="{{ route('integrity.user.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 shadow-sm">Buka Panel Monitoring</a>
    </div>
</div>
