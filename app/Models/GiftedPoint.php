<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftedPoint extends Model
{
    use HasFactory;
    protected $fillable = [
        'receiver_remote_id',
        'points',
        'gifted_at',
        'giver_remote_id'
    ];
    
    protected $casts = [
        'gifted_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
