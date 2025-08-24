<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class VendorTebang extends Model
{
    protected $table = 'vendor_tebang';

    protected $fillable = [
        'kode_vendor',
        'nama_vendor',
        'no_hp',
        'jenis_vendor',
        'status',
    ];

    public function spts(): HasMany
    {
        return $this->hasMany(SPT::class, 'kode_vendor_tebang', 'kode_vendor');
    }
}
