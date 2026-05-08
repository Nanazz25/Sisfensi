@extends('layouts.app')

@section('title', 'Manajemen Kategori & Jawaban Otomatis')

@section('content')
<div class="row clearfix">
    <div class="col-lg-4 col-md-12">
        <div class="card">
            <div class="header">
                <h2>Tambah Kategori Baru</h2>
            </div>
            <div class="body">
                <form action="{{ route('tickets.categories.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Nama Kategori</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Masalah Jaringan" required>
                    </div>
                    <div class="form-group">
                        <label>Saran Jawaban Otomatis (Auto-Reply Suggestion)</label>
                        <textarea name="suggested_response" class="form-control" rows="5" placeholder="Teks ini akan muncul sebagai saran saat operator membalas tiket di kategori ini..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Simpan Kategori</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8 col-md-12">
        <div class="card">
            <div class="header">
                <h2>Daftar Kategori</h2>
            </div>
            <div class="body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Kategori</th>
                                <th>Pesan Otomatis</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($category->suggested_response, 100) ?: '-' }}</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#editModal{{ $category->id }}">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <form action="{{ route('tickets.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kategori ini?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('tickets.categories.update', $category->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Kategori</h5>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Nama Kategori</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Saran Jawaban Otomatis</label>
                                                        <textarea name="suggested_response" class="form-control" rows="5">{{ $category->suggested_response }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
