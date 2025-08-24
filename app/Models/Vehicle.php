<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Vendor;
use App\Models\JenisUnit;

class Vehicle extends Model
{
    protected $table = 'vehicle';

    protected $fillable = [
        'kode_lambung',
        'plat_nomor',
        'jenis_unit_id',
        'kode_vendor',
        'nama_vendor'
    ];

    protected $with = ['jenisUnit'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'kode_vendor', 'kode_vendor');
    }

    public function jenisUnit()
    {
        return $this->belongsTo(JenisUnit::class, 'jenis_unit_id');
    }

    // Keep the old relationship for backward compatibility
    public function vendorAngkut()
    {
        return $this->belongsTo(Vendor::class, 'kode_vendor', 'kode_vendor');
    }

    public static function generateKodeLambung()
    {
        $year = substr(date('Y'), -2); 
        
        $latest = static::where('kode_lambung', 'LIKE', "JBM-{$year}-V%")
            ->orderBy('kode_lambung', 'desc')
            ->first();
        
        if ($latest) {
            preg_match('/V(\d+)$/', $latest->kode_lambung, $matches);
            $number = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        } else {
            $number = 1;
        }
        
        $numberStr = str_pad($number, 3, '0', STR_PAD_LEFT);
        
        return "JBM-{$year}-V{$numberStr}";
    }
}
