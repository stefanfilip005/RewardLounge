<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    use HasFactory;
    protected $table = 'employee_shifts';
    protected $fillable = [
        'employee_id', 'location', 'VM', 'VM_norm', 'NM', 'NM_norm', 'NIGHT', 'NIGHT_norm',
        'NEF', 'NEF_norm', 'RTW', 'RTW_norm', 'KTW', 'KTW_norm', 'BKTW', 'BKTW_norm',
        'weekday_0', 'weekday_0_norm', 'weekday_1', 'weekday_1_norm', 'weekday_2', 'weekday_2_norm',
        'weekday_3', 'weekday_3_norm', 'weekday_4', 'weekday_4_norm', 'weekday_5', 'weekday_5_norm',
        'weekday_6', 'weekday_6_norm',
    ];

}
