@extends('layouts.app')

@section('title', 'Manajemen Integritas')

@section('afterAppStyles')
    <link rel="stylesheet" href="{{ asset('css/integrity.admin.css') }}">
@endsection

@section('content')
    <!-- Statistik Utama -->
    @include('integrity.admin.partials._stats')

    <div class="row clearfix">
        <!-- Rule Manager (Kiri/Penuh) -->
        <div class="col-lg-12">
            @include('integrity.admin.partials._rules')
        </div>

        <!-- Marketplace Manager (Kiri-Bawah) -->
        <div class="col-lg-7">
            @include('integrity.admin.partials._marketplace')
        </div>

        <!-- Leaderboard (Kanan-Bawah) -->
        <div class="col-lg-5">
            @include('integrity.admin.partials._leaderboard')
        </div>
    </div>

    <!-- Semua Modal di satu tempat -->
    @include('integrity.admin.partials._modals')
@endsection

@section('afterAppScripts')
    <script src="{{ asset('js/integrity.admin.js') }}"></script>
@endsection
