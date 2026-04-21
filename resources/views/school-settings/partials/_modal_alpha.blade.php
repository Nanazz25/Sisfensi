<!-- Modal Daftar Siswa Alpha -->
<div class="modal fade" id="modalAlphaResult" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
            <div class="modal-header bg-white py-3 px-4 border-0">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary-soft text-primary mr-3 rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fa fa-users"></i>
                    </div>
                    <div>
                        <h6 class="modal-title font-weight-bold text-dark mb-0">Hasil Sinkronisasi Alpha</h6>
                        <p class="text-muted mb-0 style" style="font-size: 0.7rem;">{{ session('yesterdayFormatted') ?? 'Rekap Absensi Otomatis' }}</p>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="position: absolute; right: 20px; top: 15px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0">
                <div class="px-4 pb-3">
                    <div class="input-group shadow-xs border rounded overflow-hidden bg-light">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent border-0 pl-3"><i class="fa fa-search text-muted small"></i></span>
                        </div>
                        <input type="text" id="searchAlphaStudent" class="form-control bg-transparent border-0 py-2 font-weight-500" style="font-size: 0.85rem;" placeholder="Cari nama atau kelas...">
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 40vh; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="alphaStudentTable">
                        <thead>
                            <tr class="bg-light">
                                <th class="border-0 py-2 px-4 text-muted small text-uppercase font-weight-bold" style="letter-spacing: 0.5px; font-size: 0.65rem;">Nama Siswa</th>
                                <th class="border-0 py-2 px-4 text-muted small text-uppercase font-weight-bold" style="letter-spacing: 0.5px; font-size: 0.65rem;">Kelas</th>
                                <th class="border-0 py-2 px-4 text-muted small text-uppercase font-weight-bold" style="letter-spacing: 0.5px; font-size: 0.65rem;">Wali Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(session('alpha_data'))
                                @foreach(session('alpha_data') as $student)
                                    <tr>
                                        <td class="font-weight-600 py-3 px-4 text-dark small">{{ $student['name'] }}</td>
                                        <td class="py-3 px-4">
                                            <span class="badge badge-primary-soft text-primary px-2 py-1 rounded font-weight-bold" style="font-size: 0.65rem;">{{ $student['rombel'] }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-muted small">{{ $student['walas'] ?? '-' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="stats-info">
                    <span class="text-muted small">Total Siswa Alpha:</span>
                    <span class="badge badge-dark px-2 py-1 rounded ml-1 shadow-sm font-weight-bold">{{ session('alpha_data') ? count(session('alpha_data')) : 0 }}</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm px-4 font-weight-bold rounded" data-dismiss="modal">TUTUP</button>
            </div>
        </div>
    </div>
</div>
