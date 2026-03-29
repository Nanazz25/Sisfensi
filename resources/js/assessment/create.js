// Navigasi Langkah (Step) pada form penilaian massal
window.goToStep = function(step) {
    if (step === 2) {
        // Validasi: Pastikan ada minimal satu orang yang dipilih sebelum lanjut
        const selected = document.querySelectorAll('.ev-checkbox:checked');
        if (selected.length === 0) {
            alert('Pilih orang yang ingin dinilai!');
            return;
        }
        // Sembunyikan langkah 1, tampilkan langkah 2
        document.getElementById('step1').classList.add('d-none');
        document.getElementById('step2').classList.remove('d-none');
        window.scrollTo({top: 0, behavior: 'smooth'});
    } else {
        // Kembali ke pemilihan orang (Langkah 1)
        document.getElementById('step2').classList.add('d-none');
        document.getElementById('step1').classList.remove('d-none');
    }
}

// Menampilkan modal konfirmasi sebelum menyimpan penilaian
window.showConfirmModal = function() {
    const count = document.querySelectorAll('.ev-checkbox:checked').length;
    const scores = document.querySelectorAll('.score-input');
    let filledCount = 0;
    
    // Hitung berapa banyak indikator yang sudah diisi nilai
    scores.forEach(input => {
        if (parseInt(input.value) > 0) filledCount++;
    });

    if (filledCount === 0) {
        alert('Berikan minimal satu nilai pada indikator!');
        return;
    }

    // Tampilkan ringkasan pada modal
    document.getElementById('cfmCount').innerText = count;
    document.getElementById('cfmCatCount').innerText = filledCount;
    
    $('#confirmModal').modal('show');
}

// Trigger submit form utama
window.submitAssessment = function() {
    document.getElementById('assessmentForm').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    // --- Logika Seleksi & Penghitung ---
    const selectAll = document.getElementById('selectAllEvaluatees');
    const counter = document.getElementById('selectedCounter');
    
    // Fungsi untuk memperbarui angka penghitung dan lencana (badge) nama yang dipilih
    function updateCounter() {
        const selected = document.querySelectorAll('.ev-checkbox:checked');
        counter.innerText = selected.length;
        
        const badgeContainer = document.getElementById('selectedBadges');
        badgeContainer.innerHTML = '';
        
        selected.forEach(cb => {
            const name = cb.closest('tr').querySelector('.subject-name').innerText;
            const badge = document.createElement('span');
            badge.className = 'badge badge-primary mr-2 mb-2 p-2 shadow-xs';
            badge.style.fontSize = '12px';
            badge.innerHTML = `<i class="fa fa-user mr-1"></i> ${name}`;
            badgeContainer.appendChild(badge);
        });
    }

    // Fitur Pilih Semua (Select All)
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.ev-checkbox').forEach(cb => {
                cb.checked = this.checked;
                cb.closest('tr').classList.toggle('selected', this.checked);
            });
            updateCounter();
        });
    }

    // Event listener untuk setiap checkbox individu
    document.querySelectorAll('.ev-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            this.closest('tr').classList.toggle('selected', this.checked);
            updateCounter();
            if (!this.checked && selectAll) selectAll.checked = false;
        });
    });

    // --- Pencarian Orang yang Dinilai ---
    const subjectSearch = document.getElementById('subjectSearch');
    if (subjectSearch) {
        subjectSearch.addEventListener('keyup', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.subject-row').forEach(row => {
                const name = row.querySelector('.subject-name').innerText.toLowerCase();
                row.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }

    // --- Pencarian Kategori Penilaian ---
    const catSearch = document.getElementById('catSearch');
    if (catSearch) {
        catSearch.addEventListener('keyup', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.category-item').forEach(item => {
                const name = item.dataset.name;
                item.style.display = name.includes(q) ? 'block' : 'none';
            });
        });
    }

    // --- LOGIKA RATING BINTANG KUSTOM ---
    // Mendukung pengisian skor dengan klik bintang (bisa setengah bintang)
    document.querySelectorAll('.star-rating').forEach(ratingGroup => {
        const card = ratingGroup.closest('.category-item');
        const input = card.querySelector('.score-input');
        const display = card.querySelector('.score-display');
        const stars = ratingGroup.querySelectorAll('.star-item');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const index = parseInt(this.dataset.star);
                const currentScore = parseInt(input.value);
                
                const targetFirst = (index * 2) - 1; // 0.5 star
                const targetFull = (index * 2);      // full star
                
                let newScore = 0;
                if (currentScore < targetFirst) {
                    newScore = targetFirst;
                } else if (currentScore === targetFirst) {
                    newScore = targetFull;
                } else {
                    newScore = (currentScore === targetFull && index * 2 === currentScore) ? targetFirst : targetFirst;
                }
                
                input.value = newScore;
                display.innerText = newScore;
                
                if (newScore > 0) card.classList.add('active-input');
                else card.classList.remove('active-input');

                stars.forEach(s => {
                    const idx = parseInt(s.dataset.star);
                    s.classList.remove('full', 'half');
                    if (newScore >= idx * 2) {
                        s.classList.add('full');
                    } else if (newScore === (idx * 2) - 1) {
                        s.classList.add('half');
                    }
                });
            });
        });
    });
});
