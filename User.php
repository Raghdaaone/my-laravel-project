<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
use HasApiTokens, HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        
        'google_id',
        'google_verified',
        'reset_token',           
        'reset_token_expires_at',
        'name'  ,
        'email',
        'password',
        'phone',
        'role',
        'is_active',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'reset_token',
        'reset_token_expires_at',
        'google_id'  ,
        'google_verified',
            'is_active',
            'role',
            'google_id',
            'name',
            'email',
            'phone'

    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'reset_token_expires_at' => 'datetime',
            "google_verified" => 'boolean',
             'is_active' => 'boolean',
             'role' => 'string',
             'avatar' => 'string',
             'google_id' => 'string',
             'name' => 'string',
             'email' => 'string',
             'phone' => 'string',
             
        ];
    }
}
