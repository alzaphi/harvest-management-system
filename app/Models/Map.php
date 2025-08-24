<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    protected $table = 'maps';
    
    // Disable automatic handling of created_at and updated_at
    public $timestamps = false;
    
    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'uploaded_by',
        'estate_name',
        'description',
        'upload_date'
    ];

    protected $casts = [
        'upload_date' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the name of the "created at" column.
     *
     * @return string
     */
    public function getCreatedAtColumn()
    {
        return 'upload_date';
    }
    
    /**
     * Get the name of the "updated at" column.
     *
     * @return string
     */
    public function getUpdatedAtColumn()
    {
        return 'updated_at';
    }

    // Get the full storage path for the file
    public function getFilePathAttribute($value)
    {
        return $value ? 'storage/' . $value : null;
    }
}
