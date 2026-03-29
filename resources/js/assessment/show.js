document.addEventListener('DOMContentLoaded', function() {
    // --- Logika Pengurutan (Sorting) Riwayat ---
    const sortOptions = document.querySelectorAll('.sort-option');
    if (sortOptions.length > 0) {
        sortOptions.forEach(item => {
            item.addEventListener('click', function () {
                const form = document.getElementById('filterForm');
                const sortInput = document.getElementById('sortInput');
                if (form && sortInput) {
                    // Masukkan nilai sort dan kirim form secara otomatis
                    sortInput.value = this.dataset.value;
                    form.submit();
                }
            });
        });
    }

    // --- Inisialisasi Grafik Radar (Radar Chart) ---
    // Digunakan untuk memvisualisasikan persebaran nilai karakter siswa
    const radarCanvas = document.getElementById('radarChart');
    if (radarCanvas && window.radarChartData) {
        const ctx = radarCanvas.getContext('2d');
        
        new Chart(ctx, {
            type: 'radar',
            data: {
                // Memecah label setiap spasi agar membungkus jadi multi-baris
                // sehingga tidak menggeser titik tengah (center) dari grafik radar
                labels: window.radarChartData.labels.map(label => label.split(' ')),
                datasets: [{
                    label: 'Skor Penilaian Terakhir',
                    data: window.radarChartData.values,
                    fill: true,
                    backgroundColor: 'rgba(0, 188, 212, 0.4)', // Warna transparan area dalam radar
                    borderColor: 'rgb(0, 188, 212)',
                    pointBackgroundColor: 'rgb(0, 188, 212)',
                    pointBorderColor: '#fff',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        min: 0,
                        max: 10, // Skala nilai maksimal sesuai indikator (10)
                        suggestedMax: 10,
                        ticks: {
                            stepSize: 2,
                            display: true
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        angleLines: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        pointLabels: {
                            // Gaya teks untuk nama-nama kategori di sekeliling radar
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Sembunyikan legenda karena hanya ada satu dataset
                    }
                }
            }
        });
    }
});
