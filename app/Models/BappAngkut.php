<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ComplainBappAngkut;
use App\Models\HasilTebang;
use App\Models\VendorAngkut;
use App\Models\Vehicle;
use App\Models\Spd;

class BappAngkut extends Model
{
    use HasFactory;

    protected $table = 'bapp_angkut';
    public $timestamps = false;

    protected $fillable = [
        'kode_bapp',
        'kode_hasil_tebang',
        'vendor_angkut',
        'periode_bapp',
        'tanggal_bapp',
        'jenis_tebang',
        'divisi',
        'kode_petak',
        'kode_lambung',
        'zonasi',
        'tonase',
        'sortase',
        'tonase_final',
        'insentif_tandem_harvester',
        'total_pendapatan',
        'status',
        'no_spd',
        'diajukan_oleh',
        'ttd_diajukan_oleh_path',
        'diperiksa_oleh',
        'ttd_diperiksa_oleh_path',
        'disetujui_oleh',
        'ttd_disetujui_oleh_path',
    ];

    public function hasilTebang()
    {
        return $this->belongsTo(HasilTebang::class, 'kode_hasil_tebang', 'kode_hasil_tebang');
    }

    public function vendor()
    {
        return $this->belongsTo(VendorAngkut::class, 'vendor_angkut', 'kode_vendor')
                   ->select('kode_vendor', 'nama_vendor');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'kode_lambung', 'kode_lambung');
    }

    public function spd()
    {
        return $this->belongsTo(Spd::class, 'no_spd', 'no_spd');
    }

    /**
     * Get all complaints for this BAPP Angkut.
     */
    public function komplain()
    {
        return $this->hasMany(ComplainBappAngkut::class, 'kode_bapp', 'kode_bapp');
    }
}
