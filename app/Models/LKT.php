<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LKT extends Model
{
    // Approval stages
    const STAGE_DRAFT = 0;
    const STAGE_P1 = 1; // Pemeriksa (Mandor)
    const STAGE_P2 = 2; // Penyetuju (Manager)
    const STAGE_P3 = 3; // Penimbang (Admin)
    const STAGE_COMPLETED = 4;

    // Status constants
    const STATUS_DRAFT = 'Draft';
    const STATUS_DIAJUKAN = 'Diajukan';
    const STATUS_DIPERIKSA = 'Diperiksa';
    const STATUS_DISETUJUI = 'Disetujui';
    const STATUS_DITOLAK = 'Ditolak';
    const STATUS_SELESAI = 'Selesai';

    protected $table = 'lkt';
    
    protected $dates = [
        'created_at',
        'updated_at',
        'ttd_dibuat_pada',
        'ttd_diperiksa_pada',
        'ttd_disetujui_pada',
        'ttd_ditimbang_pada'
    ];

    protected $fillable = [
        'kode_lkt', 'tanggal_tebang', 'kode_vendor_tebang', 'kode_vendor_angkut',
        'kode_driver', 'kode_spt', 'kode_petak', 'jenis_tebangan', 'tarif_zona_angkutan', 'catatan',
        'dibuat_oleh', 'ttd_dibuat_oleh_path', 'diperiksa_oleh', 'ttd_diperiksa_oleh_path',
        'disetujui_oleh', 'ttd_disetujui_oleh_path', 'ditimbang_oleh', 'ttd_ditimbang_oleh_path',
        'status', 'approval_stage', 'status_updated_at', 'status_updated_by',
        'ttd_dibuat_pada', 'ttd_diperiksa_pada', 'ttd_disetujui_pada', 'ttd_ditimbang_pada'
    ];
    
    protected $appends = ['approval_progress', 'current_stage_label'];
    
    protected $casts = [
        'approval_stage' => 'integer',
        'status_updated_at' => 'datetime',
    ];

    public function spt() {
        return $this->belongsTo(SPT::class, 'kode_spt', 'kode_spt');
    }

    public function vendorTebang() {
        return $this->belongsTo(VendorAngkut::class, 'kode_vendor_tebang', 'kode_vendor')
                    ->where('jenis_vendor', 'Vendor Tebang');
    }

    public function vendorAngkut() {
        return $this->belongsTo(VendorAngkut::class, 'kode_vendor_angkut', 'kode_vendor')
                    ->where('jenis_vendor', 'Vendor Angkut');
    }

    public function petak() {
        return $this->belongsTo(SubBlock::class, 'kode_petak', 'kode_petak');
    }

    public function driver() {
        return $this->belongsTo(Vehicle::class, 'kode_driver', 'kode_lambung');
    }

    public function statusPetak() {
        return $this->belongsTo(\App\Models\StatusSubBlock::class, 'kode_petak', 'kode_petak');
    }
    
    /**
     * Get the approval progress percentage
     */
    public function getApprovalProgressAttribute()
    {
        $stages = [
            self::STAGE_DRAFT => 0,
            self::STAGE_P1 => 25,
            self::STAGE_P2 => 50,
            self::STAGE_P3 => 75,
            self::STAGE_COMPLETED => 100
        ];
        
        return $stages[$this->approval_stage ?? self::STAGE_DRAFT] ?? 0;
    }
    
    /**
     * Get current stage label
     */
    public function getCurrentStageLabelAttribute()
    {
        $stages = [
            self::STAGE_DRAFT => 'Draft',
            self::STAGE_P1 => 'Menunggu Pemeriksaan',
            self::STAGE_P2 => 'Menunggu Persetujuan',
            self::STAGE_P3 => 'Menunggu Penimbangan',
            self::STAGE_COMPLETED => 'Selesai'
        ];
        
        return $stages[$this->approval_stage ?? self::STAGE_DRAFT] ?? 'Draft';
    }
    
    /**
     * Check if user can approve this LKT
     */
    /**
     * Check if the given user is the next approver for this LKT
     */
    public function isNextApprover($user = null)
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return false;
        }
        
        $userRole = $user->role_name;
        
        // Check if user is the creator (mandor) and the LKT is still in draft stage
        if ($userRole === 'mandor' && $this->status === self::STATUS_DRAFT) {
            return true;
        }
        
        // Check for other approvers based on role and stage
        if ($userRole === 'Assistant Divisi Plantation' && $this->approval_stage === self::STAGE_P1) {
            return true;
        }
        
        if ($userRole === 'Assistant Manager Plantation' && $this->approval_stage === self::STAGE_P2) {
            return true;
        }
        
        if (($userRole === 'Manager Plantation' || $userRole === 'PT PAG') && $this->approval_stage === self::STAGE_P3) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can approve this LKT
     */
    public function canBeApprovedBy($user)
    {
        if (!$user) return false;
        
        // Admin can approve at any stage
        if ($user->role_name === 'admin') return true;
        
        // Check if the user is the next approver
        return $this->isNextApprover($user);
    }
    
    /**
     * Get the status label for display
     */
    public function getStatusLabelAttribute()
    {
        $user = auth()->user();
        
        // Skip 'Waiting' status for admin users
        if ($user->role_name === 'admin') {
            return $this->status;
        }
        
        // For PT PAG user, show 'Waiting' for LKTs that are approved and waiting for weighing
        if ($user->role_name === 'PT PAG' && $this->status === 'Disetujui' && $this->approval_stage === self::STAGE_P3) {
            return 'Waiting';
        }
        
        // If the current user is the next approver and the status is 'Diajukan', show 'Waiting'
        if ($this->isNextApprover($user) && $this->status === 'Diajukan') {
            return 'Waiting';
        }

        // If the current user is the next approver and the status is 'Diperiksa', show 'Waiting'
        if ($this->isNextApprover($user) && $this->status === 'Diperiksa') {
            return 'Waiting';
        }
        
        return $this->status;
    }
}
