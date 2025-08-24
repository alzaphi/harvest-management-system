@extends('layouts.master')

@php
$header = 'GIS Information';
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => route('dashboard')],
    ['title' => $header]
];
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<style>
    html, body {
        height: 100%;
        margin: 0;
    }

    .map-container {
        height: 100vh;
        width: 100%;
    }

    #map {
        width: 100%;
        height: 100%;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 0.5rem rgba(0, 0, 0, 0.1);
        border: 1px solid #e3e6f0;
        background:rgb(252, 250, 248);
    }

    .leaflet-popup-content {
        font-size: 14px;
    }

    .estate-label {
        background-color: rgba(255, 255, 255, 0.8);
        color: #000;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 13px;
        border: 1px solid #999;
        box-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.8rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: capitalize;
        white-space: nowrap;
        line-height: 1.5;
        text-align: center;
        min-width: 70px;
    }
    .status-active {
        background-color: #D1FAE5;
        color: #065F46;
    }
    .status-inactive {
        background-color: #FEE2E2;
        color: #991B1B;
    }
    .vendor-table th:first-child,
    .vendor-table td:first-child {
        width: 1%;
        white-space: nowrap;
    }
    .action-buttons {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
    }
    .btn-action {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    .menu-tabs-wrapper {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
        margin-top: -1.5rem;
    }
    .menu-tabs {
        display: inline-flex;
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
        overflow: hidden;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    }
    .tab-button1 {
        padding: 0.75rem 1.5rem;
        background: #f1f5f9;
        border: none;
        color: #64748b;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        position: relative;
        border-right: 1px solid #e2e8f0;
        border-top: 1px solid #e2e8f0;
        border-left: 1px solid #e2e8f0;
    }
    .tab-button1:last-child {
        border-right: none;
    }
    .tab-button1:hover {
        background: #e2e8f0;
        color: #1e40af;
    }
    .tab-button1.active {
        background: #ffffff;
        color: #1e40af;
        font-weight: 600;
        box-shadow: 0 -3px 10px rgba(0, 0, 0, 0.05);
        transform: translateY(-1px);
    }
    .tab-button1.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2px;
        background: #1e40af;
    }
    .kode-petak-label {
    font-weight: bold;
    color: #1e40af;
    background-color: rgba(255, 255, 255, 0.6);
    border: 1px solid #ccc;
    padding: 1px 4px;
    border-radius: 4px;
    pointer-events: none;
    text-align: center;
    font-size: 12px;
    }


    .info.legend {
        background: white;
        padding: 10px;
        line-height: 1.5;
        color: #333;
        border-radius: 5px;
        font-size: 13px;
        box-shadow: 0 0 5px rgba(0,0,0,0.2);
    }

    /* Custom layer control styles */
    .leaflet-control-layers {
        border-radius: 4px;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        font-size: 12px;
    }

    .leaflet-control-layers-toggle {
        background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzE2YTc2MyIgZD0iTTE5LDE5SDVWNUgxOVYxOU0xOSwzSDVDMy44OSwzIDMsMy45IDMsNVYxOUMzLDIwLjEgMy45LDIxIDUsMjFIMTlDMjAuMSwyMSAyMSwyMC4xIDIxLDE5VjVDMjEsMy45IDIwLjEsMyAxOSwzWiIvPjwvc3ZnPg==');
        width: 28px;
        height: 28px;
        cursor: pointer;
        background-size: 18px;
        background-position: center;
        background-repeat: no-repeat;
    }

    .leaflet-control-layers-list {
        padding: 6px 8px;
    }

    .leaflet-control-layers-base,
    .leaflet-control-layers-overlays {
        margin: 6px 0;
    }

    .leaflet-control-layers-overlays label {
        display: flex !important;
        align-items: center;
        margin: 4px 0;
        padding: 2px 6px;
        border-radius: 3px;
        transition: all 0.2s;
        cursor: pointer;
        font-size: 11px;
    }

    .leaflet-control-layers-overlays label:hover {
        background-color: #f0f4f8;
    }

    .leaflet-control-layers-selector {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        margin: 0 8px 0 0 !important;
        border: 1px solid #94a3b8;
        border-radius: 3px;
        background-color: white;
        cursor: pointer;
        vertical-align: middle;
        position: relative;
    }

    .leaflet-control-layers-selector:checked {
        background-color: #16a763 !important;
        border-color: #16a763 !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: center !important;
        background-size: 10px !important;
    }

    .leaflet-control-layers-selector:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(22, 167, 99, 0.3);
    }

    .leaflet-control-layers label span {
        flex: 1;
        cursor: pointer;
        user-select: none;
        line-height: 1.3;
    }

    /* Fix for the forbidden cursor issue */
    .leaflet-control-layers-selector,
    .leaflet-control-layers-selector + span,
    .leaflet-control-layers-overlays label {
        cursor: pointer !important;
    }


</style>
@endpush


@section('content')
<div class="vendor-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Maps View</h2>
        <!-- Search and other header elements can go here if needed -->
    </div>

    <!-- Menu Tabs -->
    <div class="menu-tabs-wrapper">
        <div class="menu-tabs">
            <a href="{{ route('gis.index') }}" class="tab-button1 {{ request()->routeIs('gis.index') ? 'active' : '' }}">Maps View</a>
            @can('upload-gis')
            <a href="{{ route('gis.create') }}" class="tab-button1 {{ request()->routeIs('gis.create') ? 'active' : '' }}">Maps Upload</a>
            @endcan
        </div>
    </div>


    <div class="map-container">
        <div id="map"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script src="https://unpkg.com/leaflet-easyprint"></script>



<script>


    // Initialize the map
    const map = L.map('map').setView([-4.56, 122.0], 18); // Bombana

    // Define base layers
    const openStreetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    });

    const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    });

    // Add default base layer (OpenStreetMap)
    openStreetMap.addTo(map);

    const geojsonData = {!! $geojson !!};
    const tebangGeojson = {!! $tebangGeojson !!};
    const harvestGeojson = {!! $harvestGeojson !!};

    // Status colors mapping
    const statusColors = {
        'planned': {
            fill: '#6b7280',    // gray-500
            border: '#4b5563'   // gray-600
        },
        'not_started': {
            fill: '#3b82f6',    // blue-500
            border: '#2563eb'   // blue-600
        },
        'in_progress': {
            fill: '#f59e0b',    // yellow-500
            border: '#d97706'   // yellow-600
        },
        'completed': {
            fill: '#10b981',    // green-500
            border: '#059669'   // green-600
        }
    };

    // Status labels
    const statusLabels = {
        'planned': 'Planned',
        'not_started': 'Not Started',
        'in_progress': 'In Progress',
        'completed': 'Completed'
    };

    // Harvest sub-blocks layer (higher z-index to appear above Petak JBM)
    const harvestLayer = L.geoJSON(harvestGeojson, {
        style: function(feature) {
            const status = feature.properties.status || 'planned';
            const colors = statusColors[status] || statusColors.planned;
            
            return {
                color: colors.border,
                weight: 2,
                fillOpacity: 0.6,
                fillColor: colors.fill,
                zIndex: 1000  // Higher z-index to appear above Petak JBM
            };
        },
        onEachFeature: function (feature, layer) {
            const props = feature.properties;
            const status = props.status || 'planned';
            const statusLabel = statusLabels[status] || 'Planned';
            const statusColor = statusColors[status] || statusColors.planned;
            const harvestDate = props.planned_harvest_date ? new Date(props.planned_harvest_date).toLocaleDateString('id-ID') : 'Belum ditentukan';

            // Create a custom status badge
            const statusBadge = `
                <span style="
                    display: inline-block;
                    padding: 0.25rem 0.5rem;
                    border-radius: 9999px;
                    font-size: 0.75rem;
                    font-weight: 500;
                    background-color: ${statusColor.fill};
                    color: white;
                    margin-bottom: 5px;
                ">
                    ${statusLabel}
                </span>
            `;

            const popup = `
                <div style="min-width: 200px;">
                    <h4 style="margin: 0 0 10px 0; color: #8b5a2b; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                        PETAK PANEN
                    </h4>
                    ${statusBadge}
                    <div style="margin-top: 10px;">
                        <b>Kode Petak:</b> ${props.kode_petak || 'N/A'}<br>
                        <b>Status:</b> ${statusLabel}<br>
                        <b>Rencana Panen:</b> ${harvestDate}<br>
                        <b>Divisi:</b> ${props.divisi || 'N/A'}<br>
                        <b>Luas Area:</b> ${props.luas_area ? parseFloat(props.luas_area).toFixed(2) + ' ha' : 'N/A'}<br>
                    </div>
                    <div style="margin-top: 10px; text-align: center;">
                        <a href="/harvest-sub-blocks?search=${props.kode_petak}&show_single=true" class="btn btn-sm btn-outline-primary" style="text-decoration: none;" target="_blank">
                            Lihat Detail Panen
                        </a>
                    </div>
                </div>
            `;
            
            // Store the popup content in the layer for later use
            layer.feature = feature;
            layer.bindPopup(popup);

            // Check if this is the focused harvest sub-block
            if (props.kode_petak === '{{ $focusHarvest }}') {
                // Store the layer reference for later use
                window.focusedHarvestLayer = layer;
            }

            // Add hover effect
            layer.on('mouseover', function() {
                const currentStyle = layer.options.style(feature);
                layer.setStyle({
                    weight: 3,
                    fillOpacity: 0.8,
                    color: currentStyle.color,
                    fillColor: currentStyle.fillColor
                });
                layer.bringToFront();
            });

            layer.on('mouseout', function() {
                harvestLayer.resetStyle(layer);
            });
        }
    });




    // Buat grup SVG layer
    const svgLayer = L.svg();
    svgLayer.addTo(map);
    const group = L.featureGroup({zIndex: 100});  // Lower z-index for Petak JBM
    const focusKodePetak = "{{ $focusPetak }}";
    const petakLabels = [];

    geojsonData.features.forEach(function(feature) {
    const layer = L.geoJSON(feature, {
        style: function(f) {
            const divisi = (f.properties.divisi || '').toUpperCase();
            if (divisi.startsWith('LKL')) return { color: '#006400', fillColor: '#006400', weight: 2, fillOpacity: 0.5 };
            if (divisi.startsWith('PLG')) return { color: '#32CD32', fillColor: '#32CD32', weight: 2, fillOpacity: 0.5 };
            if (divisi.startsWith('RST')) return { color: '#90EE90', fillColor: '#90EE90', weight: 2, fillOpacity: 0.5 };
            return { color: '#A0D468', fillColor: '#A0D468', weight: 2, fillOpacity: 0.5 };
        },
        onEachFeature: function (f, l) {
            const props = f.properties;
            // Simpan untuk kontrol visibilitas berdasarkan zoom
            if (props.kode_petak) {
                petakLabels.push({
                    layer: l,
                    kode: props.kode_petak
                });
            }
            const popup = `
                <b>Kode Petak:</b> ${props.kode_petak ?? 'N/A'}<br>
                <b>Divisi:</b> ${props.divisi ?? 'N/A'}<br>
                <b>Luas Area:</b> ${props.luas_area ? props.luas_area.toFixed(2) + ' ha' : 'N/A'}<br>
                <b><a href="/sub-blocks?kode_petak=${props.kode_petak}" class="btn btn-sm btn-outline-primary" style="text-decoration: none;"target="_blank">
                    Lihat Detail Sub Block
                </a></b>
            `;
            l.bindPopup(popup);


            // Debug: log info saat layer diklik
            l.on('click', function () {
                console.log("Clicked Properties:", props);
            });
            // Jika kode petak cocok, simpan dan buka popup-nya nanti
            if (props.kode_petak === focusKodePetak) {
                setTimeout(() => {
                    map.fitBounds(l.getBounds());
                    l.openPopup();
                }, 500);
            }
        }
    });
    layer.addTo(map);
    group.addLayer(layer);
    });

    // Base layers are now defined above with the map initialization

    // Define overlay layers
    const overlayLayers = {
        "Petak JBM": group,
        "Petak Layak Tebang": harvestLayer
    };

    // Function to maintain layer order
    function bringLayerToTop(layer) {
        layer.bringToFront();
        // If it's a feature group, bring all its layers to front
        if (layer.eachLayer) {
            layer.eachLayer(function(subLayer) {
                subLayer.bringToFront();
            });
        }
    }

    // Add layer control with both base and overlay layers
    const layerControl = L.control.layers(
        { 'OpenStreetMap': openStreetMap, 'Satellite': satellite },
        overlayLayers,
        {
            collapsed: true,
            position: 'topright',
            autoZIndex: false, // Disable autoZIndex to manage it manually
            sortLayers: true
        }
    );

    // Add both Petak JBM and harvest layers to map by default
    group.addTo(map);
    harvestLayer.addTo(map);

    // Show the harvest sub-block popup if it's the focused one
    if (window.focusedHarvestLayer) {
        // Use setTimeout to ensure the map is fully loaded before showing the popup
        setTimeout(() => {
            const layer = window.focusedHarvestLayer;
            const bounds = layer.getBounds();
            map.fitBounds(bounds.pad(0.5)); // Add some padding around the bounds
            
            // Open the popup after a short delay to ensure the map has finished moving
            setTimeout(() => {
                layer.openPopup();
            }, 300);
        }, 500);
    }

    // Add layer control to the map
    layerControl.addTo(map);

    // Ensure proper layer order when toggling layers
    map.on('overlayadd overlayremove', function() {
        // Always bring harvest layer to the top when visible
        if (map.hasLayer(harvestLayer)) {
            bringLayerToTop(harvestLayer);
        }
        // Then bring group layer to the bottom when visible
        if (map.hasLayer(group)) {
            group.bringToBack();
        }
    });

    // Initial layer ordering
    group.bringToBack();
    if (map.hasLayer(harvestLayer)) bringLayerToTop(harvestLayer);

    // Add scale control
    L.control.scale({
        imperial: false,
        metric: true,
        position: 'bottomright'
    }).addTo(map);

    if (group.getLayers().length > 0) {
        map.fitBounds(group.getBounds().pad(-0.2));
    } else {
        console.warn("No sub blocks found.");
    }

    setTimeout(() => {
        map.invalidateSize();
    }, 300);

    const legend = L.control({ position: 'topright' });

    legend.onAdd = function (map) {
        const div = L.DomUtil.create('div', 'info legend');
        
        // Estate Legend
        const estateLabels = [
            { label: 'LKL', color: '#006400' },
            { label: 'PLG', color: '#32CD32' },
            { label: 'RST', color: '#90EE90' },
            { label: 'Other', color: '#A0D468' }
        ];

        div.innerHTML += '<strong>Keterangan Estate</strong><br>';
        estateLabels.forEach(item => {
            div.innerHTML +=
                `<i style="background:${item.color}; width: 14px; height: 14px; display:inline-block; margin-right:5px; border:1px solid #666"></i> ${item.label}<br>`;
        });

        // Add spacing between legends
        div.innerHTML += '<br>';

        // Status Legend
        const statusLabels = [
            { label: 'Planned', color: statusColors.planned.fill, border: statusColors.planned.border },
            { label: 'Not Started', color: statusColors.not_started.fill, border: statusColors.not_started.border },
            { label: 'In Progress', color: statusColors.in_progress.fill, border: statusColors.in_progress.border },
            { label: 'Completed', color: statusColors.completed.fill, border: statusColors.completed.border }
        ];

        div.innerHTML += '<strong>Keterangan Status Panen</strong><br>';
        statusLabels.forEach(item => {
            div.innerHTML +=
                `<i style="background:${item.color}; border:1px solid ${item.border}; width: 14px; height: 14px; display:inline-block; margin-right:5px;"></i> ${item.label}<br>`;
        });

        // Add some styling to the legend
        div.style.padding = '10px';
        div.style.background = 'white';
        div.style.borderRadius = '5px';
        div.style.boxShadow = '0 1px 5px rgba(0,0,0,0.4)';
        div.style.lineHeight = '1.5';

        return div;
    };

    legend.addTo(map);

    const printer = L.easyPrint({
        tileLayer: '', // kosongkan kalau pakai custom tile
        sizeModes: ['Current'],
        filename: 'peta_kode_petak',
        exportOnly: true,
        hideControlContainer: false,
        position: 'topleft'
    }).addTo(map);


    map.on('zoomend', function () {
    const zoom = map.getZoom();

        petakLabels.forEach(item => {
            if (item.layer.getTooltip()) {
                item.layer.unbindTooltip();
            }

            if (zoom >= 17) {
                item.layer.bindTooltip(item.kode, {
                permanent: true,
                direction: 'center',
                className: 'kode-petak-label'
            });
            }
        });
    });

    // Trigger manual untuk awal tampilan
    map.fire('zoomend');





</script>

@endpush
