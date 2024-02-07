<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;
    protected $fillable = ['cart_id', 'reward_id', 'note', 'quantity'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class, 'reward_id');
    }
}
