<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoapAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'activity_name',
        'log_content',
        'receipt_number',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
