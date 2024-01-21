<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    use HasFactory;
    protected $fillable = ['remoteId', 'firstname', 'lastname', 'route', 'ip_address'];
}
