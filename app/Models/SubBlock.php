<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubBlock extends Model
{
    use HasFactory;

    protected $table = 'sub_blocks';

    protected $primaryKey = 'id';

    public $incrementing = true;

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'kode_petak',
        'estate',
        'divisi',
        'blok',
        'luas_area',
        'age_months',
        'geom_json',
        'aktif',
        'zona',
        'keterangan',
        'shape_length',
        'shape_area',
        'geometry',
    ];

    protected $casts = [
        'luas_area' => 'float',
        'shape_length' => 'float',
        'shape_area' => 'float',
        'aktif' => 'boolean',
        'geom_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the geometry attribute
     *
     * @param  mixed  $value
     * @return array|null
     */
    public function getGeometryAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        // If it's already a string, it might be a JSON string
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            return null;
        }

        // If it's a MySQL geometry object, convert it to GeoJSON
        try {
            $geometry = DB::selectOne(
                'SELECT ST_AsGeoJSON(?) as geojson',
                [$value]
            );

            return $geometry ? json_decode($geometry->geojson, true) : null;
        } catch (\Exception $e) {
            \Log::error('Error converting geometry to GeoJSON: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Set the geometry attribute
     *
     * @param  mixed  $value
     * @return void
     */
    public function setGeometryAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['geometry'] = null;
            return;
        }

        // If it's already a string, it might be a GeoJSON string
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        // If it's an array, convert it to a GeoJSON string
        if (is_array($value)) {
            $value = json_encode($value);
        }

        // Use a raw query to set the geometry
        $this->attributes['geometry'] = DB::raw(
            "ST_GeomFromText('" . addslashes($value) . "')"
        );
    }

    /**
     * Get the status associated with the sub-block.
     */
    public function status()
    {
        return $this->hasOne(StatusSubBlock::class, 'kode_petak', 'kode_petak');
    }

    /**
     * Generate polygon coordinates based on area
     *
     * @param float $centerLat Center latitude
     * @param float $centerLng Center longitude
     * @param float $area Area in hectares
     * @param int $sides Number of sides for the polygon (default: 4 for rectangle)
     * @return array
     */
    public static function generatePolygon($centerLat, $centerLng, $area, $sides = 4)
    {
        // Convert hectares to square meters
        $areaM2 = $area * 10000;

        // Calculate side length for a square (approximate)
        $sideLength = sqrt($areaM2);

        // Convert meters to degrees (approximate)
        $latLength = $sideLength / 111320; // 1 degree = ~111,320 meters
        $lngLength = $sideLength / (111320 * cos(deg2rad($centerLat)));

        // Generate polygon points
        $points = [];
        $angleStep = 2 * M_PI / $sides;

        for ($i = 0; $i < $sides; $i++) {
            $angle = $i * $angleStep;
            $x = $centerLng + ($lngLength * cos($angle));
            $y = $centerLat + ($latLength * sin($angle));
            $points[] = [$x, $y];
        }

        // Close the polygon
        $points[] = $points[0];

        return [
            'type' => 'Polygon',
            'coordinates' => [$points]
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Get the validation rules for the model.
     *
     * @return array
     */
    public static function rules($id = null)
    {
        return [
            'kode_petak' => 'required|string|max:50|unique:sub_blocks,kode_petak,' . $id . ',id',
            'estate' => 'required|string|max:100',
            'divisi' => 'required|string|max:100',
            'blok' => 'required|string|max:50',
            'luas_area' => 'required|numeric|min:0',
            'zona' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string|max:255',
            'aktif' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active sub-blocks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Get the estate that owns the sub-block.
     */
    public function estateRelation()
    {
        // Adjust the relationship based on your actual database structure
        return $this->belongsTo(Estate::class, 'estate', 'name');
    }

    /**
     * Get the division that owns the sub-block.
     */
    public function division()
    {
        // Adjust the relationship based on your actual database structure
        return $this->belongsTo(Division::class, 'divisi', 'name');
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Add global scopes, events, etc. here
        static::saving(function ($model) {
            // Add any pre-save logic here
        });
    }
}
