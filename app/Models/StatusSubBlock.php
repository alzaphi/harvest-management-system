<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;

class StatusSubBlock extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'kode_petak',
        'tanggal_update',
        'tahun',
        'status',
        'luas_status',
        'keterangan',
        'aktif'
    ];

    protected $casts = [
        'tanggal_update' => 'datetime',
        'aktif' => 'boolean',
        'luas_status' => 'float',
        'geom' => 'array' // For handling geometry data
    ];

    /**
     * Get the sub-block that owns the status.
     */
    public function subBlock()
    {
        return $this->belongsTo(SubBlock::class, 'kode_petak', 'kode_petak');
    }

    /**
     * Get the geometry attribute as an array
     */
    public function getGeomAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        // If it's already an array (like GeoJSON), return it
        if (is_array($value)) {
            return $value;
        }

        // Convert WKB to GeoJSON if needed
        if (is_string($value)) {
            try {
                // Use raw SQL to convert WKB to GeoJSON
                $result = DB::selectOne(
                    "SELECT ST_AsGeoJSON(ST_GeomFromWKB(?)) as geojson",
                    [$value]
                );

                if ($result && !empty($result->geojson)) {
                    return json_decode($result->geojson, true);
                }
            } catch (\Exception $e) {
                Log::error('Error converting geometry to GeoJSON: ' . $e->getMessage());
                return null;
            }
        }

        return $value;
    }
}
