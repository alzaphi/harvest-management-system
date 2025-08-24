<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Vehicle;
use App\Models\User;

class Vendor extends VendorAngkut
{
    protected $table = 'vendor_angkut';

    protected $fillable = [
        'kode_vendor',
        'nama_vendor',
        'no_hp',
        'jenis_vendor',
        'jumlah_tenaga_kerja',
        'status',
        'nomor_rekening',
        'nama_bank',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id';

    public $incrementing = true;

    public $timestamps = true;

    /**
     * Get the vehicles for the vendor.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'kode_vendor', 'kode_vendor');
    }

    /**
     * Get the user associated with the vendor.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'vendor_id');
    }

    public function spts()
    {
        return $this->hasMany(SPT::class, 'kode_vendor', 'kode_vendor');
    }

}
