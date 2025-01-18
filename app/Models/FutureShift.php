<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FutureShift extends Model
{
    use HasFactory;
    protected $table = 'futureShifts';

    protected $fillable = [
        'shift_id',
        'date',
        'begin',
        'end',
        'vehicle_type',
        'vehicle_type_id',
        'role',
        'role_id',
        'employee_id',
        'employee_name'
    ];

}
