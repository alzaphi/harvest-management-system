<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForemanSubBlock extends Model
{
    protected $table = 'foreman_sub_blocks';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'kode_petak',
        'divisi',
        'kode_mandor',
        'nama_mandor',
        'tanggal_kerja'
    ];

    protected $casts = [
        'tanggal_kerja' => 'date',
    ];

    /**
     * Get the subBlock that owns the ForemanSubBlock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subBlock(): BelongsTo
    {
        return $this->belongsTo(SubBlock::class, 'kode_petak', 'kode_petak');
    }

    /**
     * Get the foreman that owns the ForemanSubBlock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function foreman(): BelongsTo
    {
        return $this->belongsTo(Foreman::class, 'kode_mandor', 'kode_mandor');
    }
}
