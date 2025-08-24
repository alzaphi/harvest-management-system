import json
import pymysql
from shapely.geometry import shape
from shapely.ops import transform
import pyproj

# Konversi koordinat WGS84 ke Web Mercator untuk hitung luas (meter)
project = pyproj.Transformer.from_crs("EPSG:4326", "EPSG:3857", always_xy=True).transform

# Koneksi ke database
conn = pymysql.connect(
    host="localhost",
    user="root",
    password="",  # sesuaikan
    database="harvest_management",
    charset="utf8mb4",
    cursorclass=pymysql.cursors.DictCursor
)
cursor = conn.cursor()

# Baca GeoJSON
with open('jbm_with_age.geojson') as f:
    data = json.load(f)

for feature in data['features']:
    props = feature['properties']
    geom = shape(feature['geometry'])

    kode_petak = props.get('sub_petak') or props.get('kode_petak')
    estate = props.get('unit_kbn') or ''
    divisi = props.get('divisi') or ''
    blok = props.get('blok') or ''
    luas_area = props.get('luas_area_ha') or ''
    age = props.get('age_months') or 0
    geom_json = json.dumps(feature['geometry'])
    aktif = 1

    # Zona berdasarkan divisi
    divisi_upper = divisi.upper()
    if divisi_upper.startswith('LKL'):
        zona = '1'
    elif divisi_upper.startswith('PLG'):
        zona = '2' if '2' in divisi_upper else '3'
    elif divisi_upper.startswith('RST'):
        zona = '4'
    else:
        zona = None

    # Keterangan
    if age >= 11:
        keterangan = 'Petak JBM - Layak Tebang'
    else:
        keterangan = 'Petak JBM'

    try:
        sql = """
        INSERT INTO sub_blocks (
            kode_petak, estate, divisi, blok, luas_area, age_months,
            geom_json, aktif, zona, keterangan
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        cursor.execute(sql, (
            kode_petak, estate, divisi, blok, luas_area, age,
            geom_json, aktif, zona, keterangan
        ))
    except Exception as e:
        print(f"Gagal insert {kode_petak}: {e}")

# Commit dan tutup koneksi
conn.commit()
cursor.close()
conn.close()

print("✅ Import selesai ke tabel sub_blocks.")
