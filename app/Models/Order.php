<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'remoteId', 
        'total_points', 
        'state',
        'created_at_datetime',
        'state_1_datetime', 'state_1_user_id',
        'state_2_datetime', 'state_2_user_id',
        'state_3_datetime', 'state_3_user_id',
        'state_4_datetime', 'state_4_user_id',
        'state_5_datetime', 'state_5_user_id',
    ];
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
