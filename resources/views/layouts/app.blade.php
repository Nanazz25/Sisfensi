<!doctype html>
<html lang="en">

<x-head>
    <x-slot:title>
        @yield('title')
    </x-slot:title>
    <x-slot:afterAppStyles>
        @vite('resources/css/dashboard.css')
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

    <x-script>
        <x-slot:afterAppScripts>
            @vite('resources/js/global-main.js')
            @yield('afterAppScripts')
        </x-slot:afterAppScripts>
    </x-script>
    @stack('scripts')
</body>

</html>