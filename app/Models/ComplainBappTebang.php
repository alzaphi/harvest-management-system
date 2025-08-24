<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplainBappTebang extends Model
{
    protected $table = 'complainbapptebang';
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
        return $this->belongsTo(BappTebang::class, 'kode_bapp', 'kode_bapp');
    }
}
