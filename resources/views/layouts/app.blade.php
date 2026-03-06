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

                <!-- Content -->
                @yield('content')

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
                    // Global Delete Modal Handler
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

                    // Global Confirm Modal Handler (POST)
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
                            submitBtn.className = 'btn ' + btnClass;
                            document.getElementById('confirmModalSubmitText').innerText = btnText;
                            document.getElementById('confirmModalIcon').className = 'fa mr-1 ' + iconClass;
                        });
                    });

                    // SIDEBAR BUG FIX: Custom Close Handler
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