<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;
    protected $fillable = ['remoteId', 'firstname', 'lastname', 'logged_in_at', 'ip_address'];

}
