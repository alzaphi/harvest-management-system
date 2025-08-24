<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisUnit extends Model
{
    protected $table = 'jenis_unit';
    
    protected $fillable = [
        'kode_unit',
        'nama_unit',
        'keterangan',
    ];
    
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'jenis_unit_id');
    }
}
