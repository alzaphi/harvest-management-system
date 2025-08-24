<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingActivity extends Model
{
    use HasFactory;

    protected $table = 'tracking_activity';

    protected $fillable = [
        'kode_spt',
        'kode_petak',
        'status_tracking',
        'updated_by'
    ];

    public function spt()
    {
        return $this->belongsTo(SPT::class, 'kode_spt', 'kode_spt');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'kode_vendor', 'kode_vendor');
    }

    public function foreman()
    {
        return $this->belongsTo(Foreman::class, 'kode_mandor', 'kode_mandor');
    }

    public static function createFromSPT(SPT $spt)
    {
        $tracking = new static;
        $tracking->kode_spt = $spt->kode_spt;
        $tracking->kode_petak = $spt->kode_petak;
        $tracking->status_tracking = 'not_started'; // Initial status is always not started
        $tracking->updated_by = auth()->user()->name ?? 'System';
        $tracking->save();
        return $tracking;
    }

    public function getLktCountAttribute()
    {
        // Return the count of LKTs from the related SPT
        return $this->spt ? $this->spt->lkt->count() : 0;
    }
}
