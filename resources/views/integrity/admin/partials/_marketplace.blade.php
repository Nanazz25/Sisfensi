<div class="card shadow-sm border-0">
    <div class="header d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 px-4">
        <div class="mb-3 mb-md-0">
            <h2 class="font-weight-bold mb-0">Katalog Reward (Marketplace)</h2>
        </div>
        <div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-md-auto" data-toggle="modal" data-target="#addItemModal">
                <i class="fa fa-plus-circle mr-1"></i> Item Baru
            </button>
        </div>
    </div>
    <div class="body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Tipe</th>
                        <th>Limit Beli</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>
                            <h6 class="mb-0 font-weight-bold">{{ $item->item_name }}</h6>
                            <small class="text-muted">{{ $item->description }}</small>
                        </td>
                        <td>
                            @if($item->item_type === 'LATE_EXEMPTION')
                                <span class="badge badge-soft-success">Bebas Terlambat</span>
                                @if($item->effect_value)
                                    <div class="small text-muted font-italic">Max: {{ $item->effect_value }}mnt</div>
                                @endif
                            @else
                                <span class="badge badge-soft-secondary">Reward Fisik</span>
                            @endif
                        </td>
                        <td>
                            @if($item->purchase_limit > 0 && $item->purchase_period !== 'none')
                                <span class="text-dark font-weight-bold">{{ $item->purchase_limit }}x</span>
                                <small class="text-muted">/ {{ ucfirst($item->purchase_period) }}</small>
                            @else
                                <span class="text-muted small">Tanpa Batas</span>
                            @endif
                        </td>
                        <td><span class="text-primary font-weight-bold">{{ number_format($item->point_cost) }} P</span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info btn-edit-item" 
                                    data-url="{{ route('integrity.items.update', $item->id) }}"
                                    data-item="{{ json_encode($item) }}">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-item" 
                                    data-url="{{ route('integrity.items.destroy', $item->id) }}"
                                    data-name="{{ $item->item_name }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
