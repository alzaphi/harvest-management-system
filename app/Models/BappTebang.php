<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BappTebang extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_VENDOR = 'pending_vendor';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $table = 'bapp_tebang';

    protected $fillable = [
        'kode_bapp',
        'kode_hasil_tebang',
        'vendor_tebang',
        'periode_bapp',
        'tanggal_bapp',
        'jenis_tebang',
        'divisi',
        'kode_petak',
        'tonase',
        'sortase',
        'tonase_final',
        'tebang',
        'ikat_tumpuk',
        'muat',
        'sewa_grab',
        'insentif_pasok',
        'insentif_beras_tk',
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

    public $timestamps = false;

    public function hasilTebang()
    {
        return $this->hasMany(HasilTebang::class, 'kode_hasil_tebang', 'kode_hasil_tebang');
    }

    public function vendor()
    {
        return $this->belongsTo(VendorAngkut::class, 'vendor_tebang', 'kode_vendor');
    }

    public function vendorAngkut()
    {
        return $this->belongsTo(VendorAngkut::class, 'vendor_angkut', 'kode_vendor');
    }

    /**
     * Get all BAPP Tebang Details for this BAPP.
     */
    public function bappTebangDetails()
    {
        return $this->hasMany(BappTebangDetail::class, 'bapp_tebang_id');
    }

    public function subBlock()
    {
        return $this->belongsTo(SubBlock::class, 'kode_petak', 'kode_petak');
    }

    public function spd()
    {
        return $this->belongsTo(Spd::class, 'no_spd', 'no_spd');
    }

    /**
     * Get all komplain for this BAPP.
     */
    public function komplain()
    {
        return $this->hasMany(ComplainBappTebang::class, 'kode_bapp', 'kode_bapp');
    }

    // Method helper untuk status
    public function isPendingVendor()
    {
        return $this->status === self::STATUS_PENDING_VENDOR;
    }

    public function isPendingApproval()
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
