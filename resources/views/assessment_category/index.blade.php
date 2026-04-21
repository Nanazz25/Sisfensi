@extends('layouts.app')

@section('title', 'Daftar Kategori Penilaian')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="header d-flex justify-content-between align-items-center">
                    <h2>Daftar Kategori Penilaian</h2>
                    <div class="btn-group">
                        <a href="{{ route('assessment_category.create') }}" class="btn btn-primary shadow-sm">
                            <i class="fa fa-plus mr-1"></i> Tambah Kategori
                        </a>
                    </div>
                </div>
                <div class="body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('assessment_category.index') }}" class="ajax-form row mb-3 align-items-center">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                                </div>
                                <input type="text" name="q" id="searchInput" value="{{ request('q') }}" class="form-control border-left-0" placeholder="Cari kategori...">
                            </div>
                        </div>

                        <div class="col-md-3 mb-2 mb-md-0">
                            <select name="type" class="form-control" onchange="this.form.submit()">
                                <option value="">Semua Tipe</option>
                                @foreach($types as $t)
                                    <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-2 mb-md-0">
                            <select name="is_active" class="form-control" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="col-md-2 text-md-right">
                            <a href="{{ route('assessment_category.index') }}" class="btn btn-sm btn-outline-danger btn-block rounded-pill shadow-xs d-flex align-items-center justify-content-center" style="height: 38px;">
                                <i class="fa fa-undo mr-1"></i> Reset
                            </a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-custom spacing5 mb-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Deskripsi</th>
                                    <th>Tipe</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr class="align-middle">
                                        <td class="py-3">
                                            <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.88rem;">
                                                {{ $category->name }}</h6>
                                        </td>
                                        <td class="py-3">
                                            <small class="text-muted d-block" style="font-size: 0.72rem;">
                                                {{ $category->description }}</small>
                                        </td>
                                        <td class="py-3">
                                            <span
                                                class="badge badge-{{ $category->type === 'sikap' ? 'primary' : 'success' }}">{{ ucfirst($category->type) }}</span>
                                        </td>
                                        <td class="py-3">
                                            <span
                                                class="badge badge-{{ $category->is_active ? 'success' : 'secondary' }}">{{ $category->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('assessment_category.edit', $category) }}"
                                                    class="btn btn-sm btn-outline-warning mr-2">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('assessment_category.destroy', $category) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Belum ada kategori penilaian</h5>
                                            <p class="text-muted">Klik "Tambah Kategori" untuk membuat kategori penilaian
                                                pertama Anda.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('afterAppScripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.querySelector('form');
        if (searchInput && filterForm) {
            let timeout = null;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => filterForm.submit(), 500);
            });
            if (searchInput.value) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }
        }
    });
</script>
@endsection
