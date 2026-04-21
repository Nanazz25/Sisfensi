@extends('layouts.app')

@section('title', 'Dompet Integritas')

@section('afterAppStyles')
    <link rel="stylesheet" href="{{ asset('css/integrity.user.css') }}">
    @include('integrity.user.partials.styles')
@endsection

@section('content')
<div class="wallet-container shadow-sm">
    <div class="row">
        
        @include('integrity.user.partials.sidebar')

        <!-- AREA KONTEN DINAMIS (KANAN) -->
        <div class="col-lg-7 col-xl-8">
            <div class="wallet-content">
                @include('integrity.user.partials.history')
                @include('integrity.user.partials.shop')
                @include('integrity.user.partials.inventory')
            </div>
        </div>

    </div>
</div>

@include('integrity.user.modals')

<!-- Floating Scroll to Top Button (Mobile Only) -->
<button type="button" id="backToTop" class="btn btn-primary rounded-circle shadow-lg" style="position: fixed; z-index: 1000; transition: all 0.3s ease;">
    <i class="fa fa-chevron-up"></i>
</button>

@endsection

@section('afterAppScripts')
    @include('integrity.user.partials.scripts')
@endsection
