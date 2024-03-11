<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\LoginLog;
use App\Http\Resources\LoginLogResource;
use App\Http\Resources\PageViewResource;
use App\Models\PageView;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function loginLog()
    {
        $logs = LoginLog::where('remoteId', '!=', 116931)->latest()->take(30)->get();
        return LoginLogResource::collection($logs);
    }


    public function accessLog()
    {
        $logs = PageView::select(
            'remoteId', 
            'firstname', 
            'lastname', 
            DB::raw('DATE(created_at) as date'), 
            DB::raw('COUNT(*) as route_count')
        )
        ->where('remoteId', '!=', 116931)
        ->groupBy('remoteId', 'firstname', 'lastname', 'date')
        ->latest('date')
        ->take(50)
        ->get();
        return PageViewResource::collection($logs);
    }
    
}
