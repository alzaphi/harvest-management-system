<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Foreman extends Model
{
    protected $table = 'foreman';
    
    protected $fillable = [
        'kode_mandor',
        'nama_mandor',
        'email',
        'no_hp',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Get the latest kode_mandor
            $latest = self::where('kode_mandor', 'like', 'MA%')
                ->orderBy('kode_mandor', 'desc')
                ->first();

            $number = 1;
            if ($latest) {
                // Extract the number part and increment
                $number = (int) substr($latest->kode_mandor, 2) + 1;
            }
            
            // Format the number with leading zeros
            $model->kode_mandor = 'MA' . str_pad($number, 5, '0', STR_PAD_LEFT);
        });
    }
}
