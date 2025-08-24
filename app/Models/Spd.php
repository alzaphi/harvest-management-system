<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Spd extends Model
{
    use HasFactory;

    protected $table = 'spd';

    protected $fillable = [
        'no_spd',
        'tanggal_spd',
        'periode',
        'total_dana',
        'diajukan_oleh',
        'ttd_diajukan_oleh',
        'diverifikasi_oleh',
        'ttd_diverifikasi_oleh',
        'diketahui_oleh',
        'ttd_diketahui_oleh',
        'disetujui_oleh',
        'ttd_disetujui_oleh',
        'dibayar_oleh',
        'ttd_dibayar_oleh',
        'ditolak_oleh',
        'alasan_penolakan',
        'status',
    ];

    /**
     * Get the user who submitted the SPD
     */
    public function diajukanOleh()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    /**
     * Get the user who verified the SPD
     */
    public function diverifikasiOleh()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    /**
     * Get the user who acknowledged the SPD
     */
    public function diketahuiOleh()
    {
        return $this->belongsTo(User::class, 'diketahui_oleh');
    }

    /**
     * Get the user who approved the SPD
     */
    public function disetujuiOleh()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Get the user who processed the payment
     */
    public function dibayarOleh()
    {
        return $this->belongsTo(User::class, 'dibayar_oleh');
    }

    /**
     * Get the user who rejected the SPD
     */
    public function ditolakOleh()
    {
        return $this->belongsTo(User::class, 'ditolak_oleh');
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
