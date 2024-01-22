<?php

namespace App\Models\UserManagement;

use App\Traits\HasDateSerialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthenticationLog extends Model
{
    use HasDateSerialization, HasFactory;

    protected $fillable = [
        'log_name',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];
}
