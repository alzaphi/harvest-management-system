import geopandas as gpd

print("📥 Membaca file jbm.geojson...")
gdf = gpd.read_file("jbm.geojson")

print("🔄 Menggabungkan geometri berdasarkan unit_kbn...")
gdf_dissolved = gdf.dissolve(by="unit_kbn")

print("💾 Menyimpan ke estate_boundaries.geojson...")
gdf_dissolved.to_file("estate_boundaries.geojson", driver='GeoJSON')

print("✅ estate_boundaries.geojson berhasil dibuat.")

