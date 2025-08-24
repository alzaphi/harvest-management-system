<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilTebang extends Model
{
    protected $table = 'hasil_tebang';
    protected $fillable = [
    'kode_hasil_tebang',
    'tanggal_timbang',
    'kode_lkt',
    'kode_spt',
    'kode_petak',
    'vendor_tebang',
    'vendor_angkut',
    'jenis_tebang',
    'kode_lambung',
    'bruto',
    'tanggal_bruto',
    'tarra',
    'tanggal_tarra',
    'netto1',
    'sortase',
    'netto2',
    'divisi',
    'zonasi',
    'status',
    'status_angkut'
];

    // protected $fillable = [
    //     'kode_hasil_tebang',
    //     'tanggal_timbang',
    //     'kode_driver',
    //     'vendor_tebang',
    //     'kode_LKT',
    //     'kode_SPT',
    //     'tanggal_jam_bruto',
    //     'tanggal_jam_tarra',
    //     'total_bruto',
    //     'total_tarra',
    //     'sortase',
    //     'produk',
    //     'kode_petak',
    //     'dibuat_oleh',
    //     'ttd_dibuat_oleh_path',
    //     'diperiksa_oleh',
    //     'ttd_diperiksa_oleh_path',
    //     'disetujui_oleh',
    //     'ttd_disetujui_oleh_path',
    // ];

    // // Relasi ke Vendor Tebang
    // public function vendor()
    // {
    //     return $this->belongsTo(VendorAngkut::class, 'vendor_tebang', 'kode_vendor')
    //                 ->where('jenis_vendor', 'Vendor Tebang');
    // }

    
    // Relasi fleksibel ke vendor (bisa angkut atau tebang)
    public function vendor()
    {
        return $this->belongsTo(VendorAngkut::class, 'vendor_tebang', 'kode_vendor');
    }

    // Relasi ke vendor khusus tebang
    // public function vendorTebang()
    // {
    //     return $this->belongsTo(VendorAngkut::class, 'vendor_tebang', 'kode_vendor')
    //                 ->where('jenis_vendor', 'Vendor Tebang');
    // }

    public function vendorTebang()
{
    return $this->belongsTo(VendorAngkut::class, 'vendor_tebang', 'kode_vendor', 'nama_vendor')
                ->where('jenis_vendor', 'Vendor Tebang');
}

public function vendorAngkut()
{
    return $this->belongsTo(VendorAngkut::class, 'vendor_angkut', 'kode_vendor','nama_vendor')
                ->where('jenis_vendor', 'Vendor Angkut');
}


    // Relasi ke Driver (dari Vehicle table)
    public function driver()
    {
        return $this->belongsTo(Vehicle::class, 'kode_vendor', 'nama_vendor');
    }
    

    // Relasi ke LKT
    public function lkt()
    {
        return $this->belongsTo(LKT::class, 'kode_LKT', 'kode_lkt');
    }

    // Relasi ke SPT
    public function spt()
    {
        return $this->belongsTo(SPT::class, 'kode_SPT', 'kode_spt');
    }

        public function bappDetails()
    {
        return $this->hasMany(DetailBappTebang::class, 'kode_hasil_tebang', 'kode_hasil_tebang');
    }

    // Relasi ke SubBlock
    public function subBlock()
    {
        return $this->belongsTo(SubBlock::class, 'kode_petak', 'kode_petak');
    }

}
