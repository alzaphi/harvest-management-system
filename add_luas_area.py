import geojson
import json
from shapely.geometry import shape
from shapely.ops import transform
import pyproj

# Fungsi untuk konversi koordinat dari EPSG:4326 ke meter (Web Mercator)
project = pyproj.Transformer.from_crs("EPSG:4326", "EPSG:3857", always_xy=True).transform

# Baca file GeoJSON asli
with open("jbm.geojson") as f:
    data = geojson.load(f)

# Siapkan list untuk menyimpan fitur unik
unique_features = []
seen_sub_petak = set()

# Loop tiap feature dan proses jika sub_petak belum pernah muncul
for feature in data['features']:
    props = feature['properties']
    sub_petak = props.get('sub_petak')

    # Lewati jika sub_petak sudah pernah dimasukkan
    if sub_petak in seen_sub_petak:
        continue

    seen_sub_petak.add(sub_petak)

    # Hitung luas dari geometri
    geom = shape(feature['geometry'])
    geom_m = transform(project, geom)
    luas_ha = geom_m.area / 10000  # m² → hektar
    props['luas_area_ha'] = round(luas_ha, 4)

    # Tambahkan ke daftar fitur unik
    unique_features.append(feature)

# Buat GeoJSON baru
cleaned_data = {
    "type": "FeatureCollection",
    "features": unique_features
}

# Simpan ke file baru
with open("jbm_clean.geojson", "w") as f:
    json.dump(cleaned_data, f)

print(f"✅ {len(unique_features)} fitur unik disimpan ke jbm_clean.geojson")
