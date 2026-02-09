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
                                <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-dashboard"></i></a>
                                </li>
                                <li class="breadcrumb-item">@yield('title')</li>
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
                });
            </script>
            @yield('afterAppScripts')
            @stack('scripts')
        </x-slot:afterAppScripts>
    </x-script>
</body>

</html>