<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\LoginLog;
use App\Http\Resources\LoginLogResource;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    public function index()
    {
        $logs = LoginLog::latest()->take(30)->get();
        return LoginLogResource::collection($logs);
    }
}
