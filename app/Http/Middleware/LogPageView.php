<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;

class LogPageView
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $realIp = request()->header('X-Real-IP') ?? request()->header('X-Forwarded-For') ?? request()->ip();
        $response = $next($request);

        if ($user = $request->user()) {
            PageView::create([
                'remoteId' => $user->remoteId,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'route' => $request->path(),
                'ip_address' => $realIp,
            ]);
        }

        return $response;
    }
}
