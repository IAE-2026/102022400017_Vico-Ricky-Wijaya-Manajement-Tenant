<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'id_number',
        'address',
        'occupation',
        'emergency_contact',
        'document_path',
        'document_original_name',
        'status',
        'notes',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    protected $hidden = [
        'document_path',
        'deleted_at',
    ];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function latestContract()
    {
        return $this->hasOne(Contract::class)->latestOfMany();
    }
}
