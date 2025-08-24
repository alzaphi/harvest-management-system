<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SubBlock;
use App\Models\Foreman;
use App\Models\SptConfirmation;


class SPT extends Model
{
    protected $table = 'spt';

    protected $fillable = [
        'kode_spt',
        'kode_vendor',
        'kode_mandor',
        'kode_petak',
        'tanggal_tebang',
        'jumlah_tenaga_kerja',
        'jenis_tebang',
        'catatan',
        'dibuat_oleh',
        'ttd_dibuat_oleh_path',
        'diperiksa_oleh',
        'ttd_diperiksa_oleh_path',
        'disetujui_oleh',
        'ttd_disetujui_oleh_path',
        'status',
        'approval_stage'
    ];

    // Approval stages
    const STAGE_DRAFT = 'draft';
    const STAGE_PEMBUAT = 'menunggu_pembuat';
    const STAGE_PEMERIKSA = 'menunggu_pemeriksa';
    const STAGE_PENYETUJU = 'menunggu_penyetuju';
    const STAGE_SELESAI = 'selesai';
    const STAGE_DITOLAK = 'ditolak';

    // Get the next approval stage
    public function getNextApprovalStage()
    {
        $stages = [
            self::STAGE_DRAFT => self::STAGE_PEMBUAT,
            self::STAGE_PEMBUAT => self::STAGE_PEMERIKSA,
            self::STAGE_PEMERIKSA => self::STAGE_PENYETUJU,
            self::STAGE_PENYETUJU => self::STAGE_SELESAI,
        ];

        return $stages[$this->approval_stage] ?? null;
    }

    // Check if the document can be signed by the current user
    public function canBeSignedBy($user)
    {
        if (!auth()->check()) return false;

        $userRole = $user->role_name; // Using role_name as per your code
        
        // Assistant Divisi Plantation should never see the signature button
        if ($userRole === 'Assistant Divisi Plantation') {
            return false;
        }
        
        // Only Admin can sign in STAGE_PEMBUAT
        if ($this->approval_stage === self::STAGE_PEMBUAT && 
            ($userRole === 'Admin' || $user->isAdmin())) {
            return true;
        }
        
        return false;
    }

    // Check if user can edit SPT
    public function canBeEditedBy($user)
    {
        if (!auth()->check()) return false;

        $userRole = $user->role_name;
        
        // Only Admin and Assistant Divisi Plantation can edit if status is Draft or Diajukan
        if (in_array($userRole, ['Admin', 'Assistant Divisi Plantation']) && 
            in_array($this->approval_stage, [self::STAGE_DRAFT, self::STAGE_PEMBUAT])) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if the kode_petak has any completed SPTs based on tracking activity
     */
    public static function hasCompletedSpt($kodePetak)
    {
        return self::where('kode_petak', $kodePetak)
            ->whereHas('trackingActivity', function($query) {
                $query->where('status_tracking', 'completed');
            })
            ->exists();
    }

    /**
     * Get count of SPTs with different vendors for a kode_petak
     */
    public static function getDifferentVendorSptCount($kodePetak)
    {
        return self::where('kode_petak', $kodePetak)
            ->select('kode_vendor')
            ->distinct()
            ->count();
    }

    /**
     * Check if vendor already has an active SPT for the kode_petak
     * (not completed or cancelled in tracking)
     */
    public static function vendorHasSptForPetak($vendorId, $kodePetak)
    {
        return self::where('kode_vendor', $vendorId)
            ->where('kode_petak', $kodePetak)
            ->whereHas('trackingActivity', function($query) {
                $query->whereNotIn('status_tracking', ['completed', 'cancelled']);
            })
            ->exists();
    }

    /**
     * Check if there's an active SPT (not completed or cancelled) 
     * created within the last 3 days for the kode_petak
     */
    public static function hasRecentSpt($kodePetak)
    {
        return self::where('kode_petak', $kodePetak)
            ->where('created_at', '>=', now()->subDays(3))
            ->whereHas('trackingActivity', function($query) {
                $query->whereNotIn('status_tracking', ['completed', 'cancelled']);
            })
            ->exists();
    }

    // Check if user can delete SPT
    public function canBeDeletedBy($user)
    {
        if (!auth()->check()) return false;

        $userRole = $user->role_name;
        
        // Only Admin and Assistant Divisi Plantation can delete if status is Draft
        if (in_array($userRole, ['Admin', 'Assistant Divisi Plantation']) && 
            $this->approval_stage === self::STAGE_DRAFT) {
            return true;
        }
        
        return false;
    }

    protected $attributes = [
        'status' => 'Draft',
        'approval_stage' => self::STAGE_DRAFT,
        'jumlah_tenaga_kerja' => 0,
        'jenis_tebang' => null,
        'catatan' => null,
        'dibuat_oleh' => null,
        'ttd_dibuat_oleh_path' => null,
        'diperiksa_oleh' => null,
        'ttd_diperiksa_oleh_path' => null,
        'disetujui_oleh' => null,
        'ttd_disetujui_oleh_path' => null,
    ];

    protected $casts = [
        'tanggal_tebang' => 'date',
        'jumlah_tenaga' => 'integer',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the vendor that owns the SPT.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'kode_vendor', 'kode_vendor');
    }

    /**
     * Get the harvest sub block that owns the SPT.
     */
    public function harvestSubBlock(): BelongsTo
    {
        return $this->belongsTo(HarvestSubBlock::class, 'kode_petak', 'kode_petak');
    }

    /**
     * Get the sub block that owns the SPT.
     */
    public function subBlock(): BelongsTo
    {
        return $this->belongsTo(SubBlock::class, 'kode_petak', 'kode_petak')
            ->withDefault([
                'estate' => null,
                'divisi' => null,
                'luas_area' => null,
                'zona' => null
            ]);
    }

    /**
     * Get the estate name through subBlock relationship.
     */
    public function getEstateAttribute()
    {
        return $this->subBlock->estate;
    }

    /**
     * Get the divisi through subBlock relationship.
     */
    public function getDivisiAttribute()
    {
        return $this->subBlock->divisi;
    }

    /**
     * Get the luas_area through subBlock relationship.
     */
    public function getLuasAreaAttribute()
    {
        return $this->subBlock->luas_area;
    }

    /**
     * Get the zona through subBlock relationship.
     */
    public function getZonaAttribute()
    {
        return $this->subBlock->zona;
    }

    /**
     * Get the foreman sub block that owns the SPT.
     */
    public function foremanSubBlock()
    {
        return $this->belongsTo(ForemanSubBlock::class, 'kode_petak', 'kode_petak');
    }

    /**
     * Get the foreman that is assigned to the SPT.
     */
    public function mandor()
    {
        return $this->belongsTo(Foreman::class, 'kode_mandor', 'kode_mandor');
    }

    public function confirmations()
    {
        return $this->hasMany(SptConfirmation::class, 'spt_id');
    }

    public function confirmedByMandor()
    {
        return $this->hasOne(SptConfirmation::class, 'spt_id')
            ->where('role_name', 'mandor')
            ->latest();
    }

    /**
     * Get the LKTs associated with the SPT.
     */
    public function lkt()
    {
        return $this->hasMany(LKT::class, 'kode_spt', 'kode_spt');
    }

    /**
     * Get the tracking activity for the SPT.
     */
    public function trackingActivity()
    {
        return $this->hasOne(TrackingActivity::class, 'kode_spt', 'kode_spt');
    }

    /**
     * Get the URL for the creator's signature.
     */
    public function getTtdDibuatOlehUrlAttribute(): ?string
    {
        return $this->ttd_dibuat_oleh_path ? asset('storage/' . $this->ttd_dibuat_oleh_path) : null;
    }

    /**
     * Get the URL for the inspector's signature.
     */
    public function getTtdDiperiksaOlehUrlAttribute(): ?string
    {
        return $this->ttd_diperiksa_oleh_path ? asset('storage/' . $this->ttd_diperiksa_oleh_path) : null;
    }

    /**
     * Get the URL for the approver's signature.
     */
    public function getTtdDisetujuiOlehUrlAttribute(): ?string
    {
        return $this->ttd_disetujui_oleh_path ? asset('storage/' . $this->ttd_disetujui_oleh_path) : null;
    }

}
