document.addEventListener('DOMContentLoaded', function() {
    // --- Logika Pengurutan (Sorting) Tabel ---
    const sortOptions = document.querySelectorAll('.sort-option');
    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('filterForm');

    if (sortOptions.length > 0) {
        sortOptions.forEach(item => {
            item.addEventListener('click', function () {
                const sortInput = document.getElementById('sortInput');
                if (filterForm && sortInput) {
                    sortInput.value = this.dataset.value;
                    filterForm.submit();
                }
            });
        });
    }

    // --- Logika Auto-Search (Pencarian Otomatis) ---
    if (searchInput && filterForm) {
        let timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            // Submit form setelah 500ms berhenti mengetik (debounce)
            timeout = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });

        // Pastikan kursor tetap di akhir input setelah reload jika sedang mengetik
        const val = searchInput.value;
        if (val) {
            searchInput.focus();
            searchInput.setSelectionRange(val.length, val.length);
        }
    }

    // --- Inisialisasi Grafik Batang Vertikal untuk Rata-rata Skor ---
    const avgCanvas = document.getElementById('averageChart');
    if (avgCanvas && window.averageChartData) {
        const ctx = avgCanvas.getContext('2d');
        
        // Menentukan warna batang secara dinamis berdasarkan nilai skor (0-10)
        const backgroundColors = window.averageChartData.values.map(score => {
            if (score >= 8) return 'rgba(40, 167, 69, 0.7)';  // Hijau (Luar Biasa)
            if (score >= 6) return 'rgba(0, 188, 212, 0.7)'; // Biru Muda (Bagus)
            if (score >= 4) return 'rgba(255, 193, 7, 0.7)';  // Kuning (Cukup)
            return 'rgba(220, 53, 69, 0.7)';               // Merah (Kurang)
        });

        const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));

        // Membuat objek Chart.js
        new Chart(ctx, {
            type: 'bar', // Tipe grafik batang
            data: {
                labels: window.averageChartData.labels,
                datasets: [{
                    label: 'Rata-rata Skor',
                    data: window.averageChartData.values,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 2,
                    borderRadius: 4,
                    barThickness: 30 // Ketebalan batang agar terlihat solid
                }]
            },
            options: {
                indexAxis: 'x', // Orientasi vertikal (batang dari bawah ke atas)
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }, // Sembunyikan label dataset utama
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        callbacks: {
                            // Kustomisasi teks saat kursor diarahkan ke batang
                            label: function(context) {
                                return ` Skor Rata-rata: ${context.parsed.y} / 10`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10,
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 2,
                            font: { weight: 'bold' }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11, weight: '600' }
                        }
                    }
                }
            }
        });
    }
});
