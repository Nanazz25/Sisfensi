@extends('layouts.app')
@section('title', 'Lokasi Sekolah')

@section('content')
    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Lokasi Sekolah</h2>
            <a href="{{ route('school-locations.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Lokasi
            </a>
        </div>

        <div class="body">
            <div id="map" style="height: 400px; margin-bottom: 20px;"></div>

            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Lokasi</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Radius Maks (m)</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_lokasi }}</td>
                                <td>{{ $item->latitude }}</td>
                                <td>{{ $item->longitude }}</td>
                                <td>{{ $item->radius_maks }}</td>
                                <td>
                                    <a href="{{ route('school-locations.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger btn-delete" data-name="{{ $item->nama_lokasi }}"
                                        data-action="{{ route('school-locations.destroy', $item->id) }}" data-toggle="modal"
                                        data-target="#deleteModal">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Data kosong</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-modal-delete title="Hapus Lokasi Sekolah" message="Yakin hapus lokasi sekolah berikut?" />
@endsection

@section('afterAppStyles')
    <style>
        /* Fix Leaflet z-index overlapping sidebar and other elements */
        .leaflet-top,
        .leaflet-bottom {
            z-index: 900 !important;
        }

        .leaflet-pane {
            z-index: 0 !important;
        }

        .leaflet-control-container .leaflet-top.leaflet-left {
            z-index: 900 !important;
        }
    </style>
@endsection

@section('afterAppScripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        // Center map ke lokasi default
        const defaultLat = -6.200000;
        const defaultLng = 106.816666;
        const map = L.map('map').setView([defaultLat, defaultLng], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        const locations = @json($locations);

        locations.forEach(loc => {
            // Marker untuk tiap lokasi
            const marker = L.marker([loc.latitude, loc.longitude]).addTo(map);

            // Circle radius lokasi
            const circle = L.circle([loc.latitude, loc.longitude], {
                radius: loc.radius_maks,
                color: 'blue',
                fillColor: '#3f51b5',
                fillOpacity: 0.2
            }).addTo(map);

            // Popup info
            marker.bindPopup(`
                <b>${loc.nama_lokasi}</b><br>
                Latitude: ${loc.latitude}<br>
                Longitude: ${loc.longitude}<br>
                Radius: ${loc.radius_maks} m
            `);
        });

        // Auto-fit semua lokasi ke map
        if (locations.length > 0) {
            const group = new L.featureGroup(locations.map(loc => L.marker([loc.latitude, loc.longitude])));
            map.fitBounds(group.getBounds().pad(0.2));
        }
    </script>
@endsection