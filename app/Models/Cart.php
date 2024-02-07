<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = ['remoteId'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'remoteId', 'remoteId');
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
