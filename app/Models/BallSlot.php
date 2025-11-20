<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BallSlot extends Model
{
    protected $table = 'ball_slots';

    protected $fillable = [
        'mtr_number',
        'label',
        'helper_name',
        'helper_phone',
        'helper_email',
        'filled_by_user_id',
        'filled_at',
    ];

    protected $casts = [
        'filled_at'  => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
