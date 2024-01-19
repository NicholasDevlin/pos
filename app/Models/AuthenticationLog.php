<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthenticationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_name',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];
}
