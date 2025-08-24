<!DOCTYPE html>
<html>
<head>
    <title>Full Map - Sub Blocks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        #map {
            width: 100%;
            height: 100%;
        }

        .leaflet-popup-content {
            font-size: 14px;
        }
    </style>
</head>
<body>

<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    const map = L.map('map').setView([-4.5, 122.0], 12); // Bombana

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const geojsonData = {!! $geojson !!};

    function getRandomColor() {
        return '#' + Math.floor(Math.random()*16777215).toString(16);
    }

    const group = L.featureGroup();

    geojsonData.features.forEach(function(feature) {
        const layer = L.geoJSON(feature, {
            style: {
                color: getRandomColor(),
                weight: 2,
                fillOpacity: 0.4
            },
            onEachFeature: function (f, l) {
                l.bindPopup(`<b>Kode Petak:</b> ${f.properties.kode_petak ?? 'N/A'}`);
            }
        }).addTo(map);

        group.addLayer(layer);
    });

    if (group.getLayers().length > 0) {
        map.fitBounds(group.getBounds().pad(0.1));
    } else {
        console.warn("No sub blocks found.");
    }

    // Fix Leaflet rendering delay
    setTimeout(() => {
        map.invalidateSize();
    }, 300);
</script>

</body>
</html>
