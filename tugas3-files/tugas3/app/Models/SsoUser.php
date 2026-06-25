<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SsoUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'sso_subject',
        'role',
        'jwt_payload',
        'last_login',
    ];

    protected $casts = [
        'last_login'  => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];
}
