<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorAngkut extends Model
{
    protected $table = 'vendor_angkut';

    protected $fillable = [
        'kode_vendor',
        'nama_vendor',
        'no_hp',
        'jenis_vendor',
        'status',
        'nomor_rekening',
        'nama_bank'
    ];

    public $timestamps = false; // kalau kamu memang gak pakai timestamps di tabel
}
