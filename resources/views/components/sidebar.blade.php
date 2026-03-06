<div id="left-sidebar" class="sidebar">
    <button type="button" class="btn-custom-close-sidebar d-lg-none"
        style="position: absolute; top: 15px; right: 15px; z-index: 1001; background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); padding: 5px 15px; border-radius: 20px; color: #dc3545; font-weight: 600; font-size: 0.9rem; transition: all 0.3s;"
        onclick="document.body.classList.remove('offcanvas-active'); document.querySelector('.sidebar').classList.remove('open'); document.querySelectorAll('.overlay').forEach(el => el.style.display='none');">
        <i class="fa fa-times mr-1"></i> Close
    </button>
    <div class="sidebar-scroll">
        <div class="user-account p-3 pb-0">
            <div class="d-flex align-items-center">
                <img src="{{ asset('assets/images/user.png') }}" class="rounded-circle user-photo" width="45"
                    alt="User Profile Picture">
                <div class="dropdown ml-2">
                    <span class="d-block font-12">Welcome,</span>
                    <a href="javascript:void(0);" class="dropdown-toggle user-name" data-toggle="dropdown">
                        <strong>
                            {{ implode(' ', array_slice(explode(' ', Auth::user()->name), 0, 2)) }}
                        </strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right account">
                        <li><a href="{{ route('profile.show') }}"><i class="icon-user"></i>My Profile</a></li>
                        <li><a href="javascript:void(0);"><i class="icon-settings"></i>Settings</a></li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="icon-power"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="mb-0 mt-3">
        </div>
        <!-- Nav tabs -->
        <ul class="nav nav-tabs d-flex justify-content-between" style="padding: 0 15px;">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#menu">
                    <i class="icon-list"></i>
                    Menu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#setting">
                    <i class="icon-settings"></i>
                    Setting
                </a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content padding-0">
            <div class="tab-pane active" id="menu">
                <nav id="left-sidebar-nav" class="sidebar-nav">
                    <ul id="main-menu" class="metismenu">

                        <li class="{{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                            <a href="{{ route('dashboard.index') }}">
                                <i class="fa fa-dashboard"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        @if(auth()->user()->role === 'admin')
                            <li class="{{ request()->routeIs('attendance.manual') ? 'active' : '' }}">
                                <a href="{{ route('attendance.manual') }}">
                                    <i class="fa fa-check-square-o"></i> <span>Kelola Presensi</span>
                                </a>
                            </li>
                            {{-- Manajemen User --}}
                            <li class="{{ request()->is('users*') ? 'active' : '' }}">
                                <a href="#Users" class="has-arrow">
                                    <i class="fa fa-users"></i>
                                    <span>Manajemen User</span>
                                </a>
                                <ul class="collapse {{ request()->routeIs('users.*') ? 'in' : '' }}">
                                    <li class="{{ request()->routeIs('users.admin') ? 'active' : '' }}">
                                        <a href="{{ route('users.admin') }}">
                                            <i class="fa fa-user-secret"></i> <span>Admin</span>
                                        </a>
                                    </li>
                                    <li class="{{ request()->routeIs('users.guru') ? 'active' : '' }}">
                                        <a href="{{ route('users.guru') }}">
                                            <i class="fa fa-graduation-cap"></i> <span>Guru</span>
                                        </a>
                                    </li>
                                    <li class="{{ request()->routeIs('users.siswa') ? 'active' : '' }}">
                                        <a href="{{ route('users.siswa') }}">
                                            <i class="fa fa-user"></i> <span>Siswa</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            {{-- Data Master --}}
                            <li
                                class="{{ request()->routeIs('teachers.*') || request()->routeIs('peserta-didik.*') ? 'active' : '' }}">
                                <a href="#DataMaster" class="has-arrow">
                                    <i class="fa fa-database"></i>
                                    <span>Data Master</span>
                                </a>
                                <ul
                                    class="collapse {{ request()->routeIs('teachers.*') || request()->routeIs('peserta-didik.*') ? 'in' : '' }}">
                                    <li class="{{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                                        <a href="{{ route('teachers.index') }}">
                                            <i class="fa fa-black-tie"></i> <span>Guru</span>
                                        </a>
                                    </li>
                                    <li class="{{ request()->routeIs('peserta-didik.*') ? 'active' : '' }}">
                                        <a href="{{ route('peserta-didik.index') }}">
                                            <i class="fa fa-child"></i> <span>Peserta Didik</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if(in_array(auth()->user()->role, ['admin', 'guru']))
                            {{-- Akademik --}}
                            <li
                                class="{{ request()->routeIs('tahun-ajar.*') || request()->routeIs('jurusan.*') || request()->routeIs('rombongan-belajar.*') || request()->routeIs('subjects.*') || request()->routeIs('schedules.*') ? 'active' : '' }}">
                                <a href="#Akademik" class="has-arrow">
                                    <i class="fa fa-mortar-board"></i>
                                    <span>Akademik</span>
                                </a>
                                <ul
                                    class="collapse {{ request()->routeIs('tahun-ajar.*') || request()->routeIs('jurusan.*') || request()->routeIs('rombongan-belajar.*') || request()->routeIs('subjects.*') || request()->routeIs('schedules.*') ? 'in' : '' }}">
                                    @if(auth()->user()->role === 'admin')
                                        <li class="{{ request()->routeIs('tahun-ajar.*') ? 'active' : '' }}">
                                            <a href="{{ route('tahun-ajar.index') }}">
                                                <i class="fa fa-calendar"></i> <span>Tahun Ajar</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('jurusan.*') ? 'active' : '' }}">
                                            <a href="{{ route('jurusan.index') }}">
                                                <i class="fa fa-folder"></i> <span>Jurusan</span>
                                            </a>
                                        </li>
                                    @endif

                                    <li class="{{ request()->routeIs('rombongan-belajar.*') ? 'active' : '' }}">
                                        <a href="{{ route('rombongan-belajar.index') }}">
                                            <i class="fa fa-sitemap"></i> <span>Rombongan Belajar</span>
                                        </a>
                                    </li>

                                    @if(auth()->user()->role === 'admin')
                                        <li class="{{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                                            <a href="{{ route('subjects.index') }}">
                                                <i class="fa fa-book"></i> <span>Mata Pelajaran</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('schedules.*') ? 'active' : '' }}">
                                            <a href="{{ route('schedules.index') }}">
                                                <i class="fa fa-clock-o"></i> <span>Jadwal</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if(auth()->user()->role === 'admin')
                            {{-- Sekolah --}}
                            <li
                                class="{{ request()->routeIs('school-locations.*') || request()->routeIs('school-settings.*') ? 'active' : '' }}">
                                <a href="#Sekolah" class="has-arrow">
                                    <i class="fa fa-building"></i>
                                    <span>Sekolah</span>
                                </a>
                                <ul
                                    class="collapse {{ request()->routeIs('school-locations.*') || request()->routeIs('school-settings.*') ? 'in' : '' }}">
                                    <li class="{{ request()->routeIs('school-locations.*') ? 'active' : '' }}">
                                        <a href="{{ route('school-locations.index') }}">
                                            <i class="fa fa-map-marker"></i> <span>Lokasi Sekolah</span>
                                        </a>
                                    </li>
                                    <li class="{{ request()->routeIs('school-settings.*') ? 'active' : '' }}">
                                        <a href="{{ route('school-settings.index') }}">
                                            <i class="fa fa-cog"></i> <span>Sistem Sekolah</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if(in_array(auth()->user()->role, ['admin', 'guru']))
                            <li class="{{ request()->is('attendance-permissions*') ? 'active' : '' }}">
                                <a href="{{ route('attendance-permissions.index') }}">
                                    <i class="fa fa-file-text"></i>
                                    <span>Pengajuan Izin</span>
                                </a>
                            </li>

                            {{-- Presensi & Biometrik --}}
                            <li
                                class="{{ request()->is('face-recognition*') || request()->is('attendance*') ? 'active' : '' }}">
                                <a href="#Presensi" class="has-arrow">
                                    <i class="fa fa-camera"></i>
                                    <span>Presensi & Biometrik</span>
                                </a>
                                <ul
                                    class="collapse {{ request()->is('face-recognition*') || request()->is('attendance*') ? 'in' : '' }}">
                                    <li class="{{ request()->routeIs('face.enroll') ? 'active' : '' }}">
                                        <a href="{{ route('face.enroll') }}">
                                            <i class="fa fa-id-card"></i> <span>Registrasi Wajah</span>
                                        </a>
                                    </li>
                                    <li class="{{ request()->routeIs('attendance.scanner') ? 'active' : '' }}">
                                        <a href="{{ route('attendance.scanner') }}">
                                            <i class="fa fa-video-camera"></i> <span>Mulai Presensi</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            {{-- Laporan --}}
                            <li class="{{ request()->is('laporan*') ? 'active' : '' }}">
                                <a href="#Laporan" class="has-arrow">
                                    <i class="fa fa-file-text"></i>
                                    <span>Laporan</span>
                                </a>
                                <ul class="collapse {{ request()->is('laporan*') ? 'in' : '' }}">
                                    <li class="{{ request()->routeIs('laporan.absensi.kelas') ? 'active' : '' }}">
                                        <a href="{{ route('laporan.absensi.kelas') }}">
                                            <i class="fa fa-circle-o"></i> <span>Absensi Harian</span>
                                        </a>
                                    </li>
                                    <li class="{{ request()->routeIs('laporan.absensi.mapel') ? 'active' : '' }}">
                                        <a href="{{ route('laporan.absensi.mapel') }}">
                                            <i class="fa fa-circle-o"></i> <span>Absensi Mapel</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if(auth()->user()->role === 'siswa')
                            @php
                                $myRombel = auth()->user()->pesertaDidik->anggotaRombel()->latest()->first();
                            @endphp

                            <li class="{{ request()->routeIs('rombongan-belajar.show') ? 'active' : '' }}">
                                @if($myRombel)
                                    <a href="{{ route('rombongan-belajar.show', $myRombel->rombongan_belajar_id) }}">
                                        <i class="fa fa-users"></i>
                                        <span>Kelas Saya</span>
                                    </a>
                                @else
                                    <a href="javascript:void(0);" class="text-muted">
                                        <i class="fa fa-users"></i>
                                        <span>Belum Ada Kelas</span>
                                    </a>
                                @endif
                            </li>

                            <li class="{{ request()->routeIs('attendance.scanner') ? 'active' : '' }}">
                                <a href="{{ route('attendance.scanner') }}">
                                    <i class="fa fa-video-camera"></i>
                                    <span>Presensi</span>
                                </a>
                            </li>

                            <li class="{{ request()->is('attendance-permissions*') ? 'active' : '' }}">
                                <a href="{{ route('attendance-permissions.index') }}">
                                    <i class="fa fa-file-text"></i>
                                    <span>Pengajuan Izin</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
            <div class="tab-pane" id="Chat">
                <form>
                    <div class="input-group m-b-20">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="icon-magnifier"></i></span>
                        </div>
                        <input type="text" class="form-control" placeholder="Search...">
                    </div>
                </form>
            </div>
            <div class="tab-pane" id="setting">
                <h6>Choose Skin</h6>
                <ul class="choose-skin list-unstyled">
                    <li data-theme="purple">
                        <div class="purple"></div>
                    </li>
                    <li data-theme="blue">
                        <div class="blue"></div>
                    </li>
                    <li data-theme="cyan" class="active">
                        <div class="cyan"></div>
                    </li>
                    <li data-theme="green">
                        <div class="green"></div>
                    </li>
                    <li data-theme="orange">
                        <div class="orange"></div>
                    </li>
                    <li data-theme="blush">
                        <div class="blush"></div>
                    </li>
                    <li data-theme="red">
                        <div class="red"></div>
                    </li>
                </ul>

                <ul class="list-unstyled font_setting mt-3">
                    <li>
                        <label class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" name="font" value="font-nunito" checked="">
                            <span class="custom-control-label">Nunito Google Font</span>
                        </label>
                    </li>
                    <li>
                        <label class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" name="font" value="font-ubuntu">
                            <span class="custom-control-label">Ubuntu Font</span>
                        </label>
                    </li>
                    <li>
                        <label class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" name="font" value="font-raleway">
                            <span class="custom-control-label">Raleway Google Font</span>
                        </label>
                    </li>
                    <li>
                        <label class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" name="font" value="font-IBMplex">
                            <span class="custom-control-label">IBM Plex Google Font</span>
                        </label>
                    </li>
                </ul>

                <ul class="list-unstyled mt-3">
                    <li class="d-flex align-items-center mb-2">
                        <label class="toggle-switch theme-switch">
                            <input type="checkbox">
                            <span class="toggle-switch-slider"></span>
                        </label>
                        <span class="ml-3">Enable Dark Mode!</span>
                    </li>
                    <li class="d-flex align-items-center mb-2">
                        <label class="toggle-switch theme-rtl">
                            <input type="checkbox">
                            <span class="toggle-switch-slider"></span>
                        </label>
                        <span class="ml-3">Enable RTL Mode!</span>
                    </li>
                    <li class="d-flex align-items-center mb-2">
                        <label class="toggle-switch theme-high-contrast">
                            <input type="checkbox">
                            <span class="toggle-switch-slider"></span>
                        </label>
                        <span class="ml-3">Enable High Contrast Mode!</span>
                    </li>
                </ul>

                <hr>
                <h6>General Settings</h6>
                <ul class="setting-list list-unstyled">
                    <li>
                        <label class="fancy-checkbox">
                            <input type="checkbox" name="checkbox" checked>
                            <span>Allowed Notifications</span>
                        </label>
                    </li>
                    <li>
                        <label class="fancy-checkbox">
                            <input type="checkbox" name="checkbox">
                            <span>Offline</span>
                        </label>
                    </li>
                    <li>
                        <label class="fancy-checkbox">
                            <input type="checkbox" name="checkbox">
                            <span>Location Permission</span>
                        </label>
                    </li>
                </ul>

                <a href="#" target="_blank" class="btn btn-block btn-primary">Buy this item</a>
                <a href="#" target="_blank" class="btn btn-block btn-secondary">View portfolio</a>
            </div>

            <div class="tab-pane" id="question">
                <form>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="icon-magnifier"></i></span>
                        </div>
                        <input type="text" class="form-control" placeholder="Search...">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>