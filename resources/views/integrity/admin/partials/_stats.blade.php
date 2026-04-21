<div class="row clearfix mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="body text-center">
                <i class="fa fa-magic fa-2x text-info mb-2"></i>
                <h5 class="mb-0 font-weight-bold">{{ $rules->count() }}</h5>
                <small class="text-muted text-uppercase">Total Aturan</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="body text-center">
                <i class="fa fa-shopping-cart fa-2x text-primary mb-2"></i>
                <h5 class="mb-0 font-weight-bold">{{ $items->count() }}</h5>
                <small class="text-muted text-uppercase">Item Marketplace</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="body text-center">
                <i class="fa fa-users fa-2x text-success mb-2"></i>
                <h5 class="mb-0 font-weight-bold">{{ $topUsers->count() }}</h5>
                <small class="text-muted text-uppercase">Siswa Terdata</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="body text-center">
                <i class="fa fa-warning fa-2x text-danger mb-2"></i>
                <h5 class="mb-0 font-weight-bold">{{ $bottomUsers->where('total_points', '<', 0)->count() }}</h5>
                <small class="text-muted text-uppercase">Siswa Bermasalah</small>
            </div>
        </div>
    </div>
</div>
