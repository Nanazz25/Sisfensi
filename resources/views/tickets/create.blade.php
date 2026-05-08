@extends('layouts.app')

@section('title', 'Buat Aduan Baru')

@section('afterAppStyles')
<style>
    .priority-selector { display: flex; gap: 10px; margin-top: 5px; }
    .priority-item { flex: 1; position: relative; }
    .priority-item input { position: absolute; opacity: 0; cursor: pointer; }
    .priority-label { 
        display: block; padding: 15px 10px; text-align: center; border-radius: 10px; border: 2px solid #f0f0f0; 
        cursor: pointer; transition: all 0.3s; background: #fff;
    }
    .priority-label i { display: block; font-size: 1.2rem; margin-bottom: 5px; opacity: 0.5; }
    .priority-label .title { font-weight: 800; font-size: 0.85rem; display: block; }
    .priority-label .desc { font-size: 0.65rem; color: #888; display: block; line-height: 1.2; margin-top: 4px; }

    /* Selected States */
    .priority-item input:checked + .priority-label { transform: translateY(-3px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .priority-item input:checked + .priority-label i { opacity: 1; }

    /* Low (Green) */
    .priority-item.low input:checked + .priority-label { border-color: #28a745; background: #f4fff6; color: #28a745; }
    /* Mid (Warning) */
    .priority-item.mid input:checked + .priority-label { border-color: #ffc107; background: #fffdf2; color: #856404; }
    /* High (Danger) */
    .priority-item.high input:checked + .priority-label { border-color: #dc3545; background: #fff5f6; color: #dc3545; }

    .priority-label:hover { border-color: #ddd; background: #fcfcfc; }
</style>
@endsection

@section('content')
<div class="row clearfix">
    <div class="col-lg-8 col-md-12">
        <div class="card">
            <div class="header">
                <h2>Formulir Aduan Baru</h2>
            </div>
            <div class="body">
                <form action="{{ route('tickets.store') }}" method="POST" id="ticketForm">
                    @csrf
                    <div class="form-group">
                        <label>Kategori Masalah</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Subjek / Judul</label>
                        <input type="text" name="subject" id="subject" class="form-control" placeholder="Contoh: Masalah Login Web" required>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi Kendala</label>
                        <textarea name="description" id="description" class="form-control" rows="5" placeholder="Jelaskan masalah Anda secara detail..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Prioritas Aduan</label>
                        <div class="priority-selector">
                            <label class="priority-item low">
                                <input type="radio" name="priority" value="Low" checked>
                                <div class="priority-label">
                                    <i class="fa fa-info-circle"></i>
                                    <span class="title">Rendah</span>
                                    <span class="desc">Bisa diselesaikan santai</span>
                                </div>
                            </label>
                            <label class="priority-item mid">
                                <input type="radio" name="priority" value="Mid">
                                <div class="priority-label">
                                    <i class="fa fa-exclamation-circle"></i>
                                    <span class="title">Sedang</span>
                                    <span class="desc">Butuh perhatian segera</span>
                                </div>
                            </label>
                            <label class="priority-item high">
                                <input type="radio" name="priority" value="High">
                                <div class="priority-label">
                                    <i class="fa fa-bolt"></i>
                                    <span class="title">Tinggi</span>
                                    <span class="desc">Masalah mendesak/kritis</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Kirim Aduan</button>
                        <a href="{{ route('tickets.index') }}" class="btn btn-default">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12">
        <div class="card shadow-none border" id="similarTicketsCard" style="display: none; border-radius: 12px;">
            <div class="header border-bottom bg-light py-3">
                <h2 class="font-weight-bold text-warning" style="font-size: 0.9rem;"><i class="fa fa-exclamation-triangle mr-1"></i> Aduan Serupa Ditemukan</h2>
            </div>
            <div class="body">
                <p class="text-muted small">Masalah Anda mungkin sudah pernah dilaporkan. Silakan periksa daftar berikut untuk menghindari tiket ganda:</p>
                <ul id="similarTicketsList" class="list-group list-group-flush">
                    <!-- Similar tickets will be appended here -->
                </ul>
            </div>
        </div>

        <div class="card shadow-none border" style="border-radius: 12px; overflow: hidden;">
            <div class="header border-bottom bg-light py-3">
                <h2 class="font-weight-bold text-primary" style="font-size: 0.9rem;"><i class="fa fa-lightbulb-o mr-1"></i> Tips Mengirim Aduan</h2>
            </div>
            <div class="body p-3">
                <div class="d-flex mb-3">
                    <div class="bg-primary-light text-primary rounded-circle text-center mr-3" style="width: 24px; height: 24px; line-height: 24px; flex-shrink: 0;"><i class="fa fa-check small"></i></div>
                    <p class="mb-0 small text-dark font-weight-bold">Gunakan judul yang singkat dan jelas.</p>
                </div>
                <div class="d-flex mb-3">
                    <div class="bg-primary-light text-primary rounded-circle text-center mr-3" style="width: 24px; height: 24px; line-height: 24px; flex-shrink: 0;"><i class="fa fa-check small"></i></div>
                    <p class="mb-0 small text-dark font-weight-bold">Sertakan langkah-langkah untuk mereproduksi masalah.</p>
                </div>
                <div class="d-flex mb-0">
                    <div class="bg-primary-light text-primary rounded-circle text-center mr-3" style="width: 24px; height: 24px; line-height: 24px; flex-shrink: 0;"><i class="fa fa-check small"></i></div>
                    <p class="mb-0 small text-dark font-weight-bold">Lampirkan pesan error jika ada (tulis di deskripsi).</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Similar ticket search initialized');
        const subjectInput = document.getElementById('subject');
        const descriptionInput = document.getElementById('description');
        const similarCard = document.getElementById('similarTicketsCard');
        const similarList = document.getElementById('similarTicketsList');

        let timeout = null;

        function checkSimilar() {
            const subject = subjectInput.value;
            const description = descriptionInput.value;

            console.log('Checking similar tickets for:', subject, description);

            if (subject.length < 3 && description.length < 3) {
                similarCard.style.display = 'none';
                return;
            }

            const url = `{{ route('tickets.search-similar') }}?subject=${encodeURIComponent(subject)}&description=${encodeURIComponent(description)}`;
            console.log('Fetching:', url);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log('Search results:', data);
                    if (data.length > 0) {
                        similarList.innerHTML = '';
                        data.forEach(ticket => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent border-bottom';
                            li.innerHTML = `
                                <span class="small font-weight-bold">${ticket.subject}</span>
                                <span class="badge badge-sm badge-outline-info" style="font-size: 10px;">${ticket.status}</span>
                            `;
                            similarList.appendChild(li);
                        });
                        similarCard.style.display = 'block';
                    } else {
                        similarCard.style.display = 'none';
                    }
                })
                .catch(err => console.error('Search error:', err));
        }

        if (subjectInput) {
            subjectInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(checkSimilar, 800);
            });
        }

        if (descriptionInput) {
            descriptionInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(checkSimilar, 800);
            });
        }
    });
</script>
@endpush
