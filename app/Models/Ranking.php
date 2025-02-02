<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    use HasFactory;

    //define fillable
    protected $fillable = [
        'year',
        'remoteId',
        'location',
        'place',
        'pointsForNext',
        'points'
    ];
}