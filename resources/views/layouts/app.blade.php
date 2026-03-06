<!doctype html>
<html lang="en">

<x-head>
    <x-slot:title>
        @yield('title')
    </x-slot:title>
    <x-slot:afterAppStyles>
        @yield('afterAppStyles')
    </x-slot:afterAppStyles>
</x-head>

<body data-theme="light" class="font-nunito">
    <div id="wrapper" class="theme-cyan">

        <!-- Page Loader -->
        <x-loader />

        <!-- Top navbar div start -->
        <x-navbar />

        <!-- main left menu -->
        <x-sidebar />

        <!-- mani page content body part -->
        <div id="main-content">
            <div class="container-fluid">
                <!-- Header -->
                <div class="block-header">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <h2>@yield('title')</h2>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}"><i
                                            class="fa fa-dashboard"></i></a></li>
                                @if(request()->segment(1) && request()->segment(1) !== 'dashboard')
                                    <li class="breadcrumb-item">{{ ucwords(str_replace('-', ' ', request()->segment(1))) }}
                                    </li>
                                @endif
                                <li class="breadcrumb-item active">@yield('title')</li>
                            </ul>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="d-flex flex-row-reverse">
                                <div class="page_action">

                                </div>
                                <div class="p-2 d-flex">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-toast-notification />

                <!-- Progress Bar for AJAX -->
                <div id="ajax-progress-bar"
                    style="position: fixed; top: 0; left: 0; width: 0%; height: 3px; background: #3498db; z-index: 9999; transition: width 0.3s ease;">
                </div>

                <!-- Content -->
                <div id="ajax-content-area">
                    @yield('content')
                </div>

            </div>
        </div>

    </div>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Logout</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center">
                    <p>Apakah kamu yakin ingin logout dari sistem?</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>

                    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="icon-power"></i> Logout
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Javascript -->
    <x-script>
        <x-slot:afterAppScripts>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // --- Global AJAX Handler ---
                    const ajaxContentArea = document.querySelector('#ajax-content-area');

                    // Simple Debounce function
                    function debounce(func, wait) {
                        let timeout;
                        return function () {
                            const context = this, args = arguments;
                            clearTimeout(timeout);
                            timeout = setTimeout(() => func.apply(context, args), wait);
                        };
                    }

                    // Helper to show/hide loading state on specific search input
                    function toggleInputLoading(input, isLoading) {
                        const group = input.closest('.input-group');
                        if (!group) return;
                        const icon = group.querySelector('.fa-search, .fa-spinner');
                        if (!icon) return;

                        if (isLoading) {
                            icon.classList.remove('fa-search');
                            icon.classList.add('fa-spinner', 'fa-spin', 'text-info');
                        } else {
                            icon.classList.remove('fa-spinner', 'fa-spin', 'text-info');
                            icon.classList.add('fa-search');
                        }
                    }

                    // Function to load content via AJAX
                    async function loadAjaxContent(url, options = {}) {
                        if (!ajaxContentArea) {
                            window.location.href = url;
                            return;
                        }

                        // Store focus state
                        const focusedName = document.activeElement ? document.activeElement.name : null;
                        const selectionStart = document.activeElement ? document.activeElement.selectionStart : null;

                        ajaxContentArea.classList.add('loading-overlay');
                        const topProgress = document.getElementById('ajax-progress-bar');
                        if (topProgress) topProgress.style.width = '30%';

                        try {
                            const response = await fetch(url, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });

                            if (topProgress) topProgress.style.width = '80%';

                            if (!response.ok) throw new Error('Response not OK');

                            const html = await response.text();
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newArea = doc.querySelector('#ajax-content-area');

                            if (newArea) {
                                ajaxContentArea.innerHTML = newArea.innerHTML;
                                window.history.pushState({}, '', url);

                                // Re-initialize listeners
                                initGlobalHandlers();

                                // Restore focus
                                if (focusedName) {
                                    const el = ajaxContentArea.querySelector(`[name="${focusedName}"]`);
                                    if (el) {
                                        el.focus();
                                        if (selectionStart !== null && el.setSelectionRange) {
                                            el.setSelectionRange(selectionStart, selectionStart);
                                        }
                                    }
                                }

                                if (options.callback) options.callback();
                            } else {
                                window.location.href = url;
                            }
                        } catch (error) {
                            console.error('AJAX Error:', error);
                            window.location.href = url; // Fallback
                        } finally {
                            ajaxContentArea.classList.remove('loading-overlay');
                            if (topProgress) {
                                topProgress.style.width = '100%';
                                setTimeout(() => topProgress.style.width = '0%', 300);
                            }
                            // Reset all icons
                            document.querySelectorAll('.fa-spinner.fa-spin').forEach(icon => {
                                icon.classList.remove('fa-spinner', 'fa-spin', 'text-info');
                                icon.classList.add('fa-search');
                            });
                        }
                    }

                    // Helper to get form URL with params using URL API
                    function getFormUrl(form) {
                        const action = form.getAttribute('action') || window.location.pathname;
                        const url = new URL(action, window.location.origin);
                        const formData = new FormData(form);
                        for (const [key, value] of formData) {
                            if (value) url.searchParams.set(key, value);
                            else url.searchParams.delete(key);
                        }
                        return url.toString();
                    }

                    // Initialize/Re-initialize global listeners
                    function initGlobalHandlers() {
                        // 1. Intercept Pagination
                        document.querySelectorAll('.pagination a, .page-link').forEach(link => {
                            if (!link.classList.contains('ajax-bound')) {
                                link.classList.add('ajax-bound');
                                link.addEventListener('click', function (e) {
                                    if (this.href && !this.href.includes('#')) {
                                        e.preventDefault();
                                        loadAjaxContent(this.href);
                                    }
                                });
                            }
                        });

                        // 2. Intercept Search/Filter Forms
                        document.querySelectorAll('form.ajax-form').forEach(form => {
                            if (!form.classList.contains('ajax-bound')) {
                                form.classList.add('ajax-bound');

                                // Instant filters for select/date
                                form.querySelectorAll('select, input[type="date"]').forEach(input => {
                                    input.addEventListener('change', () => {
                                        loadAjaxContent(getFormUrl(form));
                                    });
                                });

                                // Debounced search for text inputs
                                form.querySelectorAll('input[type="text"], input[type="search"]').forEach(input => {
                                    input.addEventListener('input', function () {
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

                                form.addEventListener('submit', function (e) {
                                    e.preventDefault();
                                    loadAjaxContent(getFormUrl(this));
                                });
                            }
                        });

                        // 3. Global Delete Modal Handler (already here, but needs re-binding after AJAX)
                        document.querySelectorAll('.btn-delete').forEach(btn => {
                            btn.addEventListener('click', function () {
                                const name = this.dataset.name || '';
                                const action = this.dataset.action;
                                const nameElem = document.getElementById('deleteItemName');
                                const formElem = document.getElementById('deleteForm');
                                if (nameElem) nameElem.innerText = name;
                                if (formElem) formElem.action = action;
                            });
                        });

                        // 4. Global Confirm Modal Handler
                        document.querySelectorAll('.btn-confirm').forEach(btn => {
                            btn.addEventListener('click', function () {
                                const title = this.dataset.title || 'Konfirmasi';
                                const message = this.dataset.message || 'Apakah Anda yakin?';
                                const name = this.dataset.name || '';
                                const action = this.dataset.action;
                                const btnClass = this.dataset.btnClass || 'btn-primary';
                                const btnText = this.dataset.btnText || 'Yakin';
                                const iconClass = this.dataset.confirmIcon || 'fa-check';

                                document.getElementById('confirmModalTitle').innerText = title;
                                document.getElementById('confirmModalMessage').innerText = message;
                                document.getElementById('confirmModalItemName').innerText = name;
                                document.getElementById('confirmForm').action = action;

                                const submitBtn = document.getElementById('confirmModalSubmitBtn');
                                if (submitBtn) {
                                    submitBtn.className = 'btn ' + btnClass;
                                    document.getElementById('confirmModalSubmitText').innerText = btnText;
                                    document.getElementById('confirmModalIcon').className = 'fa mr-1 ' + iconClass;
                                }
                            });
                        });
                    }

                    // Initial run
                    initGlobalHandlers();

                    // --- Sidebar & UI ---
                    // Masalah: Theme script konflik dengan logic close manual.
                    // Solusi: Gunakan class custom pada tombol close agar tidak disentuh theme script.

                    const handleSidebarClose = (e) => {
                        if (e) {
                            e.preventDefault();
                            e.stopPropagation();
                        }

                        // 1. Hapus class active dari body
                        document.body.classList.remove('offcanvas-active');

                        // 2. Hide overlay
                        const overlays = document.querySelectorAll('.overlay');
                        overlays.forEach(overlay => {
                            overlay.style.display = 'none';
                        });
                    };

                    // Gunakan class custom yang kita buat di sidebar component
                    const sidebarCloseBtn = document.querySelector('.btn-custom-close-sidebar');
                    if (sidebarCloseBtn) {
                        sidebarCloseBtn.addEventListener('click', handleSidebarClose);
                    }

                    // Handle klik pada overlay
                    document.addEventListener('click', function (e) {
                        if (e.target.classList.contains('overlay')) {
                            handleSidebarClose(e);
                        }
                    });

                    // Monitor tombol burger navbar
                    const navbarToggleBtn = document.querySelector('.navbar .btn-toggle-offcanvas');
                    if (navbarToggleBtn) {
                        navbarToggleBtn.addEventListener('click', function () {
                            // Reset overlay visibility saat buka
                            setTimeout(() => {
                                if (document.body.classList.contains('offcanvas-active')) {
                                    const overlays = document.querySelectorAll('.overlay');
                                    overlays.forEach(overlay => {
                                        overlay.style.removeProperty('display');
                                        overlay.style.display = 'block';
                                    });
                                }
                            }, 100);
                        });
                    }

                    // EXTRA SAFTY: Ensure sidebar is closed on mobile load
                    if (window.innerWidth < 992) {
                        document.body.classList.remove('offcanvas-active');
                    }


                });
            </script>
            @yield('afterAppScripts')
            @stack('scripts')
        </x-slot:afterAppScripts>
    </x-script>
</body>

</html>