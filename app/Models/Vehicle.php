<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'funkkennung',
        'active',
        'last_seen_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];
}
