import geojson
import json
import random

# Baca file GeoJSON hasil bersih
with open("jbm_clean.geojson") as f:
    data = geojson.load(f)

# Filter hanya kode petak dari estate LKL, PLG, RST
filtered_features = [
    feat for feat in data['features']
    if (feat['properties'].get('divisi', '').upper().startswith(('LKL', 'PLG', 'RST')))
]

# Acak dan ambil maksimal 100 petak
random.seed(42)  # supaya hasil tetap bisa direproduksi
selected_features = random.sample(filtered_features, min(100, len(filtered_features)))

# Buat GeoJSON baru
tebang_data = {
    "type": "FeatureCollection",
    "features": selected_features
}

# Simpan ke file baru
with open("tebang.geojson", "w") as f:
    json.dump(tebang_data, f)

print(f"✅ {len(selected_features)} kode petak disimpan ke tebang.geojson")


