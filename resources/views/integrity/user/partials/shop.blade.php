<!-- VIEW: Marketplace Integritas (PREMIUM CARDS) -->
<div id="view-shop" class="view-pane d-none">
    <div class="d-flex flex-column flex-md-row align-items-md-center mb-4">
        <h5 class="font-weight-bold mb-3 mb-md-0 mr-3" style="font-weight: 800; color: #333;">Marketplace Integritas</h5>
        <div class="flex-grow-1 border-top d-none d-md-block" style="opacity: 0.05;"></div>
    </div>

    <!-- Filter & Search Shop -->
    <div class="shop-filters mb-4">
        <div class="row no-gutters bg-white p-3 shadow-xs rounded-lg border border-light">
            <div class="col-12 col-md-7 mb-3 mb-md-0 pr-md-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                    </div>
                    <input type="text" id="shopSearch" class="form-control border-left-0" placeholder="Cari item reward...">
                </div>
            </div>
            <div class="col-12 col-md-5 mb-0 d-flex align-items-center">
                <select id="shopTypeFilter" class="form-control custom-select mr-2">
                    <option value="">Semua Kategori</option>
                    @php
                        $shopTypes = $items->pluck('item_type')->unique();
                        $typeLabels = [
                            'LATE_EXEMPTION' => 'Bebas Terlambat',
                            'LEAVE_PERMISSION' => 'Izin Digital',
                            'TASK_EXTENSION' => 'Perpanjang Tugas',
                            'OTHER' => 'Lainnya'
                        ];
                    @endphp
                    @foreach($shopTypes as $type)
                        <option value="{{ $type }}">{{ $typeLabels[$type] ?? $type }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-light border shadow-xs" onclick="resetShop()" title="Bersihkan Filter">
                    <i class="fa fa-refresh text-muted"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="row" id="marketGrid">
        @foreach($items as $item)
        <div class="col-md-6 col-xl-4 mb-4 shop-card" 
             data-name="{{ strtolower($item->item_name) }}"
             data-type="{{ $item->item_type }}">
            <div class="market-item">
                @php
                    $icon = 'fa-tag';
                    $typeColor = 'bg-primary-soft';
                    if(str_contains(strtolower($item->item_name), 'telat')) {
                        $icon = 'fa-clock-o';
                        $typeColor = 'bg-warning-soft';
                    } elseif(str_contains(strtolower($item->item_name), 'izin')) {
                        $icon = 'fa-envelope-o';
                        $typeColor = 'bg-info-soft';
                    } elseif(str_contains(strtolower($item->item_name), 'tugas')) {
                        $icon = 'fa-tasks';
                        $typeColor = 'bg-success-soft';
                    }
                @endphp
                <div class="item-icon-box shadow-xs {{ $typeColor }}"><i class="fa {{ $icon }}"></i></div>
                <h6 class="item-title">{{ $item->item_name }}</h6>
                <p class="item-desc">{{ $item->description }}</p>
                
                <div class="item-footer">
                    <div class="item-price">{{ number_format($item->point_cost) }}<small>P</small></div>
                    @if($user->current_points >= $item->point_cost)
                        <button type="button" 
                                class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs buy-item-btn" 
                                data-toggle="modal" 
                                data-target="#buyConfirmationModal"
                                data-name="{{ $item->item_name }}"
                                data-cost="{{ number_format($item->point_cost) }} P"
                                data-url="{{ route('integrity.market.buy', $item->id) }}">
                            Tukar
                        </button>
                    @else
                        <div class="text-right">
                            <span class="badge badge-light text-danger border-0 small px-0" style="font-size: 10px;">
                                <i class="fa fa-lock mr-1"></i> Poin Kurang
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
