<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'reward_id', 'quantity', 'note', 'name', 'slogan', 'description', 'src1', 'points', 'euro'];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
