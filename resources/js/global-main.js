document.addEventListener("DOMContentLoaded", function () {
    // --- Handler AJAX Global ---
    // Area utama di mana konten AJAX akan dimuat
    const ajaxContentArea = document.querySelector("#ajax-content-area");

    // Fungsi Debounce sederhana untuk menunda eksekusi fungsi
    // Berguna untuk mencegah pemanggilan API berulang kali saat mengetik
    function debounce(func, wait) {
        let timeout;
        return function () {
            const context = this,
                args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // Helper untuk menampilkan/menyembunyikan status loading pada input pencarian tertentu
    // Mengganti ikon kaca pembesar menjadi spinner saat sedang memproses
    function toggleInputLoading(input, isLoading) {
        const group = input.closest(".input-group");
        if (!group) return;
        const icon = group.querySelector(".fa-search, .fa-spinner");
        if (!icon) return;

        if (isLoading) {
            icon.classList.remove("fa-search");
            icon.classList.add("fa-spinner", "fa-spin", "text-info");
        } else {
            icon.classList.remove("fa-spinner", "fa-spin", "text-info");
            icon.classList.add("fa-search");
        }
    }

    // Fungsi utama untuk memuat konten halaman secara asinkron (AJAX)
    async function loadAjaxContent(url, options = {}) {
        // Jika container AJAX tidak ditemukan, lakukan reload halaman biasa
        if (!ajaxContentArea) {
            window.location.href = url;
            return;
        }

        // Simpan status fokus elemen saat ini agar bisa dikembalikan setelah DOM diupdate
        const focusedName = document.activeElement
            ? document.activeElement.name
            : null;
        const selectionStart = document.activeElement
            ? document.activeElement.selectionStart
            : null;

        // Tampilkan overlay loading dan mulai progress bar
        ajaxContentArea.classList.add("loading-overlay");
        const topProgress = document.getElementById("ajax-progress-bar");
        if (topProgress) topProgress.style.width = "30%";

        try {
            // Lakukan pemanggilan ke server dengan header khusus XMLHttpRequest
            const response = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });

            if (topProgress) topProgress.style.width = "80%";

            if (!response.ok) throw new Error("Respons server tidak OK");

            // Ambil teks HTML dan parsing untuk mendapatkan area konten yang baru
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newArea = doc.querySelector("#ajax-content-area");

            if (newArea) {
                // Update DOM dengan konten baru dan simpan URL ke history browser
                ajaxContentArea.innerHTML = newArea.innerHTML;
                window.history.pushState({}, "", url);

                // Inisialisasi ulang semua pendengar event karena elemen DOM baru saja diganti
                initGlobalHandlers();

                // Kembalikan fokus ke elemen input sebelumnya jika ada
                if (focusedName) {
                    const el = ajaxContentArea.querySelector(
                        `[name="${focusedName}"]`,
                    );
                    if (el) {
                        el.focus();
                        if (selectionStart !== null && el.setSelectionRange) {
                            el.setSelectionRange(
                                selectionStart,
                                selectionStart,
                            );
                        }
                    }
                }

                // Jalankan callback jika disediakan
                if (options.callback) options.callback();
            } else {
                // Jika struktur tidak cocok, lakukan reload halaman penuh
                window.location.href = url;
            }
        } catch (error) {
            console.error("Kesalahan AJAX:", error);
            window.location.href = url; // Fallback jika terjadi error fatal
        } finally {
            // Sembunyikan loading overlay dan selesaikan progress bar
            ajaxContentArea.classList.remove("loading-overlay");
            if (topProgress) {
                topProgress.style.width = "100%";
                setTimeout(() => (topProgress.style.width = "0%"), 300);
            }
            // Atur ulang semua ikon loading menjadi ikon cari kembali
            document.querySelectorAll(".fa-spinner.fa-spin").forEach((icon) => {
                icon.classList.remove("fa-spinner", "fa-spin", "text-info");
                icon.classList.add("fa-search");
            });
        }
    }

    // Helper untuk membuat URL lengkap dengan parameter dari data form
    function getFormUrl(form) {
        const action = form.getAttribute("action") || window.location.pathname;
        const url = new URL(action, window.location.origin);
        const formData = new FormData(form);
        for (const [key, value] of formData) {
            // Tambahkan parameter ke URL jika memiliki nilai
            if (value) url.searchParams.set(key, value);
            else url.searchParams.delete(key);
        }
        return url.toString();
    }

    // Fungsi untuk mendaftarkan semua event listener global
    function initGlobalHandlers() {
        // 1. Tangani Klik Link Navigasi/Pagination agar menggunakan AJAX
        document
            .querySelectorAll(".pagination a, .page-link")
            .forEach((link) => {
                if (!link.classList.contains("ajax-bound")) {
                    link.classList.add("ajax-bound");
                    link.addEventListener("click", function (e) {
                        // Jalankan AJAX hanya untuk link valid yang bukan anchor internal
                        if (this.href && !this.href.includes("#")) {
                            e.preventDefault();
                            loadAjaxContent(this.href);
                        }
                    });
                }
            });

        // 2. Tangani Form yang ditandai dengan class 'ajax-form'
        document.querySelectorAll("form.ajax-form").forEach((form) => {
            if (!form.classList.contains("ajax-bound")) {
                form.classList.add("ajax-bound");

                // Filter Instan: Trigger AJAX saat nilai Select atau Input Date berubah
                form.querySelectorAll('select, input[type="date"]').forEach(
                    (input) => {
                        input.addEventListener("change", () => {
                            loadAjaxContent(getFormUrl(form));
                        });
                    },
                );

                // Pencarian Tertunda: Trigger AJAX dengan jeda 500ms saat mengetik
                form.querySelectorAll(
                    'input[type="text"], input[type="search"]',
                ).forEach((input) => {
                    input.addEventListener("input", function () {
                        const self = this;
                        toggleInputLoading(self, true);

                        if (!this.debounceTimer) {
                            this.debounceTimer = null;
                        }

                        clearTimeout(this.debounceTimer);
                        this.debounceTimer = setTimeout(() => {
                            loadAjaxContent(getFormUrl(form));
                        }, 500);
                    });
                });

                // Tangani pengiriman form secara manual (saat tekan Enter)
                form.addEventListener("submit", function (e) {
                    e.preventDefault();
                    loadAjaxContent(getFormUrl(this));
                });
            }
        });

        // 3. Handler Global untuk Modal Hapus (Mengisi data otomatis ke modal)
        document.querySelectorAll(".btn-delete").forEach((btn) => {
            btn.addEventListener("click", function () {
                const name = this.dataset.name || "";
                const action = this.dataset.action;
                const nameElem = document.getElementById("deleteItemName");
                const formElem = document.getElementById("deleteForm");
                if (nameElem) nameElem.innerText = name;
                if (formElem) formElem.action = action;
            });
        });

        // 4. Handler Global untuk Modal Konfirmasi Umum
        document.querySelectorAll(".btn-confirm").forEach((btn) => {
            btn.addEventListener("click", function () {
                const title = this.dataset.title || "Konfirmasi";
                const message = this.dataset.message || "Apakah Anda yakin?";
                const name = this.dataset.name || "";
                const action = this.dataset.action;
                const formId = this.dataset.formId; // Tambahan untuk mensubmit form spesifik
                const btnClass = this.dataset.btnClass || "btn-primary";
                const btnText = this.dataset.btnText || "Yakin";
                const iconClass = this.dataset.confirmIcon || "fa-check";

                document.getElementById("confirmModalTitle").innerText = title;
                document.getElementById("confirmModalMessage").innerText = message;
                document.getElementById("confirmModalItemName").innerText = name;
                
                const confirmForm = document.getElementById("confirmForm");
                const submitBtn = document.getElementById("confirmModalSubmitBtn");

                if (formId) {
                    // Jika ada formId, kita hapus action dan simpan ID form target
                    confirmForm.action = "javascript:void(0);";
                    submitBtn.setAttribute("data-submit-form", formId);
                } else {
                    // Jika tidak ada, gunakan action POST biasa
                    confirmForm.action = action;
                    submitBtn.removeAttribute("data-submit-form");
                }

                if (submitBtn) {
                    submitBtn.className = "btn " + btnClass;
                    const textSpan = document.getElementById("confirmModalSubmitText");
                    if (textSpan) textSpan.innerText = btnText;
                    
                    const iconI = document.getElementById("confirmModalIcon");
                    if (iconI) iconI.className = "fa mr-1 " + iconClass;
                }

                // Handler klik untuk submit form eksternal
                if (!submitBtn.dataset.listenerBound) {
                    submitBtn.dataset.listenerBound = "true";
                    submitBtn.addEventListener("click", function(e) {
                        const targetId = this.getAttribute("data-submit-form");
                        if (targetId) {
                            e.preventDefault();
                            const targetForm = document.getElementById(targetId);
                            if (targetForm) targetForm.submit();
                        }
                    });
                }
            });
        });
    }

    // Jalankan inisialisasi pertama kali saat aplikasi dimuat
    initGlobalHandlers();

    // --- Sidebar & Antarmuka Pengguna (UI) ---
    // Fungsi untuk menutup sidebar pada tampilan mobile
    const handleSidebarClose = (e) => {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // 1. Hapus class active dari body untuk menyembunyikan sidebar
        document.body.classList.remove("offcanvas-active");

        // 2. Sembunyikan elemen overlay gelap
        const overlays = document.querySelectorAll(".overlay");
        overlays.forEach((overlay) => {
            overlay.style.display = "none";
        });
    };

    // Cari dan daftarkan event untuk tombol tutup kustom pada sidebar
    const sidebarCloseBtn = document.querySelector(".btn-custom-close-sidebar");
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener("click", handleSidebarClose);
    }

    // Daftarkan event klik pada overlay agar sidebar menutup saat area luar diklik
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("overlay")) {
            handleSidebarClose(e);
        }
    });

    // Monitor tombol burger/toggle di navbar untuk membuka sidebar
    const navbarToggleBtn = document.querySelector(
        ".navbar .btn-toggle-offcanvas",
    );
    if (navbarToggleBtn) {
        navbarToggleBtn.addEventListener("click", function () {
            // Tampilkan kembali overlay saat sidebar dibuka
            setTimeout(() => {
                if (document.body.classList.contains("offcanvas-active")) {
                    const overlays = document.querySelectorAll(".overlay");
                    overlays.forEach((overlay) => {
                        overlay.style.removeProperty("display");
                        overlay.style.display = "block";
                    });
                }
            }, 100);
        });
    }

    // KEAMANAN TAMBAHAN: Pastikan sidebar otomatis tertutup jika halaman dimuat pada resolusi mobile
    if (window.innerWidth < 992) {
        document.body.classList.remove("offcanvas-active");
    }
});
