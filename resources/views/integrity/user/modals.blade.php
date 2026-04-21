<!-- MODAL: Panduan Mendapat Poin -->
<div class="modal fade" id="rulesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0" style="border-radius: 24px; max-height: 85vh;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" style="font-weight: 800;">
                    <i class="fa fa-book text-info mr-2"></i>Panduan Poin
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4">Pelajari berbagai cara untuk mengumpulkan poin integritas atau hal-hal yang harus dihindari.</p>
                
                <!-- Filter & Search Section -->
                <div class="rules-header mb-4">
                    <div class="input-group mb-3 shadow-xs">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0" style="border-radius: 12px 0 0 12px;"><i class="fa fa-search text-muted small"></i></span>
                        </div>
                        <input type="text" id="ruleSearch" class="form-control border-left-0" placeholder="Cari panduan (misal: tepat waktu, telat...)" style="border-radius: 0 12px 12px 0; height: 45px; font-size: 0.9rem;">
                    </div>

                    <div class="nav nav-pills nav-fill bg-light p-1 rounded-pill" id="ruleTabs">
                        <a class="nav-link active rounded-pill small font-weight-bold py-2" data-type="all" href="javascript:void(0)">Semua</a>
                        <a class="nav-link rounded-pill small font-weight-bold py-2 text-success" data-type="plus" href="javascript:void(0)">Reward (+)</a>
                        <a class="nav-link rounded-pill small font-weight-bold py-2 text-danger" data-type="minus" href="javascript:void(0)">Penalti (-)</a>
                    </div>
                </div>

                <!-- Rules List -->
                <div class="rules-list-container pr-2" style="overflow-y: auto; max-height: 45vh;">
                    <div id="rulesListContainer">
                        @foreach($pointRules as $rule)
                            <div class="rule-item-box mb-3 p-3 bg-white border rounded-xl transition-all shadow-xs" 
                                 data-name="{{ strtolower($rule->rule_name) }}" 
                                 data-type="{{ $rule->point_modifier > 0 ? 'plus' : 'minus' }}"
                                 style="border-radius: 15px;">
                                <div class="d-flex align-items-center">
                                    <div class="rule-icon-box mr-3 {{ $rule->point_modifier > 0 ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' }}" 
                                         style="width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fa {{ $rule->point_modifier > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} small"></i>
                                    </div>
                                    <div class="flex-grow-1 pr-3">
                                        <div class="font-weight-bold text-dark" style="font-size: 0.9rem; line-height: 1.3;">{{ $rule->rule_name }}</div>
                                        <div class="d-flex align-items-center mt-1">
                                            <span class="badge badge-light border text-muted px-2 py-0 mr-2" style="font-size: 9px; font-weight: 500;">
                                                {{ $rule->trigger_type == 'manual' ? 'Penilaian Guru' : 'Sistem Otomatis' }}
                                            </span>
                                            @if($rule->attendance_type != 'all')
                                                <span class="text-muted" style="font-size: 9px;"><i class="fa fa-clock-o mr-1"></i>{{ ucfirst($rule->attendance_type) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="h5 mb-0 font-weight-bold {{ $rule->point_modifier > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $rule->point_modifier > 0 ? '+' : '' }}{{ $rule->point_modifier }}
                                        </div>
                                        <small class="text-muted font-weight-bold" style="font-size: 9px;">POIN</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light p-3" style="border-radius: 0 0 24px 24px;">
                <button type="button" class="btn btn-secondary btn-block rounded-pill font-weight-bold" data-dismiss="modal">Tutup Panduan</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ruleSearch');
    const tabLinks = document.querySelectorAll('#ruleTabs .nav-link');
    const ruleItems = document.querySelectorAll('.rule-item-box');
    let activeType = 'all';

    function filterRules() {
        const query = searchInput.value.toLowerCase();
        let visibleCount = 0;

        ruleItems.forEach(item => {
            const matchesSearch = item.getAttribute('data-name').includes(query);
            const matchesTab = activeType === 'all' || item.getAttribute('data-type') === activeType;

            if (matchesSearch && matchesTab) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Add empty message if needed
        const existingMsg = document.getElementById('noRulesMsg');
        if (visibleCount === 0) {
            if (!existingMsg) {
                const msg = document.createElement('div');
                msg.id = 'noRulesMsg';
                msg.className = 'text-center py-5 text-muted';
                msg.innerHTML = '<i class="fa fa-search fa-2x mb-2 opacity-50"></i><p class="small">Tidak ada aturan yang cocok</p>';
                document.getElementById('rulesListContainer').appendChild(msg);
            }
        } else if (existingMsg) {
            existingMsg.remove();
        }
    }

    searchInput.addEventListener('input', filterRules);

    tabLinks.forEach(link => {
        link.addEventListener('click', function() {
            tabLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            activeType = this.getAttribute('data-type');
            filterRules();
        });
    });
});
</script>

<!-- MODAL: Detail Lengkap Riwayat Mutasi -->
<div class="modal fade" id="fullHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content border-0" style="border-radius: 24px;">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title font-weight-bold" style="font-weight: 800;">Riwayat Lengkap</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                @foreach($mutations as $m)
                    <div class="d-flex align-items-center px-4 py-3 border-bottom">
                         <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center {{ $m->amount > 0 ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' }}" style="width: 35px; height: 35px; font-size: 10px;">
                            <i class="fa {{ $m->amount > 0 ? 'fa-plus' : 'fa-minus' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 font-weight-bold small text-dark">{{ $m->description }}</h6>
                            <small class="text-muted text-xs">{{ $m->created_at->format('d M Y, H:i') }}</small>
                        </div>
                        <div class="text-right ml-2 font-weight-bold {{ $m->amount > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $m->amount > 0 ? '+' : '' }}{{ $m->amount }}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="modal-footer border-0">
                <div class="w-100 text-center">{{ $mutations->links() }}</div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Inventori Voucher Saya -->
<div class="modal fade" id="inventoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" style="font-weight: 800;">
                    <i class="fa fa-briefcase text-warning mr-2"></i>Inventori Voucher
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body bg-light mt-3 p-4">
                @forelse($inventory as $token)
                    <div class="card mb-3 border-0 shadow-xs rounded-lg p-3">
                         <div class="d-flex justify-content-between mb-2">
                            <span class="badge {{ $token->status === 'AVAILABLE' ? 'badge-success' : 'badge-secondary' }} rounded-pill text-xs px-2">
                                {{ $token->status }}
                            </span>
                            <small class="text-muted text-xs">{{ $token->created_at->format('d M Y') }}</small>
                        </div>
                        <h6 class="font-weight-bold mb-1" style="font-size: 0.9rem;">{{ $token->item->item_name }}</h6>
                        <small class="text-muted d-block mb-2">{{ $token->item->description }}</small>
                        @if($token->status === 'USED')
                             <small class="text-primary font-weight-bold">
                                <i class="fa fa-check-circle"></i> Digunakan: {{ $token->updated_at->format('d/m/y H:i') }}
                             </small>
                        @else
                             <small class="text-success font-weight-bold">
                                <i class="fa fa-clock-o"></i> Siap digunakan otomatis
                             </small>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fa fa-folder-open-o fa-3x text-light mb-3"></i>
                        <p class="text-muted">Inventori masih kosong.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Daftar Tingkatan Peringkat (Rank Levels) -->
<div class="modal fade" id="rankLevelsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" style="font-weight: 800;">
                    <i class="fa fa-line-chart text-primary mr-2"></i>Tingkatan Peringkat
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4">Urutan gelar siswa berdasarkan jumlah saldo poin mereka.</p>
                
                <div class="table-responsive">
                    <table class="table table-borderless table-sm">
                        <thead>
                            <tr class="text-muted small border-bottom">
                                <th>Gelar Peringkat</th>
                                <th class="text-right">Minimal Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $ranks = [
                                    ['name' => 'Legenda Sisfensi', 'points' => '10.000', 'color' => '#6f42c1'],
                                    ['name' => 'Maestro Integritas', 'points' => '5.000', 'color' => '#007bff'],
                                    ['name' => 'Ksatria Adab', 'points' => '1.000', 'color' => '#17a2b8'],
                                    ['name' => 'Duta Kedisiplinan', 'points' => '500', 'color' => '#20c997'],
                                    ['name' => 'Aset Sekolah', 'points' => '250', 'color' => '#28a745'],
                                    ['name' => 'Integritas Elite', 'points' => '100', 'color' => '#ffc107'],
                                    ['name' => 'Siswa Teladan', 'points' => '50', 'color' => '#fd7e14'],
                                    ['name' => 'Siswa Reguler', 'points' => '0', 'color' => '#6c757d'],
                                    ['name' => 'Butuh Pembinaan', 'points' => '< 0', 'color' => '#dc3545'],
                                ];
                            @endphp

                            @foreach($ranks as $r)
                                <tr class="{{ $user->level == $r['name'] ? 'bg-primary-soft rounded' : '' }}">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2" style="width: 8px; height: 8px; border-radius: 50%; background-color: {{ $r['color'] }};"></div>
                                            <span class="font-weight-bold {{ $user->level == $r['name'] ? 'text-primary' : 'text-dark' }}">{{ $r['name'] }}</span>
                                            @if($user->level == $r['name'])
                                                <small class="ml-2 badge badge-primary py-0 px-2" style="font-size: 8px;">Pangkat Saat Ini</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 text-right font-weight-bold">{{ $r['points'] }} P</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info mt-3 rounded-lg border-0 small">
                    <i class="fa fa-info-circle mr-1"></i> Teruslah berbuat baik dan jaga kedisiplinan Anda untuk menaikkan peringkat!
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Konfirmasi Pembelian Item -->
<div class="modal fade" id="buyConfirmationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-soft rounded-circle" style="width: 80px; height: 80px;">
                        <i class="fa fa-shopping-cart text-primary h2 mb-0"></i>
                    </div>
                </div>
                <h4 class="font-weight-bold mb-2">Konfirmasi Penukaran</h4>
                <p class="text-muted">Apakah Anda yakin ingin menukarkan poin Anda untuk item ini?</p>
                
                <div class="bg-light p-3 rounded-lg border mb-4 text-left">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Item:</span>
                        <span class="font-weight-bold text-dark" id="confirmItemName">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Biaya:</span>
                        <span class="font-weight-bold text-primary" id="confirmItemCost">-</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <button type="button" class="btn btn-light btn-block rounded-pill font-weight-bold shadow-sm" data-dismiss="modal">Batal</button>
                    </div>
                    <div class="col-6">
                        <form id="confirmBuyForm" action="" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-bold shadow-sm">Ya, Tukar!</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
