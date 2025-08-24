<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplainBappAngkut extends Model
{
    protected $table = 'complainbappangkut';
    protected $primaryKey = 'complain_id';
    
    protected $fillable = [
        'kode_bapp',
        'deskripsi',
        'tanggal'
    ];

    protected $dates = ['tanggal'];

    public $timestamps = false;
    public $incrementing = true;

    /**
     * Get the BAPP that owns the complaint.
     */
    public function bapp()
    {
        return $this->belongsTo(BappAngkut::class, 'kode_bapp', 'kode_bapp');
    }
}
