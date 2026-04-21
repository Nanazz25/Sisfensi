<!-- VIEW: INVENTORI (VOUCHER ANDA) -->
<div id="view-inventory" class="view-pane d-none">
    <div class="d-flex flex-column flex-md-row align-items-md-center mb-4">
        <h5 class="font-weight-bold mb-3 mb-md-0 mr-3" style="font-weight: 800; color: #333;">Gudang Inventori</h5>
        <div class="flex-grow-1 border-top d-none d-md-block" style="opacity: 0.05;"></div>
    </div>

    <!-- Filter & Search Inventory -->
    <div class="inventory-filters mb-4">
        <div class="row no-gutters bg-white p-3 shadow-xs rounded-lg border border-light">
            <div class="col-12 col-md-5 mb-3 mb-md-0 pr-md-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                    </div>
                    <input type="text" id="inventorySearch" class="form-control border-left-0" placeholder="Cari voucher...">
                </div>
            </div>
            <div class="col-6 col-md-3 mb-0 pr-2 pr-md-2">
                <select id="statusFilter" class="form-control custom-select">
                    <option value="">Semua Status</option>
                    <option value="AVAILABLE">Tersedia</option>
                    <option value="USED">Sudah Dipakai</option>
                </select>
            </div>
            <div class="col-6 col-md-4 mb-0 d-flex align-items-center">
                <select id="typeFilter" class="form-control custom-select mr-2">
                    <option value="">Semua Tipe</option>
                    @php
                        $types = $inventory->pluck('item.item_type')->unique();
                        $typeLabels = [
                            'LATE_EXEMPTION' => 'Bebas Terlambat',
                            'LEAVE_PERMISSION' => 'Izin Digital',
                            'TASK_EXTENSION' => 'Perpanjang Tugas',
                            'OTHER' => 'Lainnya'
                        ];
                    @endphp
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ $typeLabels[$type] ?? $type }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-light border shadow-xs" onclick="resetInventory()" title="Bersihkan Filter">
                    <i class="fa fa-refresh text-muted"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="row" id="inventoryGrid">
        @forelse($inventory as $token)
        <div class="col-md-6 col-xl-6 mb-4 inventory-card" 
             data-name="{{ strtolower($token->item->item_name) }}" 
             data-status="{{ $token->status }}" 
             data-type="{{ $token->item->item_type }}">
            <div class="market-item">
                @php
                    $typeIcon = 'fa-tag';
                    $typeColor = 'bg-primary-soft';
                    if($token->item->item_type === 'LATE_EXEMPTION') {
                        $typeIcon = 'fa-clock-o';
                        $typeColor = 'bg-warning-soft';
                    } elseif($token->item->item_type === 'LEAVE_PERMISSION') {
                        $typeIcon = 'fa-envelope-o';
                        $typeColor = 'bg-info-soft';
                    }
                @endphp
                
                <div class="item-status-badge">
                    <span class="item-badge-soft {{ $token->status === 'AVAILABLE' ? 'item-badge-soft-success' : 'item-badge-soft-secondary' }}">
                        {{ $token->status === 'AVAILABLE' ? 'Tersedia' : 'Digunakan' }}
                    </span>
                </div>

                <div class="item-icon-box shadow-xs {{ $typeColor }}">
                    <i class="fa {{ $typeIcon }}"></i>
                </div>

                <h6 class="item-title">{{ $token->item->item_name }}</h6>
                <p class="item-desc">{{ $token->item->description }}</p>
                
                <div class="item-footer">
                    <div class="inventory-meta">
                        @if($token->status === 'USED')
                            <i class="fa fa-history mr-1"></i> {{ $token->updated_at->format('j M, H:i') }}
                        @else
                            <i class="fa fa-calendar mr-1"></i> {{ $token->created_at->format('d M Y') }}
                        @endif
                    </div>
                    <div class="text-right">
                        <small class="font-weight-bold text-muted" style="font-size: 9px;">ID: #{{ $token->id }}</small>
                    </div>
                </div>
            </div>
        </div>
        @empty
            <div class="col-12 text-center py-5 bg-light rounded-lg border border-dashed text-muted">
                <i class="fa fa-archive d-block h4 mb-2"></i> Gudang inventori masih kosong.
            </div>
        @endforelse
    </div>
</div>
