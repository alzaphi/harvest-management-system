import geojson
import random
import json

# Baca file jbm_clean.geojson
with open('jbm_clean.geojson', 'r') as f:
    data = geojson.load(f)

features = data['features']

# Filter yang estate-nya LKL atau PLG
eligible = [f for f in features if (f['properties'].get('estate') or f['properties'].get('divisi', '')).upper().startswith(('LKL', 'PLG'))]

# Ambil 100 sub_petak random dari eligible
selected_high_age = random.sample(eligible, min(100, len(eligible)))

# Tandai sub_petak yang dipilih agar tidak dapat age rendah
selected_ids = set([f['properties'].get('sub_petak') or f['properties'].get('kode_petak') for f in selected_high_age])

for feature in features:
    props = feature['properties']
    kode = props.get('sub_petak') or props.get('kode_petak')
    if kode in selected_ids:
        props['age_months'] = random.choice([11, 12, 13, 14])
    else:
        props['age_months'] = random.choice([7, 8, 9, 10])

# Simpan kembali ke file baru
with open('jbm_with_age.geojson', 'w') as f:
    json.dump(data, f)

print("✅ Sukses menambahkan kolom age_months ke jbm_with_age.geojson.")
