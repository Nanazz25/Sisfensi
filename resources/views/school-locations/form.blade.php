@extends('layouts.app')
@section('title', isset($schoolLocation) ? 'Edit Lokasi Sekolah' : 'Tambah Lokasi Sekolah')

@section('content')
    <div class="card">
        <div class="header">
            <h2>{{ isset($schoolLocation) ? 'Edit Lokasi Sekolah' : 'Tambah Lokasi Sekolah' }}</h2>
        </div>

        <div class="body">
            <form method="POST" action="{{ isset($schoolLocation)
        ? route('school-locations.update', $schoolLocation->id)
        : route('school-locations.store') }}">
                @csrf
                @if(isset($schoolLocation)) @method('PUT') @endif

                <div class="form-group">
                    <label>Nama Lokasi</label>
                    <input type="text" name="nama_lokasi" class="form-control"
                        value="{{ old('nama_lokasi', $schoolLocation->nama_lokasi ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>Lokasi di Peta</label>
                    <div class="input-group mb-3">
                        <input type="text" id="searchLocation" class="form-control" placeholder="Cari lokasi...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                        </div>
                    </div>
                    <div id="map" style="height: 400px;"></div>
                </div>

                <input type="hidden" name="latitude" id="latitude"
                    value="{{ old('latitude', $schoolLocation->latitude ?? '') }}">

                <input type="hidden" name="longitude" id="longitude"
                    value="{{ old('longitude', $schoolLocation->longitude ?? '') }}">

                <div class="form-group">
                    <label>Radius Maks (meter)</label>
                    <input type="number" name="radius_maks" id="radius_maks" class="form-control"
                        value="{{ old('radius_maks', $schoolLocation->radius_maks ?? 100) }}" required>
                </div>

                <button class="btn btn-primary">
                    {{ isset($schoolLocation) ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('school-locations.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </form>
        </div>
    </div>
@endsection

@section('afterAppScripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        const defaultLat = {{ old('latitude', $schoolLocation->latitude ?? -6.200000) }};
        const defaultLng = {{ old('longitude', $schoolLocation->longitude ?? 106.816666) }};

        const map = L.map('map').setView([defaultLat, defaultLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

        function updateLatLng(lat, lng) {
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        }

        marker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            updateLatLng(pos.lat, pos.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateLatLng(e.latlng.lat, e.latlng.lng);
        });

        document.getElementById('searchLocation').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();

                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${this.value}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length > 0) {
                            const lat = data[0].lat;
                            const lon = data[0].lon;

                            map.setView([lat, lon], 17);
                            marker.setLatLng([lat, lon]);
                            updateLatLng(lat, lon);
                        }
                    });
            }
        });

        // Circle awal
        let radius = parseInt(document.getElementById('radius_maks').value) || 100;
        let circle = L.circle([defaultLat, defaultLng], { radius: radius }).addTo(map);

        // Update circle saat radius berubah
        document.getElementById('radius_maks').addEventListener('input', function () {
            radius = parseInt(this.value) || 100;
            circle.setRadius(radius);
        });

    </script>
@endsection