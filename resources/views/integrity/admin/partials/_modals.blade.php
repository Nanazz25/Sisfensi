<!-- Modal Add Rule -->
<div class="modal fade" id="addRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('integrity.rules.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Konfigurasi Aturan Integritas</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Nama Aturan</label>
                                <input type="text" name="rule_name" class="form-control rounded-pill" placeholder="Cth: Tugas Tepat Waktu" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Kategori Aturan</label>
                                <select name="trigger_type" class="form-control rounded-pill border-primary" id="triggerTypeSelect">
                                    <option value="attendance">Otomatis (Presensi)</option>
                                    <option value="manual">Manual (Perilaku/Tindakan)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Kategori Absensi</label>
                                <select name="attendance_type" class="form-control rounded-pill border-primary">
                                    <option value="all">Semua (Global)</option>
                                    <option value="masuk">Absen Masuk (Harian)</option>
                                    <option value="pelajaran">Absen Mapel (Kelas)</option>
                                    <option value="pulang">Absen Pulang</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="target_role" value="siswa">
                    </div>

                    <div class="p-4 bg-light rounded-lg border mb-3">
                        <h6 class="font-weight-bold mb-3"><i class="fa fa-info-circle mr-1 text-primary"></i> Pengaturan Logika Aturan</h6>
                        
                        <!-- Attendance Logic -->
                        <div id="attendanceLogic" class="attendance-wrapper">
                            <div class="form-group">
                                <label class="small font-weight-bold">Basis Logika</label>
                                <select name="basis_type" class="form-control rounded-pill basis-select">
                                    <option value="status">Berdasarkan Status (Hadir/Alfa/dll)</option>
                                    <option value="setting">Berdasarkan Jam Sekolah (Dinamis)</option>
                                    <option value="schedule" class="d-none schedule-option">Berdasarkan Jadwal Mapel</option>
                                    <option value="fixed">Berdasarkan Jam Pasti (Manual)</option>
                                </select>
                            </div>

                            <!-- Basis: Status -->
                            <div class="logic-group status-logic d-none">
                                <label class="small font-weight-bold">Pilih Status</label>
                                <select name="condition_value" class="form-control rounded-pill">
                                    <option value="hadir">Hadir</option>
                                    <option value="terlambat">Terlambat</option>
                                    <option value="alfa">Alfa</option>
                                    <option value="izin">Izin/Sakit</option>
                                </select>
                                <input type="hidden" name="condition_operator" value="=">
                            </div>

                            <!-- Basis: Schedule (Mapel) -->
                            <div class="logic-group schedule-logic d-none">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="small font-weight-bold">Patokan Jadwal</label>
                                        <select name="reference_key" class="form-control rounded-pill" disabled>
                                            <option value="jam_mulai_mapel">Jam Mulai Mapel</option>
                                            <option value="jam_selesai_mapel">Jam Selesai Mapel</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold">Kondisi</label>
                                        <select name="condition_operator" class="form-control rounded-pill" disabled>
                                            <option value=">">Lebih dari</option>
                                            <option value="<">Kurang dari</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small font-weight-bold">Offset (Menit)</label>
                                        <div class="input-group">
                                            <input type="number" name="offset_minutes" class="form-control rounded-pill" value="0" disabled>
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-transparent border-0 small">mnt</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Basis: Setting -->
                            <div class="logic-group setting-logic d-none">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="small font-weight-bold">Patokan Jam</label>
                                        <select name="reference_key" class="form-control rounded-pill" disabled>
                                            @foreach($settings as $s)
                                                <option value="{{ $s->key }}">
                                                    @if($s->key == 'jam_masuk') Jam Masuk (Terlambat)
                                                    @elseif($s->key == 'jam_masuk_toleransi') Batas Kehadiran (Alpha)
                                                    @else Jam Pulang Sekolah
                                                    @endif
                                                    ({{ \Carbon\Carbon::parse($s->value)->format('H:i') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold">Kondisi</label>
                                        <select name="condition_operator" class="form-control rounded-pill" disabled>
                                            <option value=">">Lebih dari</option>
                                            <option value="<">Kurang dari</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small font-weight-bold">Offset (Menit)</label>
                                        <div class="input-group">
                                            <input type="number" name="offset_minutes" class="form-control rounded-pill" value="0" disabled>
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-transparent border-0 small">mnt</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Basis: Fixed -->
                            <div class="logic-group fixed-logic d-none">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="small font-weight-bold">Operator</label>
                                        <select name="condition_operator" class="form-control rounded-pill" disabled>
                                            <option value="<">Sebelum Jam</option>
                                            <option value=">">Sesudah Jam</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small font-weight-bold">Waktu</label>
                                        <input type="time" name="condition_value" class="form-control rounded-pill" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Only -->
                        <div id="manualLogic" class="manual-wrapper">
                            <div class="p-3 border-left border-warning bg-white rounded shadow-xs" style="border-width: 5px !important;">
                                <p class="mb-1 text-dark font-weight-bold">Aturan Berbasis Perilaku / Tindakan (Non-Waktu)</p>
                                <p class="text-muted small mb-0">
                                    Aturan ini tidak menggunakan logika waktu. Guru akan memilih aturan ini secara manual 
                                    saat melakukan penilaian karakter atau presensi.
                                </p>
                                <input type="hidden" name="condition_operator" value="MANUAL" disabled>
                                <input type="hidden" name="condition_value" value="N/A" disabled>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between">
                            <span class="font-weight-bold text-uppercase small">Jumlah Poin Fisik:</span>
                            <input type="number" name="point_modifier" class="form-control rounded-pill shadow-sm text-center font-weight-bold" style="width: 120px;" value="1" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow">Simpan Aturan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Rule -->
<div class="modal fade" id="editRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="editRuleForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Update Aturan Integritas</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Nama Aturan</label>
                                <input type="text" name="rule_name" id="edit_rule_name" class="form-control rounded-pill" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Kategori Aturan</label>
                                <select name="trigger_type" id="edit_rule_trigger" class="form-control rounded-pill border-primary">
                                    <option value="attendance">Otomatis (Presensi)</option>
                                    <option value="manual">Manual (Perilaku/Tindakan)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Kategori Absensi</label>
                                <select name="attendance_type" id="edit_attendance_type" class="form-control rounded-pill border-primary">
                                    <option value="all">Semua (Global)</option>
                                    <option value="masuk">Absen Masuk (Harian)</option>
                                    <option value="pelajaran">Absen Mapel (Kelas)</option>
                                    <option value="pulang">Absen Pulang</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="target_role" value="siswa">
                    </div>

                    <div class="p-4 bg-light rounded-lg border mb-3">
                        <h6 class="font-weight-bold mb-3"><i class="fa fa-info-circle mr-1 text-primary"></i> Pengaturan Logika Aturan</h6>
                        
                        <div class="attendance-wrapper">
                            <div class="form-group">
                                <label class="small font-weight-bold">Basis Logika</label>
                                <select name="basis_type" class="form-control rounded-pill basis-select">
                                    <option value="status">Berdasarkan Status (Hadir/Alfa/dll)</option>
                                    <option value="setting">Berdasarkan Jam Sekolah (Dinamis)</option>
                                    <option value="schedule" class="d-none schedule-option">Berdasarkan Jadwal Mapel</option>
                                    <option value="fixed">Berdasarkan Jam Pasti (Manual)</option>
                                </select>
                            </div>

                            <div class="logic-group status-logic d-none">
                                <label class="small font-weight-bold">Pilih Status</label>
                                <select name="condition_value" class="form-control rounded-pill">
                                    <option value="hadir">Hadir</option>
                                    <option value="terlambat">Terlambat</option>
                                    <option value="alfa">Alfa</option>
                                    <option value="izin">Izin/Sakit</option>
                                </select>
                                <input type="hidden" name="condition_operator" value="=">
                            </div>

                            <div class="logic-group schedule-logic d-none">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="small font-weight-bold">Patokan Jadwal</label>
                                        <select name="reference_key" class="form-control rounded-pill">
                                            <option value="jam_mulai_mapel">Jam Mulai Mapel</option>
                                            <option value="jam_selesai_mapel">Jam Selesai Mapel</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold">Kondisi</label>
                                        <select name="condition_operator" class="form-control rounded-pill">
                                            <option value=">">Lebih dari</option>
                                            <option value="<">Kurang dari</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small font-weight-bold">Offset (Menit)</label>
                                        <div class="input-group">
                                            <input type="number" name="offset_minutes" class="form-control rounded-pill" value="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-transparent border-0 small">mnt</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="logic-group setting-logic d-none">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="small font-weight-bold">Patokan Jam</label>
                                        <select name="reference_key" class="form-control rounded-pill" disabled>
                                            @foreach($settings as $s)
                                                <option value="{{ $s->key }}">
                                                    @if($s->key == 'jam_masuk') Jam Masuk (Terlambat)
                                                    @elseif($s->key == 'jam_masuk_toleransi') Batas Kehadiran (Alpha)
                                                    @else Jam Pulang Sekolah
                                                    @endif
                                                    ({{ \Carbon\Carbon::parse($s->value)->format('H:i') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold">Kondisi</label>
                                        <select name="condition_operator" class="form-control rounded-pill" disabled>
                                            <option value=">">Lebih dari</option>
                                            <option value="<">Kurang dari</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small font-weight-bold">Offset (Menit)</label>
                                        <div class="input-group">
                                            <input type="number" name="offset_minutes" class="form-control rounded-pill" value="0" disabled>
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-transparent border-0 small">mnt</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="logic-group fixed-logic d-none">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="small font-weight-bold">Operator</label>
                                        <select name="condition_operator" class="form-control rounded-pill" disabled>
                                            <option value="<">Sebelum Jam</option>
                                            <option value=">">Sesudah Jam</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small font-weight-bold">Waktu</label>
                                        <input type="time" name="condition_value" class="form-control rounded-pill" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="manual-wrapper">
                            <div class="p-3 border-left border-warning bg-white rounded shadow-xs" style="border-width: 5px !important;">
                                <p class="mb-1 text-dark font-weight-bold">Aturan Berbasis Perilaku / Tindakan (Non-Waktu)</p>
                                <p class="text-muted small mb-0">Aturan ini tidak menggunakan logika waktu.</p>
                                <input type="hidden" name="condition_operator" value="MANUAL" disabled>
                                <input type="hidden" name="condition_value" value="N/A" disabled>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between">
                            <span class="font-weight-bold text-uppercase small">Jumlah Poin Fisik:</span>
                            <input type="number" name="point_modifier" id="edit_rule_modifier" class="form-control rounded-pill shadow-sm text-center font-weight-bold" style="width: 120px;" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-info rounded-pill px-4 shadow">Update Aturan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Item -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('integrity.items.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Item Marketplace Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Item</label>
                        <input type="text" name="item_name" class="form-control rounded-pill" placeholder="Cth: Voucher Bebas Telat" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Tipe Fitur Item</label>
                        <select name="item_type" class="form-control rounded-pill border-primary item-type-select">
                            <option value="PHYSICAL_REWARD">Reward Fisik (Kantin/Koperasi)</option>
                            <option value="LATE_EXEMPTION">Bebas Terlambat (Fungsi Otomatis)</option>
                        </select>
                    </div>
                    <div class="form-group item-effect-group d-none">
                        <label class="font-weight-bold">Efek Keterlambatan (Maks. Menit)</label>
                        <div class="input-group">
                            <input type="number" name="effect_value" class="form-control rounded-pill" placeholder="Cth: 15">
                            <div class="input-group-append"><span class="input-group-text bg-transparent border-0">mnt</span></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Harga Poin</label>
                        <input type="number" name="point_cost" class="form-control rounded-pill" placeholder="100" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Limit Beli</label>
                                <input type="number" name="purchase_limit" class="form-control rounded-pill" placeholder="Cth: 1">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Per Periode</label>
                                <select name="purchase_period" class="form-control rounded-pill">
                                    <option value="none">Tanpa Batas</option>
                                    <option value="daily">Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan kegunaan item ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow">Publish Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Item -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form id="editItemForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Update Item Marketplace</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Item</label>
                        <input type="text" name="item_name" id="edit_item_name" class="form-control rounded-pill" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Tipe Fitur Item</label>
                        <select name="item_type" id="edit_item_type" class="form-control rounded-pill border-primary item-type-select">
                            <option value="PHYSICAL_REWARD">Reward Fisik (Kantin/Koperasi)</option>
                            <option value="LATE_EXEMPTION">Bebas Terlambat (Fungsi Otomatis)</option>
                        </select>
                    </div>
                    <div class="form-group item-effect-group d-none">
                        <label class="font-weight-bold">Efek Keterlambatan (Maks. Menit)</label>
                        <div class="input-group">
                            <input type="number" name="effect_value" id="edit_item_effect" class="form-control rounded-pill">
                            <div class="input-group-append"><span class="input-group-text bg-transparent border-0">mnt</span></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Harga Poin</label>
                        <input type="number" name="point_cost" id="edit_item_cost" class="form-control rounded-pill" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Limit Beli</label>
                                <input type="number" name="purchase_limit" id="edit_item_limit" class="form-control rounded-pill" placeholder="Kosongkan jika tak terbatas">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Per Periode</label>
                                <select name="purchase_period" id="edit_item_period" class="form-control rounded-pill">
                                    <option value="none">Tanpa Batas</option>
                                    <option value="daily">Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Deskripsi</label>
                        <textarea name="description" id="edit_item_desc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-info rounded-pill px-4 shadow">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('components.modal-delete', [
    'title' => 'Hapus Aturan Integritas',
    'message' => 'Yakin ingin menghapus aturan ini? Siswa yang mendapatkan poin dari aturan ini mungkin akan terpengaruh.'
])
