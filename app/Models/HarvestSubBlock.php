<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HarvestSubBlock extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_petak',
        'estate',
        'divisi',
        'luas_area',
        'harvest_season',
        'age_months',
        'yield_estimate_tph',
        'planned_harvest_date',
        'priority_level',
        'remarks'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'planned_harvest_date' => 'date',
        'yield_estimate_tph' => 'decimal:2',
        'luas_area' => 'double',
        'age_months' => 'integer',
        'priority_level' => 'integer',
    ];

    /**
     * Get the sub-block that owns the harvest record.
     */
    public function subBlock()
    {
        return $this->belongsTo(SubBlock::class, 'kode_petak', 'kode_petak');
    }

    /**
     * Get the tracking activities for the harvest sub-block.
     */
    public function trackingActivity()
    {
        return $this->hasMany(TrackingActivity::class, 'kode_petak', 'kode_petak');
    }
}
