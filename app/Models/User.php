<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_name',
        'name',
        'username',
        'password',
        'vendor_id',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When a user is created, assign the role
        static::created(function ($user) {
            if ($user->role_name) {
                $role = \Spatie\Permission\Models\Role::firstOrCreate([
                    'name' => $user->role_name,
                    'guard_name' => 'web'
                ]);
                
                // First remove all existing roles
                $user->roles()->detach();
                
                // Then assign the new role
                $user->assignRole($role);
                
                // Log the role assignment
                \Log::channel('single')->info('Role assigned to user:', [
                    'username' => $user->username,
                    'role_name' => $user->role_name
                ]);
            }
        });

        // When a user is updated, sync the role
        static::updated(function ($user) {
            if ($user->role_name) {
                $role = \Spatie\Permission\Models\Role::firstOrCreate([
                    'name' => $user->role_name,
                    'guard_name' => 'web'
                ]);
                
                // First remove all existing roles
                $user->roles()->detach();
                
                // Then assign the new role
                $user->assignRole($role);
                
                // Log the role update
                \Log::channel('single')->info('Role updated for user:', [
                    'username' => $user->username,
                    'role_name' => $user->role_name
                ]);
            }
        });
    }
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /**
     * Set the user's password with hashing.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            // Pastikan value tidak kosong dan belum di-hash
            if (!empty($value) && !preg_match('/^\$2y\$\d{2}\$.*/', $value)) {
                $this->attributes['password'] = bcrypt($value);
                \Log::channel('single')->info('Password hashed for user:', [
                    'username' => $this->username ?? 'new_user',
                    'hashed_password' => $this->attributes['password']
                ]);
            } else {
                $this->attributes['password'] = $value;
            }
        }
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    /**
     * Get the username attribute for authentication.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Get the vendor associated with the user.
     */
    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('admin') || $this->hasRole('Admin') || $this->hasRole('ADMIN');
    }

    /**
     * Get the vendor associated with the user.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    /**
     * Get the vendor's kode_vendor through the vendor relationship.
     */
    public function getKodeVendorAttribute()
    {
        return $this->vendor ? $this->vendor->kode_vendor : null;
    }
}
