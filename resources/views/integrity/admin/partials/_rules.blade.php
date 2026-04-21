<div class="card shadow-sm border-0 mb-4">
    <div class="header d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 px-4">
        <div class="mb-3 mb-md-0">
            <h2 class="font-weight-bold mb-0">Mesin Aturan Dinamis (Rule Engine)</h2>
            <p class="text-muted small mb-0">Kelola bagaimana poin diberikan secara otomatis atau manual.</p>
        </div>
        <div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-md-auto" data-toggle="modal" data-target="#addRuleModal">
                <i class="fa fa-plus-circle mr-1"></i> Tambah Aturan
            </button>
        </div>
    </div>
    <div class="body">
        <!-- Filters -->
        <form method="GET" action="{{ route('integrity.admin.index') }}" class="mb-4">
            <input type="hidden" name="order" value="{{ request('order', 'desc') }}">
            <div class="row no-gutters bg-light p-3 rounded-lg border">
                <!-- Search -->
                <div class="col-md-3 pr-md-2 mb-2 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="q" class="form-control border-left-0" placeholder="Cari nama aturan..." value="{{ request('q') }}">
                    </div>
                </div>

                <!-- trigger_type -->
                <div class="col-6 col-md-2 pr-2 mb-2 mb-md-0">
                    <select name="trigger_type" class="form-control" onchange="this.form.submit()">
                        <option value="">Tipe Pemicu</option>
                        <option value="attendance" {{ request('trigger_type') == 'attendance' ? 'selected' : '' }}>Otomatis</option>
                        <option value="manual" {{ request('trigger_type') == 'manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>

                <!-- attendance_type -->
                <div class="col-6 col-md-2 pr-md-2 mb-2 mb-md-0">
                    <select name="attendance_type" class="form-control" onchange="this.form.submit()">
                        <option value="">Kategori</option>
                        <option value="masuk" {{ request('attendance_type') == 'masuk' ? 'selected' : '' }}>Harian</option>
                        <option value="pelajaran" {{ request('attendance_type') == 'pelajaran' ? 'selected' : '' }}>Mapel</option>
                        <option value="pulang" {{ request('attendance_type') == 'pulang' ? 'selected' : '' }}>Pulang</option>
                    </select>
                </div>

                <!-- point_type -->
                <div class="col-6 col-md-2 pr-2 mb-2 mb-md-0">
                    <select name="point_type" class="form-control" onchange="this.form.submit()">
                        <option value="">Jenis Poin</option>
                        <option value="plus" {{ request('point_type') == 'plus' ? 'selected' : '' }}>Reward (+)</option>
                        <option value="minus" {{ request('point_type') == 'minus' ? 'selected' : '' }}>Penalti (-)</option>
                    </select>
                </div>

                <!-- sort -->
                <div class="col-6 col-md-2 pr-md-2 mb-0">
                    <select name="sort" class="form-control" onchange="this.form.submit()">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Urutan: Input</option>
                        <option value="rule_name" {{ request('sort') == 'rule_name' ? 'selected' : '' }}>Urutan: Nama</option>
                        <option value="point_modifier" {{ request('sort') == 'point_modifier' ? 'selected' : '' }}>Urutan: Poin</option>
                    </select>
                </div>

                <!-- Reset -->
                <div class="col-12 col-md-1 mt-2 mt-md-0">
                    <a href="{{ route('integrity.admin.index') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center" style="height: 38px;" title="Reset Filter">
                        <i class="fa fa-refresh"></i>
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-custom spacing5">
                <thead>
                    <tr>
                        <th>Nama Aturan</th>
                        <th>Target Role</th>
                        <th>Kategori</th>
                        <th>Kondisi (Logic)</th>
                        <th>Modifier Poin</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                    <tr>
                        <td><span class="font-weight-bold">{{ $rule->rule_name }}</span></td>
                        <td><span class="badge badge-info">{{ strtoupper($rule->target_role) }}</span></td>
                        <td>
                            @php
                                $typeMap = [
                                    'all' => ['label' => 'Semua', 'class' => 'badge-dark'],
                                    'masuk' => ['label' => 'Harian', 'class' => 'badge-primary'],
                                    'pelajaran' => ['label' => 'Mapel', 'class' => 'badge-warning'],
                                    'pulang' => ['label' => 'Pulang', 'class' => 'badge-info'],
                                ];
                                $t = $typeMap[$rule->attendance_type] ?? $typeMap['all'];
                            @endphp
                            <span class="badge {{ $t['class'] }}">{{ $t['label'] }}</span>
                        </td>
                        <td>
                            <span class="font-weight-bold text-dark">
                                @if($rule->trigger_type === 'manual')
                                    Tindakan Manual (Guru)
                                @elseif($rule->basis_type === 'status')
                                    Status: <span class="badge badge-outline-primary">{{ strtoupper($rule->condition_value) }}</span>
                                @elseif($rule->basis_type === 'setting')
                                    @php
                                        $keyMap = [
                                            'jam_masuk' => 'Jam Masuk (Terlambat)', 
                                            'jam_masuk_toleransi' => 'Batas Kehadiran (Alpha)', 
                                            'jam_pulang' => 'Jam Pulang Sekolah'
                                        ];
                                        $opText = $rule->condition_operator == '<' ? 'sebelum' : 'sesudah';
                                    @endphp
                                    <span class="font-weight-bold">{{ abs($rule->offset_minutes) }} mnt</span> 
                                    <span class="text-primary">{{ $opText }}</span> 
                                    {{ $keyMap[$rule->reference_key] ?? $rule->reference_key }}
                                @elseif($rule->basis_type === 'schedule')
                                    @php
                                        $keyMap = [
                                            'jam_mulai_mapel' => 'Jam Mulai Mapel',
                                            'jam_selesai_mapel' => 'Jam Selesai Mapel',
                                        ];
                                        $opText = $rule->condition_operator == '<' ? 'sebelum' : 'sesudah';
                                    @endphp
                                    <span class="font-weight-bold">{{ abs($rule->offset_minutes) }} mnt</span> 
                                    <span class="text-primary">{{ $opText }}</span> 
                                    {{ $keyMap[$rule->reference_key] ?? $rule->reference_key }}
                                @else
                                    Jam {{ $rule->condition_operator }} {{ $rule->condition_value }}
                                @endif
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $rule->point_modifier > 0 ? 'badge-success' : 'badge-danger' }} px-3 p-2">
                                {{ $rule->point_modifier > 0 ? '+' : '' }}{{ $rule->point_modifier }} POIN
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info btn-edit-rule" 
                                    data-url="{{ route('integrity.rules.update', $rule->id) }}"
                                    data-rule="{{ json_encode($rule) }}">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-rule" 
                                    data-url="{{ route('integrity.rules.destroy', $rule->id) }}"
                                    data-name="{{ $rule->rule_name }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada aturan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $rules->links() }}
        </div>
    </div>
</div>
