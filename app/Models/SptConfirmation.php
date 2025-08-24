<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Spt;
use App\Models\User;

class SptConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'spt_id',
        'user_id',
        'role_name',
        'confirmed_at'
    ];

    protected $dates = [
        'confirmed_at',
        'created_at',
        'updated_at'
    ];

    public function spt()
    {
        return $this->belongsTo(Spt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
